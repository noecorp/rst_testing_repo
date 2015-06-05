<?php

/**
 * Model that manages the products
 *
 * @package Operation_Models
 * @copyright transerv
 */
class Corp_Ratnakar_Cardholders extends Corp_Ratnakar {

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
    protected $_name = DbTable::TABLE_RAT_CORP_CARDHOLDER;

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

    /**
     * Retrieves all the products attached to
     * the specified master product
     * 
     * @param integer $resourceId
     * @access public
     * @return void
     */
    public function showPendingCardholderDetails($batchName,$productId = 0, $page = 1, $paginate = NULL, $force = FALSE) {
        switch (CURRENT_MODULE) {
            case MODULE_AGENT: 
                $colName = 'by_agent_id';
                break;
            case MODULE_CORPORATE: 
                $colName = 'by_corporate_id';
                break;
            case MODULE_OPERATION:
                $colName = 'by_ops_id';
                break;
        }
        
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number"); 
         
        $user = Zend_Auth::getInstance()->getIdentity();
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_RAT_CORP_CARDHOLDER_BATCH,array('id', 'bank_id', 'product_id', $card_number, 'card_pack_id', 'afn', 'medi_assist_id', 'employee_id', 'first_name', 'middle_name', 'last_name', 'name_on_card', 'gender', 'date_of_birth', 'mobile', 'email', 'landline', 'address_line1', 'address_line2', 'city', 'pincode', 'mother_maiden_name', 'employer_name', 'corporate_id', 'corp_address_line1', 'corp_address_line2', 'corp_city', 'corp_pin', 'id_proof_type', 'id_proof_number', 'address_proof_type', 'address_proof_number', 'by_ops_id', 'by_agent_id', 'by_corporate_id', 'batch_name', 'date_created', 'date_updated', 'upload_status', 'failed_reason'));
        $select->where('upload_status = ?', STATUS_TEMP);
        $select->where('batch_name = ?', $batchName);
        $select->where('product_id = ?', $productId);
        $select->where("$colName = ?", $user->id);
        $select->order('id ASC');
        if($force){
            return $this->_db->fetchAll($select);
        }
        return $this->_paginate($select, $page, $paginate);
    }
    
    public function getBatch(){
         
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_RAT_CORP_CARDHOLDER_BATCH, array('batch_name'));
        $select->order('batch_name ASC');
        $select->group('batch_name');

        $batch = $this->_db->fetchAll($select);
        
        $dataArray = array('' => 'Select Batch');
        foreach ($batch as $val) {
            $dataArray[$val['batch_name']] = $val['batch_name'];
        }
       
        return $dataArray;

    }
    
     public function getBatchDetails($batchName) {
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`cb`.`card_number`,'".$decryptionKey."') as card_number");
         
        $select = $this->_db->select()
                ->from(DbTable::TABLE_RAT_CORP_CARDHOLDER_BATCH . " as cb", array('cb.id', $card_number, 
                    'cb.card_pack_id', 'cb.medi_assist_id', 'cb.employee_id', 
                    'cb.first_name', 'cb.last_name',
                    'cb.name_on_card', 'cb.mobile', 'cb.email', 'cb.city', 'cb.pincode', 'cb.gender',
                    'cb.date_of_birth', 'cb.employer_name', 'cb.corporate_id', 'cb.upload_status', 'cb.failed_reason as batch_failed_reason'))
                ->joinLeft(DbTable::TABLE_RAT_CORP_CARDHOLDER . ' as c', "c.batch_id = cb.id AND cb.upload_status = '" . STATUS_PASS . "' ", array('id as cardholder_id', 'status as cardholder_status', 'failed_reason'))
                ->where('cb.batch_name =?', $batchName)
                ->where("cb.upload_status IN ('".STATUS_PASS."', '".STATUS_DUPLICATE."', '".STATUS_FAILED."')");
        
        $rs = $this->_db->fetchAll($select);
        $i = 0;
        foreach($rs as $val)
        {
            $data[$i]['medi_assist_id'] = $val['medi_assist_id'];
            $data[$i]['employee_id'] = $val['employee_id'];
            $data[$i]['card_number'] = Util::maskCard($val['card_number'], 4, 8);
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
    
    
    
    public function getBatchDetailsByDate($batchArr) {
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`cb`.`card_number`,'".$decryptionKey."') as card_number");
           
        $select = $this->_db->select()
                ->from(DbTable::TABLE_RAT_CORP_CARDHOLDER_BATCH . " as cb", array('cb.id', $card_number, 
                    'cb.card_pack_id', 'cb.medi_assist_id', 'cb.employee_id', 
                    'cb.first_name', 'cb.last_name',
                    'cb.name_on_card', 'cb.mobile', 'cb.email', 'cb.city', 'cb.pincode', 'cb.gender',
                    'cb.date_of_birth', 'cb.employer_name', 'cb.corporate_id', 'cb.upload_status', 'cb.failed_reason as batch_failed_reason'))
                ->joinLeft(DbTable::TABLE_RAT_CORP_CARDHOLDER . ' as c', "c.batch_id = cb.id AND cb.upload_status = '" . STATUS_PASS . "' ", array('id as cardholder_id', 'status as cardholder_status', 'failed_reason'))
                ->where("cb.upload_status IN ('".STATUS_PASS."', '".STATUS_DUPLICATE."', '".STATUS_FAILED."')");
        if(isset($batchArr['product_id']) && !empty($batchArr['product_id'])) {
            $select->where('cb.product_id =?', $batchArr['product_id']);
        }
        if(isset($batchArr['batch_name']) && !empty($batchArr['batch_name'])) {
            $select->where('cb.batch_name =?', $batchArr['batch_name']);
        }
        if(isset($batchArr['start_date']) && !empty($batchArr['start_date'])) {
            $select->where('cb.date_created >= ?', $batchArr['start_date']);
        }
        if(isset($batchArr['end_date']) && !empty($batchArr['end_date'])) {
            $select->where('cb.date_created <= ?', $batchArr['end_date']);
        }
        if(isset($batchArr['status']) && !empty($batchArr['status'])) {
            $select->where('c.status = ?', $batchArr['status']);
        }
        if(isset($batchArr['by_corporate_id']) && !empty($batchArr['by_corporate_id'])) {
            $select->where('c.by_corporate_id = ?', $batchArr['by_corporate_id']);
        }
        $data = array();
        $rs = $this->_db->fetchAll($select);
        $i = 0;
        foreach($rs as $val)
        {
            $data[$i]['medi_assist_id'] = $val['medi_assist_id'];
            $data[$i]['employee_id'] = $val['employee_id'];
            $data[$i]['card_number'] = Util::maskCard($val['card_number'], 4);
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
                elseif($val['cardholder_status'] == STATUS_ACTIVATION_PENDING) 
                {
                    $data[$i]['status'] = STATUS_ACTIVATION_PENDING;
                    $data[$i]['failed_reason'] = '';
                }
            }
            $data[$i]['id'] = $val['id'];
            
            $i++;
        }
        return $data;
    }
    
    
    public function exportgetBatchDetailsByDate($batchArr)
    {
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`cb`.`card_number`,'".$decryptionKey."') as card_number");
         
        $select = $this->_db->select()
                ->from(DbTable::TABLE_RAT_CORP_CARDHOLDER_BATCH . " as cb", array('cb.id', $card_number, 
                    'cb.card_pack_id', 'cb.medi_assist_id', 'cb.employee_id', 
                    'cb.first_name', 'cb.last_name',
                    'cb.name_on_card', 'cb.mobile', 'cb.email', 'cb.city', 'cb.pincode', 'cb.gender',
                    'cb.date_of_birth', 'cb.employer_name', 'cb.corporate_id', 'cb.upload_status', 'cb.failed_reason as batch_failed_reason'))
                ->joinLeft(DbTable::TABLE_RAT_CORP_CARDHOLDER . ' as c', "c.batch_id = cb.id AND cb.upload_status = '" . STATUS_PASS . "' ", array('id as cardholder_id', 'status as cardholder_status', 'failed_reason'))
                ->where("cb.upload_status IN ('".STATUS_PASS."', '".STATUS_DUPLICATE."', '".STATUS_FAILED."')");
        if(isset($batchArr['product_id']) && !empty($batchArr['product_id'])) {
            $select->where('cb.product_id =?', $batchArr['product_id']);
        }
        if(isset($batchArr['batch_name']) && !empty($batchArr['batch_name'])) {
            $select->where('cb.batch_name =?', $batchArr['batch_name']);
        }
        if(isset($batchArr['start_date']) && !empty($batchArr['start_date'])) {
            $select->where('cb.date_created >= ?', $batchArr['start_date']);
        }
        if(isset($batchArr['end_date']) && !empty($batchArr['end_date'])) {
            $select->where('cb.date_created <= ?', $batchArr['end_date']);
        }
        if(isset($batchArr['status']) && !empty($batchArr['status'])) {
            $select->where('c.status = ?', $batchArr['status']);
        }
        if(isset($batchArr['by_corporate_id']) && !empty($batchArr['by_corporate_id'])) {
            $select->where('c.by_corporate_id = ?', $batchArr['by_corporate_id']);
        }
        $data = array();
        //echo $select; //exit;
        $rs = $this->_db->fetchAll($select);
        $i = 0;
        foreach($rs as $val)
        {
            $data[$i]['medi_assist_id'] = $val['medi_assist_id'];
            $data[$i]['employee_id'] = $val['employee_id'];
            $data[$i]['card_number'] = Util::maskCard($val['card_number'],4);
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
            //$data[$i]['id'] = $val['id'];
            
            $i++;
        }
        return $data;
    }

    public function updateCardholder($arr, $id) {
        $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        if((isset($arr['card_number'])) && ($arr['card_number'] != '')){
            $card_number = addslashes(trim($arr['card_number'])) ;
            $arr['card_number'] = new Zend_Db_Expr("AES_ENCRYPT('" . $card_number . "','" . $encryptionKey . "')");
            $arr['crn'] = new Zend_Db_Expr("AES_ENCRYPT('" . $card_number . "','" . $encryptionKey . "')");
        } else {
            $arr['card_number'] = '';
            $arr['crn'] = '';
        } 
        $this->_db->update(DbTable::TABLE_RAT_CORP_CARDHOLDER, $arr, "id=$id");
        return TRUE;
    }

/*
    public function insertCardholderBatch($dataArr, $batchName, $status) {
        $user = Zend_Auth::getInstance()->getIdentity();
       
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
            $paramChk = array(
                'mobile' => $dataArr[11],
                'card_number' => $dataArr[0],
                'product_id' => $dataArr['product_id']
            );
            $check = $this->checkCardholderDuplication($paramChk);
            $checkCard = $this->checkDuplicateCardhNumberBatch(array('card_number' => $dataArr[0],'batch_name' => $batchName));
            $checkCardNumber = $this->checkDuplicateCardhNumber(array('card_number' => $dataArr[0]));
            if (!$check) {
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
                $valid = $this->isValid($dataArr);
                         
                if (!$valid) {
                    $errMsg = $this->getError();
                    $errorMsg = empty($errMsg) ? 'details invalid' : $errMsg;

                    $status = STATUS_FAILED;
                    $failed_reason = $errorMsg;
                } 
            }
        }
        
        $data = array(
            'card_number' => $dataArr[0],
            'card_pack_id' => $dataArr[1],
            'afn' => $dataArr[2],
            'medi_assist_id' => $dataArr[3],
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
            'corp_address_line1' => $dataArr[21],
            'corp_address_line2' => $dataArr[22],
            'corp_city' => $dataArr[23],
            'corp_pin' => $dataArr[24],
            'id_proof_type' => $dataArr[25],
            'id_proof_number' => $dataArr[26],
            'address_proof_type' => $dataArr[27],
            'address_proof_number' => $dataArr[28],
            'by_ops_id' => $user->id,
            'batch_name' => $batchName,
            'product_id' => $dataArr['product_id'],
            'failed_reason' => $failed_reason,
            'upload_status' => $status,
            'date_created' => new Zend_Db_Expr('NOW()')
        );
        if (!empty($user->corporate_code)) {
            $data['by_ops_id'] = 0;
            $data['by_agent_id'] = 0;
            $data['by_corporate_id'] = $user->id;
        } else if (!empty($user->agent_code)) {
            $data['by_ops_id'] = 0;
            $data['by_agent_id'] = $user->id;
            $data['by_corporate_id'] = 0;
        }

        $this->_db->insert(DbTable::TABLE_RAT_CORP_CARDHOLDER_BATCH, $data);
        return TRUE;
    }
*/


      public function insertCardholderBatch($dataArr, $batchName, $status) {
        $user = Zend_Auth::getInstance()->getIdentity();
        $cardInfo = array('id' => 0,
           'card_number' => '');
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
            $checkCardPackId = $this->checkDuplicateCardPackId(array('card_pack_id' => $dataArr[1]));
            
            $checkCardPackIdErrMsg='';
            $crnObj = new CRNMaster();
            $cardInfo = $crnObj->getCRNInfoByProduct('',$dataArr[1],$dataArr['product_id']);
            if(!isset($cardInfo['id']) && empty($cardInfo['id'])){
                 $checkCardPackId = false;
                 $checkCardPackIdErrMsg ='CARD PACK ID not found';
            }elseif(!empty($dataArr[0]) && $cardInfo['card_number'] != $dataArr[0]){
                  $checkCardPackId = false;
                  $checkCardPackIdErrMsg ='CARD PACK ID And Card Number combination not matched';
            }else{
                //Not Required to update as its getting updated while Registration
                 //$crnObj->updateStatusByCardNumberNPackId(array('card_pack_id'=>$dataArr[1],'card_number' => $dataArr[0],'product_id'=>$dataArr['product_id'],'status'=>STATUS_USED));
            }
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
            }elseif(!$checkCardPackId){
                  $status = STATUS_FAILED;
                  if($checkCardPackIdErrMsg != ''){
                    $failed_reason = $checkCardPackIdErrMsg;  
                  }else{
                    $failed_reason = 'Card pack id already exists';  
                  }
            }
            else
            {
                $valid = $this->isValid($dataArr);
                         
                if (!$valid) {
                    $errMsg = $this->getError();
                    $errorMsg = empty($errMsg) ? 'details invalid' : $errMsg;

                    $status = STATUS_FAILED;
                    $failed_reason = $errorMsg;
                } 
            }
            
            if($status != STATUS_TEMP)
            {
                $crnObj->updateStatusByCardNumberNPackId(array('card_number'=>$dataArr[0],'card_pack_id'=>$dataArr[1],'product_id'=>$dataArr['product_id'],'status'=>STATUS_FREE), TRUE);
            }
        }
        
        $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        if((isset($cardInfo['card_number'])) && ($cardInfo['card_number'] != '')){
            $card_number = addslashes(trim($cardInfo['card_number'])) ;
            $card_numberEnc = new Zend_Db_Expr("AES_ENCRYPT('" . $card_number . "','" . $encryptionKey . "')");
        } else {
            $card_numberEnc = '';
        }
            
        $data = array(
            'bank_id' => $dataArr['bank_id'],
            'card_number' => $card_numberEnc,
            'card_pack_id' => $dataArr[1],
            'afn' => $dataArr[2],
            'medi_assist_id' => $dataArr[3],
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
            'corp_address_line1' => $dataArr[21],
            'corp_address_line2' => $dataArr[22],
            'corp_city' => $dataArr[23],
            'corp_pin' => $dataArr[24],
            'id_proof_type' => $dataArr[25],
            'id_proof_number' => $dataArr[26],
            'address_proof_type' => $dataArr[27],
            'address_proof_number' => $dataArr[28],
            'by_ops_id' => $user->id,
            'batch_name' => $batchName,
            'product_id' => $dataArr['product_id'],
            'failed_reason' => $failed_reason,
            'upload_status' => $status,
            'date_created' => new Zend_Db_Expr('NOW()')
        );
        if (!empty($user->corporate_code)) {
            $data['by_ops_id'] = 0;
            $data['by_agent_id'] = 0;
            $data['by_corporate_id'] = $user->id;
        } else if (!empty($user->agent_code)) {
            $data['by_ops_id'] = 0;
            $data['by_agent_id'] = $user->id;
            $data['by_corporate_id'] = 0;
        }
        $this->_db->insert(DbTable::TABLE_RAT_CORP_CARDHOLDER_BATCH, $data);
        return TRUE;
    }

    /* searchRemitter() will return remitters details after searching remitter
     */

    public function searchCardholder($param) {
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $columnName = $param['searchCriteria'];
        $keyword = $param['keyword'];
        if($columnName == 'card_number'){ 
            //$keyword = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number");
            $columnName = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."')");
        } 
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number");
        $crn = new Zend_Db_Expr("AES_DECRYPT(`crn`,'".$decryptionKey."') as crn");
        $productId = $param['product_id']; 
        $whereString = "$columnName LIKE '%$keyword%' AND product_id = '$productId'"; 
        $details = $this->_db->select()
                ->from(DbTable::TABLE_RAT_CORP_CARDHOLDER, array('id', $crn,'customer_master_id', $card_number, 'afn', 'medi_assist_id', 'employee_id', 'concat(first_name," ",last_name) as name', 'gender', 'date_of_birth', 'mobile', 'email', 'employer_name', 'corporate_id', 'status', 'name_on_card', 'date_failed', 'failed_reason'))
                ->where($whereString)
                ->where("status = '".STATUS_ACTIVE."'")
                ->order('first_name DESC');
        return $this->_db->fetchAll($details);
    }

    public function emailDuplication($email) {
        $where = " email = '" . $email . "' AND (status = '" . STATUS_ACTIVE . "')";
        $select = $this->select()
                ->from(DbTable::TABLE_RAT_CORP_CARDHOLDER, array('id'))
                ->where($where);

        return $this->fetchRow($select);
    }

    public function mobileDuplication($mobile) {
        $where = " mobile = '" . $mobile . "' AND (status = '" . STATUS_ACTIVE . "')";
        $select = $this->select()
                ->from(DbTable::TABLE_RAT_CORP_CARDHOLDER, array('id'))
                ->where($where);

        return $this->fetchRow($select);
    }

    /* searchRemitter() will return remitters details after searching remitter
     */

    public function searchByMediAssistId($id) {
        
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number");
        $crn = new Zend_Db_Expr("AES_DECRYPT(`crn`,'".$decryptionKey."') as crn");
        
        $details = $this->_db->select()
                ->from(DbTable::TABLE_RAT_CORP_CARDHOLDER, array('id', $crn, $card_number, 'afn', 'medi_assist_id', 'employee_id', 'concat(first_name," ",last_name) as name', 'gender',
                    'date_of_birth', 'mobile', 'email', 'employer_name', 'corporate_id', 'status', 'product_id', 'customer_master_id'))
                ->where("status = '" . STATUS_ACTIVE . "'")
                ->where("medi_assist_id = '$id'");
        return $this->_db->fetchRow($details);
    }

    /* checkPANDuplication() is responsible to verify the unique PAN number
     * param: pan no.
     */

    public function checkPANDuplication($pan) {

        if (trim($pan) == '')
            throw new Exception('PAN not found!');

        $where = "lower(rcm.pan) ='" . strtolower($pan) . "'";
        $where .= " AND (rcm.status='" . STATUS_ACTIVE . "' OR rcm.status='" . STATUS_INACTIVE . "')";

        $select = $this->_db->select()
                ->from(DbTable::TABLE_RAT_CUSTOMER_MASTER . ' as rcm', array('id'))
                ->where($where);
        $row = $this->_db->fetchRow($select);
        if (empty($row))
            return false;
        else
            throw new Exception('Cardholder with same PAN existed already!');
    }

    /* checkAadhaarDuplication() is responsible to verify the unique Aadhaar number
     * param: Aadhaar no.
     */

    public function checkAadhaarDuplication($aadhaarNo) {

        if (trim($aadhaarNo) == '')
            throw new Exception('Aadhaar number not found!');

        $where = "rcm.aadhaar_no ='" . $aadhaarNo . "'";
        $where .= " AND (rcm.status='" . STATUS_ACTIVE . "' OR rcm.status='" . STATUS_INACTIVE . "')";

        $select = $this->_db->select()
                ->from(DbTable::TABLE_RAT_CUSTOMER_MASTER . ' as rcm', array('id'))
                ->where($where);
        $row = $this->_db->fetchRow($select);
        if (empty($row))
            return false;
        else
            throw new Exception('Cardholder with same Aadhaar Number existed already!');
    }

    public function checkCardholderDuplicationinMaster($param) {
        $emailModel = new Email();
        $mobileModel = new Mobile();
        $aadhar = isset($param['aadhaar_no']) ? $param['aadhaar_no'] : '';
        $pan = isset($param['pan']) ? $param['pan'] : '';
        $email = $param['email'];
        $mobile = $param['mobile'];

        if (isset($aadhar) && $aadhar != '') {
            $aadharDup = $this->checkAadhaarDuplication($aadhar);
            if (!$aadharDup)
                return TRUE;
        }
        else if (isset($pan) && $pan != '') {
            $panDup = $this->checkPANDuplication($pan);
            if (!$panDup)
                return TRUE;
        }
        else if (isset($email) && $email != '') {
            $emailnotDup = $emailModel->checkCorpCardholderEmailDuplicate($email);
            if ($emailnotDup)
                return TRUE;
        }
        else if (isset($mobile) && $mobile != '') {
            $mobilenotDup = $mobileModel->checkCorpCardholderMobileDuplicate($mobile);
            if ($mobilenotDup)
                return TRUE;
        }
        else
            return FALSE;
    }

    /* addCardholder () will add the cardholder details with different corresponding tables
     */

    public function addCardholder($data) {
        if (empty($data))
            throw new Exception('Data missing for add cardholder');

        $objCustomerMaster = new CustomerMaster();
        
        $this->_db->beginTransaction();

        try {
            // adding data in customer master table
            $customerMasterData = array(
                'shmart_crn' => $data['shmart_crn'],
                'bank_id' => $data['bank_id'],
                'status' => STATUS_INACTIVE,
            );

            $cusomerMasterId = $objCustomerMaster->add($customerMasterData);


            // adding data in rat_customer_master table

            $ratCustomerMasterData = array(
                'customer_master_id' => $cusomerMasterId,
                'shmart_crn' => $data['shmart_crn'],
                'first_name' => $data['first_name'],
                'middle_name' => $data['middle_name'],
                'last_name' => $data['last_name'],
                'aadhaar_no' => $data['aadhaar_no'],
                'pan' => $data['pan'],
                'mobile_country_code' => $data['mobile_country_code'],
                'mobile' => $data['mobile'],
                'email' => $data['email'],
                'gender' => $data['gender'],
                'date_of_birth' => $data['date_of_birth'],
                'status' => STATUS_ACTIVE,
            );

            $this->addRatCustomerMaster($ratCustomerMasterData);
            
            // adding data in rat_corp_cardholders table
            $ratHicCardholdersData = array(
                'customer_master_id' => $cusomerMasterId,
                'unicode' => $data['unicode'],
                'crn' => $data['shmart_crn'],
                'card_number' => $data['card_number'],
                'afn' => $data['afn'],
                'medi_assist_id' => $data['medi_assist_id'],
                'employee_id' => $data['employee_id'],
                'first_name' => $data['first_name'],
                'middle_name' => $data['middle_name'],
                'last_name' => $data['last_name'],
                'aadhaar_no' => $data['aadhaar_no'],
                'pan' => $data['pan'],
                'gender' => $data['gender'],
                'date_of_birth' => $data['date_of_birth'],
                'mobile' => $data['mobile'],
                'email' => $data['email'],
                'employer_name' => $data['employer_name'],
                'by_agent_id' => $data['agent_id'],
                'status' => STATUS_ACTIVE,
                'date_created' => new Zend_Db_Expr('NOW()'),
		'channel'   => $data['channel'],
            );

            $this->addCustomer($data);

            $this->_db->commit();
        } catch (Exception $e) {
            // If any of the queries failed and threw an exception,
            // we want to roll back the whole transaction, reversing
            // changes made in the transaction, even those that succeeded.
            // Thus all changes are committed together, or none are.
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_db->rollBack();
            //throw new Exception ("Transaction not completed due to system failure");
            throw new Exception($e->getMessage());
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
        }


        return TRUE;
    }

    /* addRatCustomerMaster() will add the info in ratnakar customer master
     */

    public function addRatCustomerMaster($data) {
        if (empty($data))

           throw new Exception(ErrorCodes::ERROR_INVALID_DATA_ADD_CUST_FAILURE_MSG, ErrorCodes::ERROR_INVALID_DATA_ADD_CUST_FAILURE_CODE); 
        $this->_db->insert(DbTable::TABLE_RAT_CUSTOMER_MASTER, $data);
        return $this->_db->lastInsertId();
    }

    /* addRatHicCardholder() will add the info in ratnakar hic cardholder
     */

    public function addRatCorpCardholder($data) {
        if (empty($data))
            throw new Exception('Data missing while adding customer details');
            
        if((isset($data['crn'])) && ($data['crn'] != '')){
            $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
            $data['crn'] = new Zend_Db_Expr("AES_ENCRYPT('".$data['crn']."','".$encryptionKey."')");
        }
        if((isset($data['card_number'])) && ($data['card_number'] != '')){
            $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
            $data['card_number'] = new Zend_Db_Expr("AES_ENCRYPT('".$data['card_number']."','".$encryptionKey."')");
        }
        $this->_db->insert(DbTable::TABLE_RAT_CORP_CARDHOLDER, $data);
    }

    /* checkCardNumberDuplication() is responsible to verify the unique Card number
     * param: Card no.
     */

    public function checkCardNumberDuplication($cardNo) {

        if (trim($cardNo) == ''){
            throw new Exception('Card number not found!');
        }
        
        $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $cardNo = new Zend_Db_Expr("AES_ENCRYPT('".$cardNo."','".$encryptionKey."')");
            
        $select = $this->_db->select()
                ->from(DbTable::TABLE_RAT_CORP_CARDHOLDER . ' as rhc', array('id'));
        $select->where("rhc.card_number = ?", $cardNo); 
        $select->where("rhc.status='" . STATUS_ACTIVE . "' OR rhc.status='" . STATUS_INACTIVE . "'"); 
        
        $row = $this->_db->fetchRow($select);
        if (empty($row))
            return false;
        else
            throw new Exception('Card Number already exists');
    }

    /* checkAFNDuplication() is responsible to verify the unique afn
     * param: afn.
     */

    public function checkAFNDuplication($afn) {

        if (trim($afn) == '')
            throw new Exception('AFN not found!');

        $where = "rhc.afn ='" . $afn . "'";
        $where .= " AND (rhc.status='" . STATUS_ACTIVE . "' OR rhc.status='" . STATUS_INACTIVE . "')";

        $select = $this->_db->select()
                ->from(DbTable::TABLE_RAT_CORP_CARDHOLDER . ' as rhc', array('id'))
                ->where($where);
        $row = $this->_db->fetchRow($select);
        if (empty($row))
            return false;
        else
            throw new Exception('AFN already exists!');
    }

    public function bulkAddCardholder($idArr, $batchName ,$status = STATUS_ECS_PENDING, $channel) {
        if (empty($idArr))
            throw new Exception('Data missing for add cardholder');
        $custProductModel = new Corp_Ratnakar_CustomerProduct();
        $custPurseModel = new Corp_Ratnakar_CustomerPurse();
        $masterPurseModel = new MasterPurse();
        $objCustomerMaster = new CustomerMaster();
        $bankModel = new Banks();
        $productModel = new Products();
        $crnModel = new CRN();
        $customerTrackModel = new CustomerTrack();
        
        $user = Zend_Auth::getInstance()->getIdentity();
        $str = '';
        try {
            // Foreach selected id value
            foreach ($idArr as $id) {
                $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
                $card_number = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number"); 
                $select = $this->_db->select()
                        ->from(DbTable::TABLE_RAT_CORP_CARDHOLDER_BATCH, array('id', 'bank_id', 'product_id', $card_number, 'card_pack_id', 'afn', 'medi_assist_id', 'employee_id', 'first_name', 'middle_name', 'last_name', 'name_on_card', 'gender', 'date_of_birth', 'mobile', 'email', 'landline', 'address_line1', 'address_line2', 'city', 'pincode', 'mother_maiden_name', 'employer_name', 'corporate_id', 'corp_address_line1', 'corp_address_line2', 'corp_city', 'corp_pin', 'id_proof_type', 'id_proof_number', 'address_proof_type', 'address_proof_number', 'by_ops_id', 'by_agent_id', 'by_corporate_id', 'batch_name', 'date_created', 'date_updated', 'upload_status', 'failed_reason'))
                        ->where("id =?", $id);

                $dataArr = $this->_db->fetchRow($select);  
                $this->_db->beginTransaction();
                $checkFile = $this->checkFilename($dataArr['batch_name']);
                if (!$checkFile) 
                {
                    $updateArr = array('upload_status' => STATUS_FAILED, 'failed_reason' => 'Filename already uploaded');
                    $this->_db->update(DbTable::TABLE_RAT_CORP_CARDHOLDER_BATCH, $updateArr, "id= $id");
                }
                else
                { 
                    $checkPin = $this->checkPinCity($dataArr['city'], $dataArr['pincode']);
                    if (!$checkPin) 
                    {
                        $updateArr = array('upload_status' => STATUS_FAILED, 'failed_reason' => 'Pin Code/city master validation failed');
                        $this->_db->update(DbTable::TABLE_RAT_CORP_CARDHOLDER_BATCH, $updateArr, "id= $id");
                    }
                    else
                    { 
                        $paramChk = array(
                            'mobile' => $dataArr['mobile'],
                            'medi_assist_id' => $dataArr['medi_assist_id'],
                            'card_number' => $dataArr['card_number'],
//                            'product_id' => $dataArr['product_id'],
                        );
                        $check = $this->checkCardholderDuplication($paramChk);
                        $checkCard = $this->checkDuplicateCardhNumber(array('card_number' => $dataArr['card_number']));    
                        if (!$check) {
                              $updateArr = array('upload_status' => STATUS_FAILED, 'failed_reason' => 'Duplicate record');
                              $this->_db->update(DbTable::TABLE_RAT_CORP_CARDHOLDER_BATCH, $updateArr, "id= $id");
                         }elseif(!$checkCard){
                              $updateArr = array('upload_status' => STATUS_FAILED, 'failed_reason' => 'Duplicate record');
                              $this->_db->update(DbTable::TABLE_RAT_CORP_CARDHOLDER_BATCH, $updateArr, "id= $id");
                         }
                        else
                        {
                            $productDetail = $productModel->getProductInfo($dataArr['product_id']);
                            $customerMasterId = $objCustomerMaster->generateCustomerId(array('bank_id' => $productDetail['bank_id']));
                            $custMasterDetails = $objCustomerMaster->findById($customerMasterId);
                            $custMasterDetail = Util::toArray($custMasterDetails);
                            // adding data in rat_customer_master table
                            
                            $ratCustomerMasterData = array(
                                'bank_id' => $dataArr['bank_id'],
                                'customer_master_id' => $customerMasterId,
                                'shmart_crn' => $custMasterDetail['shmart_crn'],
                                'first_name' => $dataArr['first_name'],
                                'middle_name' => $dataArr['middle_name'],
                                'last_name' => $dataArr['last_name'],
                                'aadhaar_no' => (isset($dataArr['IdentityProofType']) && strtolower($dataArr['IdentityProofType']) == 'aadhar card') ? $dataArr['IdentityProofDetail'] : '',
                                'pan' => (isset($dataArr['IdentityProofType']) && strtolower($dataArr['IdentityProofType']) == 'pan card') ? $dataArr['IdentityProofDetail'] : '',
                                'mobile_country_code' => isset($dataArr['mobile_country_code']) ? $dataArr['mobile_country_code'] : '',
                                'mobile' => $dataArr['mobile'],
                                'email' => $dataArr['email'],
                                'gender' => $dataArr['mobile'],
                                'date_of_birth' => isset($dataArr['date_of_birth']) ? $dataArr['date_of_birth'] : '',
                                'status' => STATUS_ACTIVE,
                            );
                            
                            $ratCustomerId = $this->addRatCustomerMaster($ratCustomerMasterData);
                            //echo 'ratCustomerId=>'.$ratCustomerId; exit;
                            //insert into customer purse
                            $purseDetails = $masterPurseModel->getPurseDetailsbyBankIdProductId($dataArr['product_id'], $productDetail['bank_id']);
                            foreach ($purseDetails as $purseDetail) {
                                $purseArr = array(
                                    'customer_master_id' => $customerMasterId,
                                    'rat_customer_id' => $ratCustomerId,
                                    'product_id' => $dataArr['product_id'],
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
                            
                       $customerType = ($productDetail['const'] == PRODUCT_CONST_RAT_CNY) ? TYPE_KYC : TYPE_NONKYC;
                              
                            
                        $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
                        $card_number = new Zend_Db_Expr("AES_ENCRYPT('".$dataArr['card_number']."','".$encryptionKey."')"); 
                        
                            $data = array(
                                'bank_id' => $dataArr['bank_id'],
                                'afn' => $dataArr['afn'],
                                'card_number' => $card_number,
                                'crn' => $card_number,
                                'card_pack_id' => $dataArr['card_pack_id'],
                                'rat_customer_id' => $ratCustomerId,
                                'customer_master_id' => $customerMasterId,
                                'medi_assist_id' => $dataArr['medi_assist_id'],
                                'employee_id' => $dataArr['employee_id'],
                                'customer_type' => $customerType,
                                'first_name' => $dataArr['first_name'],
                                'middle_name' => $dataArr['middle_name'],
                                'last_name' => $dataArr['last_name'],
                                'name_on_card' => $dataArr['name_on_card'],
                                'gender' => Util::getGenderTxt($dataArr['gender']),
        //                        'gender' => $dataArr['gender'],
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
                                'corporate_id' => $dataArr['corporate_id'],
                                'corp_address_line1' => $dataArr['corp_address_line1'],
                                'corp_address_line2' => $dataArr['corp_address_line2'],
                                'corp_city' => $dataArr['corp_city'],
                                'corp_pin' => $dataArr['corp_pin'],
                                'id_proof_type' => $this->mediAssistIdProofType($dataArr['id_proof_type']),
                                'id_proof_number' => $dataArr['id_proof_number'],
                                'address_proof_type' => $this->mediAssistAddressProofType($dataArr['address_proof_type']),
                                'address_proof_number' => $dataArr['address_proof_number'],
                                'by_ops_id' => $user->id,
                                'batch_id' => $dataArr['id'],
                                'batch_name' => $dataArr['batch_name'],
                                'product_id' => $dataArr['product_id'],
                                'status_ops' => STATUS_APPROVED,
                                'status_ecs' => STATUS_WAITING,
                                'status' => $status,
                                'date_created' => new Zend_Db_Expr('NOW()'),
				'channel' => $channel
                            );
                            if(!empty($user->corporate_code)){
                                $data['by_ops_id'] = 0;
                                $data['by_agent_id'] = 0;
                                $data['by_corporate_id']= $user->id;
                                $data['status_ops']= ($customerType == TYPE_KYC)? STATUS_PENDING : STATUS_APPROVED;
                            }
                            else if(!empty($user->agent_code)){
                                $data['by_ops_id'] = 0;
                                $data['by_agent_id'] = $user->id;
                                $data['by_corporate_id'] = 0;
                                $data['status_ops']= ($customerType == TYPE_KYC)? STATUS_PENDING : STATUS_APPROVED;
                            }
                            $this->insert($data);
                            $cardholderId = $this->_db->lastInsertId(DbTable::TABLE_RAT_CORP_CARDHOLDER, 'id');
                            $updateArr = array('upload_status' => STATUS_PASS);
                            $this->_db->update(DbTable::TABLE_RAT_CORP_CARDHOLDER_BATCH, $updateArr, "id= $id");

                            // Entry in Customer product table
                            $prodArr = array('product_customer_id' => $cardholderId,
                                'product_id' => $dataArr['product_id'],
                                'program_type' => $productDetail['program_type'],
                                'bank_id' => $productDetail['bank_id'],
                                'by_agent_id' => (isset($dataArr['by_api_user_id']) && $dataArr['by_api_user_id'] > 0 )? $dataArr['by_api_user_id'] : 0 ,
                                'by_ops_id' => (CURRENT_MODULE == MODULE_OPERATION)? $user->id:0,
                                'by_corporate_id' =>  (isset($dataArr['corporate_id']) && $dataArr['corporate_id'] > 0 )? $dataArr['corporate_id'] : 0,
                                'date_created' => new Zend_Db_Expr('NOW()'),
                                'status' => STATUS_ACTIVE);
                            $custProductModel->save($prodArr);
            
                           $custDetailArr = array(
                                'rat_cardholder_id' => $cardholderId,
                                'product_id' => $dataArr['product_id'],
                                'employer_name' => $dataArr['employer_name'],
                                'emp_address_line1' => $dataArr['corp_address_line1'],
                                'emp_address_line2' => $dataArr['corp_address_line2'],
                                'emp_city' => $dataArr['corp_city'],
                                'emp_state' => $dataArr['corp_state'],
                                'emp_pin' => $dataArr['corp_pin'],
                                'date_created' => new Zend_Db_Expr('NOW()'),
                                'status' => STATUS_ACTIVE
                            );
                           
                            $this->insertCardhoderDetail($custDetailArr);
                        } 
                    }
                }
                $this->_db->commit();
            }// END of foreach loop
            $notInid = implode(",", $idArr);
            $rejectedArr = array('upload_status' => STATUS_REJECTED);


            $this->_db->update(DbTable::TABLE_RAT_CORP_CARDHOLDER_BATCH, $rejectedArr, "id NOT IN ($notInid) AND batch_name = '$batchName' AND upload_status = '".STATUS_TEMP."'");
        } catch (Exception $e) {
            
            //echo "<pre>";print_r($e); exit;
            // If any of the queries failed and threw an exception,
            // we want to roll back the whole transaction, reversing
            // changes made in the transaction, even those that succeeded.
            // Thus all changes are committed together, or none are.
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_db->rollBack();
            //throw new Exception ("Transaction not completed due to system failure");
            throw new Exception($e->getMessage());
        }
        return TRUE;
    }

    /*
     *  getCardholderSearchSql function will return the sql for cardholder search
     *  as params:- medi assis id, employer name, card number, mobile, email,aadhaar no, pan
     *  any of above params can be accepted
     */

    public function getCardholderSearchSql($param) {
        //echo '<pre>';print_r($param);exit;
        $mediAssistId = isset($param['medi_assist_id']) ? $param['medi_assist_id'] : '';
        $partnerRefNum = isset($param['partner_ref_no']) ? $param['partner_ref_no'] : '';
        $employerName = isset($param['employer_name']) ? $param['employer_name'] : ''; 
        if(isset($param['card_number'])){
            $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
            $cardNumber = new Zend_Db_Expr("AES_ENCRYPT('".$param['card_number']."','".$encryptionKey."')");
        } else {
            $cardNumber = '';
        }
        $mobile = isset($param['mobile']) ? $param['mobile'] : '';
        $employeeId = isset($param['employee_id']) ? $param['employee_id'] : '';
        $email = isset($param['email']) ? $param['email'] : '';
        $aadhaarNo = isset($param['aadhaar_no']) ? $param['aadhaar_no'] : '';
        $pan = isset($param['pan']) ? $param['pan'] : '';
        $cardholderId = isset($param['cardholder_id']) ? $param['cardholder_id'] : '';
        $customerMasterId = isset($param['customer_master_id']) ? $param['customer_master_id'] : '';
        $byCorporateId = isset($param['by_corporate_id']) ? $param['by_corporate_id'] : '';
        $status = isset($param['status']) ? $param['status'] : '';
        $orderBy = isset($param['order']) ? $param['order'] : '';
        $productId = isset($param['product_id']) ? $param['product_id'] : '';
        $txnRefNum = isset($param['txnrefnum']) ? $param['txnrefnum'] : '';
        $txnCode = isset($param['txn_code']) ? $param['txn_code'] : '';
        $ratCustomerId = isset($param['rat_customer_id']) ? $param['rat_customer_id'] : '';
            
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`rhc`.`card_number`,'".$decryptionKey."') as card_number");

        $select = $this->select();
        $select->from(DbTable::TABLE_RAT_CORP_CARDHOLDER . " as rhc", 
                array('rhc.id as id','rhc.customer_master_id', $card_number,'rhc.rat_customer_id',
                    'rhc.medi_assist_id', 'rhc.employee_id', 
                    'concat(rhc.first_name," ",rhc.last_name) as cardholder_name', 'rhc.gender', 
                    'rhc.mobile', 'rhc.email', 'rhc.employer_name', 'rhc.status as cardholder_status', 
                    'rhc.afn', 'rhc.mobile', 'rhc.first_name', 'rhc.middle_name', 'rhc.last_name', 
                    'rhc.date_of_birth', 'rhc.batch_name', 'rhc.corporate_id', 'rhc.product_id', 'rhc.city', 'rhc.customer_type','rhc.partner_ref_no','rhc.bank_id','rhc.txn_code','rhc.txnrefnum', 'rhc.address_line1', 'rhc.address_line2', 'rhc.name_on_card', 'rhc.mother_maiden_name', 'rhc.pincode', 'rhc.by_agent_id'));
        $select->setIntegrityCheck(false);
        //$select->where("rhc.status = '".STATUS_ACTIVE."'");
        if ($mediAssistId != ''){
            $select->where("rhc.medi_assist_id = '" . $mediAssistId . "'");
        }
        if ($partnerRefNum != ''){
            $select->where("rhc.partner_ref_no = '" . $partnerRefNum . "'");
        }
        if ($employerName != ''){
            $select->where("rhc.employer_name like '%" . $employerName . "%'");
        }
        if ($cardNumber != ''){
            $select->where("rhc.card_number = ?", $cardNumber);
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
        if ($customerMasterId != ''){
            $select->where("rhc.customer_master_id = '" . $customerMasterId . "'");
        }
        if ($byCorporateId != ''){
            $select->where("rhc.by_corporate_id = '" . $byCorporateId . "'");
        }
        if ($productId != ''){
            $select->where("rhc.product_id = '" . $productId . "'");
        }
        if ($txnRefNum != ''){
            $select->where("rhc.txnrefnum = '" . $txnRefNum . "'");
        }
        
        if ($txnCode != ''){
            $select->where("rhc.txn_code = '" . $txnCode . "'");
        }
        if ($status != ''){
            $select->where("rhc.status = '" . $status . "'");
        }
        if ($ratCustomerId != ''){
            $select->where("rhc.rat_customer_id = '" . $ratCustomerId . "'");
        }
        if ($orderBy != ''){
            $select->order($orderBy);
        }else{
            $select->order("cardholder_name");
        }
        return $select;
    }

    /* getCardholderSearch() will search cardholder of medi assist of ratnakar bank
     * param: medi assis id, employer name, card number, mobile, email,aadhaar no, pan
     * any of above params can be accepted
     */

    public function getCardholderSearch($param, $page, $paginate = NULL) {
        $select = $this->getCardholderSearchSql($param);

        $result = $this->fetchAll($select);
        $result = $result->toArray();

        return $result;
    }

    /* getCardholderInfo() will find cardholder details
     * param: cardholder id
     */

    public function getCardholderInfo($param) {
        $select = $this->getCardholderSearchSql($param);
        return $this->fetchRow($select);
    }
    
    public function updateCardholderById($dataOld, $dataNew, $user_id, $remarks = '', $userType = USER_TYPE_AGENT) {
        if ($userType == USER_TYPE_AGENT)
            $newDataUpdate = array('status' => $dataNew['status']);
        else
            $newDataUpdate = $dataNew;

        $cardholderId = $dataOld['id'];

        $update = $this->_db->update(DbTable::TABLE_RAT_CORP_CARDHOLDER, $newDataUpdate, 'id="' . $cardholderId . '"');

        App_Logger::masterLog(array(
            'txt_old' => $dataOld,
            'txt_new' => $dataNew,
            'user_type' => $userType,
            'user_id' => $user_id, //if user_type = agent then agent user id
            'table' => DbTable::TABLE_RAT_CORP_CARDHOLDER,
            'functionality' => FUNCTIONALITY_UPDATE_CARDHOLDER,
            'remarks' => $remarks,
        ));

        return TRUE;
    }

    public function generateRandom6DigitCode() {
        return rand(111111, 999999);
    }

    /* getRatMasterDetails() is responsible to get details
     *
     */

    public function getRatMasterDetails($custMasterId) {
        $select = $this->_db->select()
                ->from(DbTable::TABLE_RAT_CUSTOMER_MASTER . ' as rcm')
                ->where("customer_master_id =?", $custMasterId)
                ->where("status =?", STATUS_ACTIVE);
        $row = $this->_db->fetchRow($select);
        return $row;
    }

    public function getCardholderDetails($data) {
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number");
        $crn = new Zend_Db_Expr("AES_DECRYPT(`crn`,'".$decryptionKey."') as crn");
        $details = $this->select()
                ->from(DbTable::TABLE_RAT_CORP_CARDHOLDER, array('id', $crn, $card_number, 'afn', 'medi_assist_id', 'employee_id', 'concat(first_name," ",last_name) as name', 'gender',
                    'date_of_birth', 'mobile', 'email', 'employer_name', 'corporate_id', 'status', 'product_id', 'customer_master_id'))
                ->setIntegrityCheck(false)
                ->where("id =?", $data['id'])
                ->where("customer_master_id =?", $data['customer_master_id'])
                ->where("product_id =?", $data['product_id']);
//                ->where("status='".STATUS_ACTIVE."'")
//                ->where("upload_status='" . STATUS_PASS . "' OR upload_status='" . STATUS_DUPLICATE . "'");
        return $this->fetchRow($details);
    }

    public function checkCardholderMobileDuplication($mobile) {
        if (isset($mobile) && $mobile != '') {
            $select = $this->select();
            $select->from(DbTable::TABLE_RAT_CORP_CARDHOLDER, array('id'));
            $select->where("mobile =?", $mobile);
            $rs = $this->fetchRow($select);
            if (empty($rs)) {
                return TRUE;
            } else {
                return FALSE;
            }
        }
        else
            return FALSE;
    }
    
    public function checkFilename($fileName) {
        $select = $this->select();
        $select->from(DbTable::TABLE_RAT_CORP_CARDHOLDER, array('id'));
        $select->where("batch_name =?", $fileName);
        $select->where("status IN ('".STATUS_ACTIVE."', '".STATUS_INACTIVE."', '".STATUS_ECS_FAILED."')");
        $rs = $this->fetchRow($select);
        if (empty($rs)) {
            return TRUE;
        } else {
            return FALSE;
        }
        
    }
    
    public function checkCardholderDuplication($param) {
        $product = App_DI_Definition_BankProduct::getInstance(BANK_RATNAKAR_CORP);
        $productUnicode = $product->product->unicode;
        $productModel = new Products();
        $mediProduct = $productModel->getProductInfoByUnicode($productUnicode);
            
        if(isset($param['card_number'])){
            $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
            $cardNumber = new Zend_Db_Expr("AES_ENCRYPT('".$param['card_number']."','".$encryptionKey."')");
        } else {
            $cardNumber = '';
        }
        $mediAssistId = isset($param['medi_assist_id']) ? $param['medi_assist_id'] : '';
        $mobile = isset($param['mobile']) ? $param['mobile'] : '';
        $batchName = isset($param['batch_name']) ? $param['batch_name'] : '';
        $productId = isset($param['product_id']) ? $param['product_id'] : '';
        $partnerRefNo = isset($param['partner_ref_no']) ? $param['partner_ref_no'] : '';
        $select = $this->select();
        $select->from(DbTable::TABLE_RAT_CORP_CARDHOLDER, array('id'));
        if (isset($mediAssistId) && $mediAssistId != '') {
            $select->where("medi_assist_id =?", $mediAssistId);
        }
        if (isset($cardNumber) && $cardNumber != '') {
            $select->where("card_number =?", $cardNumber);
        }
        if (isset($mobile) && $mobile != '') {
            $select->where("mobile =?", $mobile);
        }
        if (isset($batchName) && $batchName != '') {
            $select->where("batch_name =?", $batchName);
        }
        if (isset($productId) && $productId != '') {
            $select->where("product_id =?", $productId);
        }
        if (isset($partnerRefNo) && $partnerRefNo != '') {
            $select->where("partner_ref_no =?", $partnerRefNo);
        }
        $select->where("status =? ", STATUS_ACTIVE);
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

    public function ratMediAssistECSRegn() {
        $custPurseModel = new Corp_Ratnakar_CustomerPurse();
        $masterPurseModel = new MasterPurse();
        $productModel = new Products();
        $custProductModel = new Corp_Ratnakar_CustomerProduct();
        $customerTrackModel = new CustomerTrack();
        $m = new \App\Messaging\Corp\Ratnakar\Operation();
        $user = Zend_Auth::getInstance()->getIdentity();
        
        $rat_arr = array(PRODUCT_CONST_RAT_MEDI);
        $productid_rat = $productModel->getProductIDbyConstArr($rat_arr); 
         
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`rcc`.`card_number`,'".$decryptionKey."') as card_number");
        $crn = new Zend_Db_Expr("AES_DECRYPT(`rcc`.`crn`,'".$decryptionKey."') as crn");
        
        $select = $this->_db->select()
                ->from(DbTable::TABLE_RAT_CORP_CARDHOLDER .' as rcc', array(
            'id', 'bank_id', 'product_id', 'rat_customer_id', 'customer_master_id', 'customer_type', $crn, 'unicode', $card_number, 'card_pack_id', 'afn', 'medi_assist_id', 'employee_id', 'partner_ref_no', 'txnrefnum', 'title', 'first_name', 'middle_name', 'last_name', 'name_on_card', 'gender', 'date_of_birth', 'aadhaar_no', 'pan', 'mobile', 'mobile2', 'email', 'landline', 'address_line1', 'address_line2', 'city', 'state', 'pincode', 'country', 'mother_maiden_name', 'employer_name', 'corporate_id', 'corp_address_line1', 'corp_address_line2', 'corp_city', 'corp_state', 'corp_pin', 'corp_country', 'id_proof_type', 'id_proof_number', 'id_proof_doc_id', 'address_proof_type', 'address_proof_number', 'address_proof_doc_id', 'occupation', 'is_card_activated', 'activation_date', 'is_card_dispatched', 'card_dispatch_date', 'by_ops_id', 'by_corporate_id', 'by_agent_id', 'batch_id', 'batch_name', 'date_created', 'date_updated', 'date_approval', 'date_toggle_kyc', 'date_activation', 'date_blocked', 'narration', 'txn_code', 'failed_reason', 'date_failed', 'date_crn_update', 'status_ecs', 'status_ops', 'status', 'aml_status'))
                ->where("status =?", STATUS_ECS_PENDING)
                ->where("product_id IN(?)", $productid_rat)
                ->order('id')
                ->limit(RAT_CORP_ECS_REGN_LIMIT);
        $dataArr = $this->_db->fetchAll($select);
        
        $numCust = 0;
        foreach ($dataArr as $data) {
            
            $id = $data['id'];


            $msg = '';
            try {
                $crnMaster = new CRNMaster();
                
                $crnInfo = $crnMaster->getInfoByCardNumberNPackId(array(
                   'card_number'    => $data['card_number'],
                   'card_pack_id'   => $data['card_pack_id'],
                   'product_id'     => $data['product_id'],
                   //'medi_assist_id'     => $data['medi_assist_id'],
                   'status'         => STATUS_FREE
                ));
                $crnInfo = Util::toArray($crnInfo);
                $sentToECS = FALSE;
                if(!empty($crnInfo)) {
                    $resp = TRUE;
                    
                    if(isset($crnInfo['member_id']) && !empty($crnInfo['member_id'])) {
                       if($crnInfo['member_id'] != $data['medi_assist_id']) {
                        $resp = false;
                        $msg = 'Initial Validation failed: Member Id not found in system';                        
                       }
                    }
                    if($resp) {
                        $cardholderArray = $this->getCardholderArray($data);
                        $sentToECS = TRUE;                    
                        $ecsApi = new App_Api_ECS_Corp_Ratnakar();
                        $resp = $ecsApi->mediAssistCardholderRegistration($cardholderArray);
                        if ($resp == false) {
                            $msg = $ecsApi->getError();
                        }
                    }
                } else {
                    $resp = false;
                    $msg = 'Initial Validation failed: Card Number or Card Pack Id not found in system';
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
                    'mobile_country_code' => isset($data['mobile_country_code']) ? $data['mobile_country_code'] : '', 'mobile' => $data['mobile'],
                    'email' => $data['email'],
                    'gender' => $data['gender'],
                    'date_of_birth' => isset($data['date_of_birth']) ? $data['date_of_birth'] : '',
                    'status' => STATUS_ACTIVE,
                );

                $ratCustomerId = $this->addRatCustomerMaster($ratCustomerMasterData);

                //insert into customer purse
                $purseDetails = $masterPurseModel->getPurseDetailsbyBankIdProductId($data['product_id'], $productDetail['bank_id']);
                foreach ($purseDetails as $purseDetail) {
                    $purseArr = array(
                        'customer_master_id' => $customerMasterId,
                        'rat_customer_id' => $ratCustomerId,
                        'product_id' => $data['product_id'],
                        'purse_master_id' => $purseDetail['id'],
                        'bank_id' => $productDetail['bank_id'],
                        //'by_ops_id' => $user->id,
                        'date_updated' => new Zend_Db_Expr('NOW()')
                    );
                    $purseParam = array('rat_customer_id' => $ratCustomerId, 'purse_master_id' => $purseDetail['id']);
                    $purseDetails = $custPurseModel->getCustPurseDetails($purseParam);
                    if (empty($purseDetails)) { // If purse entry not found
                        $custPurseModel->save($purseArr);
                    }
                }
                // Get Customer product details
                //Update customer product
                  $prodUpdateArr = array(
                        'rat_customer_id' => $ratCustomerId,
                    );
                 $custProductModel->update($prodUpdateArr,"product_customer_id = $id");
                
                // update the status to STATUS_ACTIVE in cardholders
                $updateArr = array('status' => STATUS_ACTIVE, 'customer_master_id' => $customerMasterId, 'rat_customer_id' => $ratCustomerId,'date_activation' => new Zend_Db_Expr('NOW()'), 'failed_reason' =>'');
                $this->_db->update(DbTable::TABLE_RAT_CORP_CARDHOLDER, $updateArr, "id= $id");
                
                
                $crnMaster->updateStatusByCardNumberNPackId(array(
                   'card_number'    => $data['card_number'],
                   'card_pack_id'   => $data['card_pack_id'],
                   'product_id'     => $data['product_id'],
                   'status'         => STATUS_USED
                ));
            
                $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
                $card_numberEnc = new Zend_Db_Expr("AES_ENCRYPT('".$data['card_number']."','".$encryptionKey."')"); 
            
             // Insert into customer Track
               $customerTrackArr =  array(
                    'card_number' => $card_numberEnc,
                    'card_pack_id' => $data['card_pack_id'],
                    'member_id' => $data['medi_assist_id'],
                    'crn' => $data['card_number'],
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
                // 
            } else {
                //On Failure
                if(!$sentToECS) { 
                    $updateArr = array('failed_reason' => $msg,
                        'date_failed' => new Zend_Db_Expr('NOW()'));
                    $this->update($updateArr, "id= $id");
                } else {
                    $updateArr = array('status' => STATUS_ECS_FAILED, 'failed_reason' => $msg,
                        'date_failed' => new Zend_Db_Expr('NOW()'));
                    $this->update($updateArr, "id= $id");
                }
                $updateprodArr = array('status' => STATUS_INACTIVE);
                $this->_db->update(DbTable::TABLE_RAT_CUSTOMER_PRODUCT, $updateprodArr, "product_customer_id= $id");
            }
        } catch (App_Exception $e) {
                $resp = false;
                $msg = $e->getMessage();
            } catch (Exception $e) {
                $resp = false;
                $msg = $e->getMessage();
            }
            $numCust++;
        }
        return $numCust;
    }

    public function getCardholderArray($param, $card_number='') {
        $ECSModel = new ECS();
        $state = new CityList();
        $dob = Util::returnDateFormatted($param['date_of_birth'], "Y-m-d", "d-m-Y", "-");
        $cityCode = $state->getCityCode(ucfirst(strtolower($param['city'])));
        if(!empty($param['unicode'])) {
            $ECSModel->assignMediassistCRN($param['id']);
        }

        if(!empty($card_number)) {
            $paramArray['cardNumber'] = $card_number;
        } else {
            $cardholderDetails = $this->findById($param['id']);
            $cardholder = Util::toArray($cardholderDetails); 
            $paramArray['cardNumber'] = $cardholder['card_number'];
        }
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
        
        $paramArray['legalid'] = (isset($param['medi_assist_id']) && !empty($param['medi_assist_id'])) ? $param['medi_assist_id'] : '';
        $paramArray['mothersmaidenname'] = $param['mother_maiden_name'];
        $paramArray['phonemobile'] = $param['mobile'];
        $paramArray['zipcode'] = $param['pincode'];
        if(isset($param['gender']) && in_array(strtolower($param['gender']), array('male','female'))) {
            if(strtolower($param['gender']) == 'male') $param['gender'] = 'M';
            if(strtolower($param['gender']) == 'female') $param['gender'] = 'F';
        }
            
        $paramArray['gender'] = !empty($param['gender']) ? $param['gender'] : 'M';

        return $paramArray;
    }

    public function getRatCardholderProductsAndBank($cardholder_id) {
        $product = App_DI_Definition_BankProduct::getInstance(BANK_RATNAKAR_CORP);
        $productUnicode = $product->product->unicode;

        $select = $this->_db->select()
                ->from(DbTable::TABLE_RAT_CUSTOMER_PRODUCT . " as cp", array('id', 'product_id', 'bank_id'))
                ->joinLeft(DbTable::TABLE_PRODUCTS . ' as p', "p.id = cp.product_id AND p.program_type = '" . PROGRAM_TYPE_CORP . "' AND p.unicode = '$productUnicode' ", array('program_type', 'unicode', 'bank_id'))
                ->joinLeft(DbTable::TABLE_BANK . ' as b', "b.id = cp.bank_id", array('unicode as bank_unicode'))
                ->where('cp.rat_customer_id =?', $cardholder_id)
                ->where("cp.status = '" . STATUS_ACTIVE . "' OR cp.status = '" . STATUS_ECS_PENDING . "' ");
        return $this->_db->fetchRow($select);
    }

    //Duplicate function of above
    public function getRatCardholderInfoProductsAndBank($cardholder_id) {
        $product = App_DI_Definition_BankProduct::getInstance(BANK_RATNAKAR_CORP);
        $productUnicode = $product->product->unicode;

        $select = $this->_db->select()
                ->from(DbTable::TABLE_RAT_CORP_CARDHOLDER . " as cp", array('id', 'product_id'))
                ->joinLeft(DbTable::TABLE_PRODUCTS . ' as p', "p.id = cp.product_id AND p.program_type = '" . PROGRAM_TYPE_CORP . "' AND p.unicode = '$productUnicode' ", array('program_type', 'unicode', 'bank_id'))
                ->joinLeft(DbTable::TABLE_BANK . ' as b', "b.id = p.bank_id", array('unicode as bank_unicode'))
                ->where('cp.id =?', $cardholder_id)
                ->where("cp.status = '" . STATUS_ACTIVE . "' OR cp.status = '" . STATUS_ECS_PENDING . "' ");
        return $this->_db->fetchRow($select);
    }

    public function getRatCardholderPurses($rat_customer_id=0, $page = 1) {
        $select = $this->select()
                ->setIntegrityCheck(false)
                ->from(DbTable::TABLE_RAT_CUSTOMER_PURSE . " as cp", array('amount'))
                ->joinLeft(DbTable::TABLE_PURSE_MASTER . ' as p', "p.id = cp.purse_master_id", array('name', 'description'))
                ->where('p.id = cp.purse_master_id')
                ->where('cp.rat_customer_id >0')
                ->where('cp.rat_customer_id =?', $rat_customer_id);
        $purse = $this->_paginate($select, $page, TRUE);
        
        return $purse;
    }

    
      /* checkIdNumberDuplication() is responsible to verify the unique indentification number
     * it will accept the identification number and agent id and identification type in param array and will return the exception message if duplicate else will return false
     */
     public function checkIdNumberDuplication($param){
         $idNo = isset($param['idNo'])?$param['idNo']:'';
         $idType = isset($param['idType'])?$param['idType']:'';
         $cardholderId = isset($param['cardholderId'])?$param['cardholderId']:'';
         
         if(trim($idNo)=='' || trim($idType)=='')
             throw new Exception('Identification Number not found!');
        
        $where = "rc.id_proof_number = '".$idNo."' AND rc.id_proof_type = '".$idType."' AND rc.status='".STATUS_ACTIVE."' AND id !='".$cardholderId."'";
        
        $select = $this->_db->select()
                ->from(DbTable::TABLE_RAT_CORP_CARDHOLDER .' as rc',array('id'))
                ->where($where);
        
        $row = $this->_db->fetchRow($select);
        if(empty($row))
            return false;
        else
            throw new Exception('Identification Number Exists!');
        }
        /* checkAddressNumberDuplication() is responsible to verify the unique address proof number
     * it will accept the address proof number and agent id in param array, and will return the exception message if duplicate else will return false
     */
     public function checkAddressNumberDuplication($param){
         $addressProofNo = isset($param['addressProofNo'])?$param['addressProofNo']:'';
         $cardholderId = isset($param['cardholderId'])?$param['cardholderId']:'';
         $addressProofType = isset($param['addressProofType'])?$param['addressProofType']:'';
         
         if(trim($addressProofNo)=='' || trim($addressProofType)=='')
             throw new Exception('Address Proof Number not found!');
         
        $where = "rc.address_proof_number ='".$addressProofNo."' AND rc.address_proof_type='".$addressProofType."' AND rc.status='".STATUS_ACTIVE."' AND id !='".$cardholderId."'";
       
        $select = $this->_db->select()
                 ->from(DbTable::TABLE_RAT_CORP_CARDHOLDER .' as rc',array('id'))
                 ->where($where);
               
        $row = $this->_db->fetchRow($select);
        if(empty($row))
            return false;
        else
            throw new Exception('Address Proof Number Exists!');
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
       public function getCardholders($param ,$dateCreated = FALSE) {
        $from = isset($param['from']) ? $param['from'] : '';
        $to = isset($param['to']) ? $param['to'] : '';
        $apprvfrom = isset($param['apprvfrom']) ? $param['apprvfrom'] : '';
        $apprvto = isset($param['apprvto']) ? $param['apprvto'] : '';
        $product = isset($param['product_id']) && $param['product_id'] > 0 ? $param['product_id'] : '';
        $corporateId = isset($param['by_corporate_id']) ? $param['by_corporate_id'] : '';
        $status = isset($param['status']) ? $param['status'] : '';
        $department = isset($param['department']) ? $param['department'] : '';
        $location = isset($param['location']) ? $param['location'] : '';
        $agent_id = isset($param['agent_id']) ? $param['agent_id'] : '';
        
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`rhc`.`card_number`,'".$decryptionKey."') as card_number");
        $crn = new Zend_Db_Expr("AES_DECRYPT(`rhc`.`crn`,'".$decryptionKey."') as crn");
        
        $select = $this->select();
        $select->from(DbTable::TABLE_RAT_CORP_CARDHOLDER . " as rhc",array(
            'rhc.id', 'rhc.bank_id', 'rhc.product_id', 'rhc.rat_customer_id', 'rhc.customer_master_id', 'rhc.customer_type', $crn, 'rhc.unicode', $card_number, 'rhc.card_pack_id', 'rhc.afn', 'rhc.medi_assist_id', 'rhc.employee_id', 'rhc.partner_ref_no', 'rhc.txnrefnum', 'rhc.title', 'rhc.first_name', 'rhc.middle_name', 'rhc.last_name', 'rhc.name_on_card', 'rhc.gender', 'rhc.date_of_birth', 'rhc.aadhaar_no', 'rhc.pan', 'rhc.mobile', 'rhc.mobile2', 'rhc.email', 'rhc.landline', 'rhc.address_line1', 'rhc.address_line2', 'rhc.city', 'rhc.state', 'rhc.pincode', 'rhc.country', 'rhc.mother_maiden_name', 'rhc.employer_name', 'rhc.corporate_id', 'rhc.corp_address_line1', 'rhc.corp_address_line2', 'rhc.corp_city', 'rhc.corp_state', 'rhc.corp_pin', 'rhc.corp_country', 'rhc.id_proof_type', 'rhc.id_proof_number', 'rhc.id_proof_doc_id', 'rhc.address_proof_type', 'rhc.address_proof_number', 'rhc.address_proof_doc_id', 'rhc.occupation', 'rhc.is_card_activated', 'rhc.activation_date', 'rhc.is_card_dispatched', 'rhc.card_dispatch_date', 'rhc.by_ops_id', 'rhc.by_corporate_id', 'rhc.by_agent_id', 'rhc.batch_id', 'rhc.batch_name', 'rhc.date_created', 'rhc.date_updated', 'rhc.date_approval', 'rhc.date_toggle_kyc', 'rhc.date_activation', 'rhc.date_blocked', 'rhc.narration', 'rhc.txn_code', 'rhc.failed_reason', 'rhc.date_failed', 'rhc.date_crn_update', 'rhc.status_ecs', 'rhc.status_ops', 'rhc.status', 'rhc.aml_status' 
            ,'concat(rhc.first_name," ",rhc.last_name) as cardholder_name','rhc.channel'));
        $select->setIntegrityCheck(false);
        $select->joinLeft(DbTable::TABLE_PRODUCTS . " as p", "rhc.product_id  = p.id",array('p.name as product_name'));
        $select->joinLeft(DbTable::TABLE_RAT_CORP_CARDHOLDER_DETAILS . " as rhcd", "rhcd.rat_cardholder_id  = rhc.id",array());
        $select->joinLeft(DbTable::TABLE_DOCS . " as docA", "rhc.address_proof_doc_id  = docA.id",array('date_created as date_uploaded_a'));
        $select->joinLeft(DbTable::TABLE_DOCS . " as docI", "rhc.id_proof_doc_id = docI.id",array('date_created as date_uploaded_i'));    
        $select->joinLeft(DbTable::TABLE_PURSE_MASTER . " as pm", "rhc.product_id = pm.product_id AND pm.status='".STATUS_ACTIVE."'",array("GROUP_CONCAT(pm.code SEPARATOR ', ') as code"));  
        if ($product != ''){
            $select->where("rhc.product_id = '" . $product . "'");
        }
         if ($corporateId != ''){
            $select->where("rhc.by_corporate_id = '" . $corporateId . "'");
        }
        if ($agent_id != '') {
            $select->where('rhc.by_agent_id = ?', $agent_id);
        }
        if ($from != '' && $to != ''){
            if($dateCreated){
                $select->where("rhc.date_created >=  '" . $from . "'");
                $select->where("rhc.date_created <= '" . $to . "'");
            }else{
            
            $select->where("rhc.date_activation >=  '" . $from . "'");
            $select->where("rhc.date_activation <= '" . $to . "'");
            }
           
        }
        if ($apprvfrom != '' && $apprvto != ''){
            $select->where("rhc.date_approval >=  '" . $apprvfrom . "'");
            $select->where("rhc.date_approval <= '" . $apprvto . "'");
            $select->where("rhc.status_ops =  '" . STATUS_APPROVED . "'");
        }
        if($department!=''){
            $select->where("rhc.employer_name LIKE  '%" . $department . "%'");
        }
        if($location!=''){
            $select->where("rhcd.emp_city LIKE  '%" . $location . "%'");
        }
        if(!empty($status)){
            $select->where("rhc.status =  '" . $status . "'");
        }else{
            $select->where("rhc.status =  '" . STATUS_ACTIVE . "'");
        }
        $select->order("first_name");
        $select->group("rhc.id");
        //echo $select; //exit;
        return $this->fetchAll($select);
    }
    
     /* exportAgentFundRequests function will find data for Agent fund requests report. 
    * it will accept param array with query filters e.g.. duration
    */
    public function exportgetCardholders($param ,$dateCreated){ 
        
        $data = $this->getCardholders($param,$dateCreated);
                
        $retData = array();
        
        if(!empty($data))
        {
            foreach($data as $key=>$data){
    
                $retData[$key]['product_name'] = $data['product_name'];
                $retData[$key]['medi_assist_id'] = $data['medi_assist_id'];
                $retData[$key]['partner_ref_no'] = $data['partner_ref_no'];
                $retData[$key]['employee_id'] = $data['employee_id'];
                $retData[$key]['customer_type'] = strtoupper($data['customer_type']);
                $retData[$key]['card_number'] = UTIL :: maskCard($data['card_number']);
                $retData[$key]['card_pack_id'] = $data['card_pack_id'];
                $retData[$key]['title'] = $data['title'];
                $retData[$key]['first_name'] = $data['first_name'];
                $retData[$key]['middle_name'] = $data['middle_name'];
                $retData[$key]['last_name'] = $data['last_name'];
                $retData[$key]['name_on_card'] = $data['name_on_card'];
                $retData[$key]['gender'] = ucfirst($data['gender']);
                $retData[$key]['date_of_birth'] = $data['date_of_birth'];
                $retData[$key]['mobile'] = $data['mobile'];
                $retData[$key]['mobile2'] = $data['mobile2'];
                $retData[$key]['email'] = $data['email'];
                $retData[$key]['landline'] = $data['landline'];
                $retData[$key]['address_line1'] = $data['address_line1'];
                $retData[$key]['address_line2'] = $data['address_line2'];
                $retData[$key]['city'] = ucfirst($data['city']);
                $retData[$key]['state'] = ucfirst($data['state']);
                $retData[$key]['country'] = ucfirst($data['country']);
                $retData[$key]['pincode'] = $data['pincode'];
                $retData[$key]['mother_maiden_name'] = $data['mother_maiden_name'];
                $retData[$key]['employer_name'] = $data['employer_name'];
                $retData[$key]['corporate_id'] = $data['corporate_id'];
                $retData[$key]['corp_address_line1'] = $data['corp_address_line1'];
                $retData[$key]['corp_address_line2'] = $data['corp_address_line2'];
                $retData[$key]['corp_city'] = ucfirst($data['corp_city']);
                $retData[$key]['corp_pin'] = $data['corp_pin'];
                $retData[$key]['date_uploaded_a'] = $data['date_uploaded_a'];
                $retData[$key]['date_uploaded_i'] = $data['date_uploaded_i'];
                $retData[$key]['is_card_activated'] = $data['is_card_activated'];
                $retData[$key]['activation_date'] = $data['activation_date'];
                $retData[$key]['is_card_dispatched'] = $data['is_card_dispatched'];
                $retData[$key]['card_dispatch_date'] = $data['card_dispatch_date'];
                $retData[$key]['wallet_code'] = $data['code'];
                $retData[$key]['date_created'] = $data['date_created'];
                $retData[$key]['status'] = ucfirst($data['status']);
                $retData[$key]['date_failed'] = $data['date_failed'];
                $retData[$key]['failed_reason'] = $data['failed_reason'];
		$retData[$key]['channel'] = strtoupper($data['channel']);
          }
        }
        
        return $retData;
   }
   

    public function getPendingKyc($page = 1, $params,$paginate = NULL) {
        
        //Decryption of Card Number
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`rhc`.`card_number`,'".$decryptionKey."') as card_number");
        
        $select = $this->select();
        $select->from(DbTable::TABLE_RAT_CORP_CARDHOLDER . " as rhc", 
                array('rhc.id as id','rhc.customer_master_id', $card_number,'rhc.rat_customer_id',
                    'rhc.medi_assist_id', 'rhc.employee_id', 
                    'concat(rhc.first_name," ",rhc.last_name) as cardholder_name', 'rhc.gender', 
                    'rhc.mobile', 'rhc.email', 'rhc.employer_name', 'rhc.status', 
                    'rhc.afn', 'rhc.mobile', 'rhc.first_name', 'rhc.middle_name', 'rhc.last_name', 
                    'rhc.date_of_birth', 'rhc.name_on_card', 'rhc.batch_name', 'rhc.corporate_id',
                    'rhc.product_id','rhc.date_activation'));
        if(isset($params['product_id']) && !empty($params['product_id'])) {
        $productId = $params['product_id'];
            $select->where("product_id = '$productId' ");
        }
        $select->where("status = '".STATUS_ACTIVE."' OR status = '".STATUS_ECS_PENDING."' ");
        $select->where("id_proof_doc_id = 0 OR address_proof_doc_id = 0");
        $select->where('date_created >= ?',$params['from_date'].' 00:00:00'); 
        $select->where('date_created <= ?',$params['to_date'].' 23:59:59'); 
        $select->order("cardholder_name");
        return $this->fetchAll($select);
    }
    
    public function getCardholderInfoByCardNumber(array $param) {
        if(!isset($param['card_number']) || !isset($param['product_id'])) {
            throw new App_Exception('Invalid details provided to fetch cardholder details');
            return FALSE;
        }
        $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $param['card_number'] = new Zend_Db_Expr("AES_ENCRYPT('".$param['card_number']."','".$encryptionKey."')"); 
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number");
        $crn = new Zend_Db_Expr("AES_DECRYPT(`crn`,'".$decryptionKey."') as crn");
        $select = $this->select();
        $select->from(DbTable::TABLE_RAT_CORP_CARDHOLDER, array(
    'id', 'bank_id', 'product_id', 'rat_customer_id', 'customer_master_id', 'customer_type', $crn, 'unicode', $card_number, 'card_pack_id', 'afn', 'medi_assist_id', 'employee_id', 'partner_ref_no', 'txnrefnum', 'title', 'first_name', 'middle_name', 'last_name', 'name_on_card', 'gender', 'date_of_birth', 'aadhaar_no', 'pan', 'mobile', 'mobile2', 'email', 'landline', 'address_line1', 'address_line2', 'city', 'state', 'pincode', 'country', 'mother_maiden_name', 'employer_name', 'corporate_id', 'corp_address_line1', 'corp_address_line2', 'corp_city', 'corp_state', 'corp_pin', 'corp_country', 'id_proof_type', 'id_proof_number', 'id_proof_doc_id', 'address_proof_type', 'address_proof_number', 'address_proof_doc_id', 'occupation', 'is_card_activated', 'activation_date', 'is_card_dispatched', 'card_dispatch_date', 'by_ops_id', 'by_corporate_id', 'by_agent_id', 'batch_id', 'batch_name', 'date_created', 'date_updated', 'date_approval', 'date_toggle_kyc', 'date_activation', 'date_blocked', 'narration', 'txn_code', 'failed_reason', 'date_failed', 'date_crn_update', 'status_ecs', 'status_ops', 'status', 'aml_status'
        ));
        
        $select->where("status = '".STATUS_ACTIVE."'");
        $select->where("card_number = ?",$param['card_number']);
        $select->where("product_id = ?",$param['product_id']); 
        return $this->fetchRow($select); 
    }
    
    
    public function getCardholderInfoByMAID($product_id, $maId ) { 
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number");
        $crn = new Zend_Db_Expr("AES_DECRYPT(`crn`,'".$decryptionKey."') as crn");
        
        $details = $this->_db->select()
                ->from(DbTable::TABLE_RAT_CORP_CARDHOLDER, array('id', $crn, $card_number, 'afn', 'medi_assist_id','rat_customer_id','mother_maiden_name','address_line1','address_line2','city','pincode','mobile','email','date_of_birth'))
                ->where("status = ?",STATUS_ACTIVE)
                ->where("medi_assist_id = ?",$maId)
                ->where("product_id = '$product_id'");
        return $this->_db->fetchRow($details);
    }    
    
    
    public function blockCardholderInfoByMAID($product_id, $maId ) {
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number");
        $crn = new Zend_Db_Expr("AES_DECRYPT(`crn`,'".$decryptionKey."') as crn");
        $details = $this->_db->select()
                ->from(DbTable::TABLE_RAT_CORP_CARDHOLDER, array('id', $crn, $card_number, 'afn', 'medi_assist_id','rat_customer_id','mother_maiden_name','address_line1','address_line2','city','pincode','mobile','email','date_of_birth','status','date_blocked'))
                ->where("status = ?",STATUS_ACTIVE)
                ->where("medi_assist_id = ?",$maId)
                ->where("product_id = '$product_id'");
        $rs =  $this->_db->fetchRow($details);
        if(!empty($rs)) {
            $updateArr = array(
              'status' => STATUS_BLOCKED,
              'date_blocked' => new Zend_Db_Expr('NOW()')
            );
            $this->_db->update(DbTable::TABLE_RAT_CORP_CARDHOLDER, $updateArr, 'medi_assist_id="'.$maId.'" AND product_id="'.$product_id.'" AND status="'.STATUS_ACTIVE.'"');
            return TRUE;
        } else {
            return FALSE;
        } 
    }    
    
    public function blockCardholderInfoByCardNumber(array $param) {
        if(!isset($param['card_number']) || !isset($param['product_id'])) {
            throw new App_Exception('Invalid details provided to fetch cardholder details');
            return FALSE;
        } 
        $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $param['card_number'] = new Zend_Db_Expr("AES_ENCRYPT('".$param['card_number']."','".$encryptionKey."')");        
        $select = $this->select()
            ->where("status = '".STATUS_ACTIVE."'")
            ->where("card_number = ?",$param['card_number'])
            ->where("product_id = ?",$param['product_id']);
        $rs = $this->fetchRow($select); 
            
        if(!empty($rs)) {
            $updateArr = array (
                'status'        =>  STATUS_BLOCKED,
                'date_blocked'  =>  new Zend_Db_Expr('NOW()')
            );
            $whereArr = array(
                'card_number'   =>  $param['card_number'],
                'product_id'    =>  $param['product_id'],
                'status'        =>  STATUS_ACTIVE
            ); 
            $this->update($updateArr, $whereArr);
            return TRUE;
        } else {
            return FALSE;
        } 
    }    
    
    public function checkDuplicate($dataArr) { 
        $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $dataArr['card_number'] = new Zend_Db_Expr("AES_ENCRYPT('".$dataArr['card_number']."','".$encryptionKey."')");
            
        $sql = $this->select()
                ->where("card_number=?",$dataArr['card_number'])
                ->where('card_pack_id=?',$dataArr['card_pack_id'])
                ->where('status="'.STATUS_FREE.'" OR status="'.STATUS_BLOCKED.'"');
        
        $rs = $this->fetchRow($sql);
        if(!empty($rs)) { 
            return TRUE;
        }
        return FALSE;
    }
    
    
    public function getBatchDDByDate($batchArr){
        
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_RAT_CORP_CARDHOLDER_BATCH, array('batch_name'));
        if(isset($batchArr['product_id']) && !empty($batchArr['product_id'])) {
            $select->where('product_id = ?', $batchArr['product_id']);
        }
        if(isset($batchArr['start_date']) && !empty($batchArr['start_date'])) {
            $select->where('date_created >= ?', Util::returnDateFormatted($batchArr['start_date'], "d-m-Y", "Y-m-d", "-", "-", 'from'));
        }
        if(isset($batchArr['end_date']) && !empty($batchArr['end_date'])) {
            $select->where('date_created <= ?', Util::returnDateFormatted($batchArr['end_date'], "d-m-Y", "Y-m-d", "-", "-", 'to'));
        }
        if(isset($batchArr['by_corporate_id']) && !empty($batchArr['by_corporate_id'])) {
            $select->where('by_corporate_id = ?', $batchArr['by_corporate_id']);
        }
        $select->order('batch_name ASC');
        $select->group('batch_name');
        //echo $select; exit; 
        $batch = $this->_db->fetchAll($select);
        
//        $dataArray = array('' => 'Select Batch');
        foreach ($batch as $val) {
            $dataArray[$val['batch_name']] = $val['batch_name'];
        }
        return $dataArray;

    }
       public function getCustomerInfo($custId){ 
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number");
        $crn = new Zend_Db_Expr("AES_DECRYPT(`crn`,'".$decryptionKey."') as crn");
        $select = $this->select();
        $select->from(DbTable::TABLE_RAT_CORP_CARDHOLDER, array(
    'id', 'bank_id', 'product_id', 'rat_customer_id', 'customer_master_id', 'customer_type', $crn, 'unicode', $card_number, 'card_pack_id', 'afn', 'medi_assist_id', 'employee_id', 'partner_ref_no', 'txnrefnum', 'title', 'first_name', 'middle_name', 'last_name', 'name_on_card', 'gender', 'date_of_birth', 'aadhaar_no', 'pan', 'mobile', 'mobile2', 'email', 'landline', 'address_line1', 'address_line2', 'city', 'state', 'pincode', 'country', 'mother_maiden_name', 'employer_name', 'corporate_id', 'corp_address_line1', 'corp_address_line2', 'corp_city', 'corp_state', 'corp_pin', 'corp_country', 'id_proof_type', 'id_proof_number', 'id_proof_doc_id', 'address_proof_type', 'address_proof_number', 'address_proof_doc_id', 'occupation', 'is_card_activated', 'activation_date', 'is_card_dispatched', 'card_dispatch_date', 'by_ops_id', 'by_corporate_id', 'by_agent_id', 'batch_id', 'batch_name', 'date_created', 'date_updated', 'date_approval', 'date_toggle_kyc', 'date_activation', 'date_blocked', 'narration', 'txn_code', 'failed_reason', 'date_failed', 'date_crn_update', 'status_ecs', 'status_ops', 'status', 'aml_status'
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
        $select->from($this->_name .' as kc',array(
            'id', 'bank_id', 'product_id', 'rat_customer_id', 'customer_master_id', 'customer_type', $crn, 'unicode', $card_number, 'card_pack_id', 'afn', 'medi_assist_id', 'employee_id', 'partner_ref_no', 'txnrefnum', 'title', 'first_name', 'middle_name', 'last_name', 'name_on_card', 'gender', 'date_of_birth', 'aadhaar_no', 'pan', 'mobile', 'mobile2', 'email', 'landline', 'address_line1', 'address_line2', 'city', 'state', 'pincode', 'country', 'mother_maiden_name', 'employer_name', 'corporate_id', 'corp_address_line1', 'corp_address_line2', 'corp_city', 'corp_state', 'corp_pin', 'corp_country', 'id_proof_type', 'id_proof_number', 'id_proof_doc_id', 'address_proof_type', 'address_proof_number', 'address_proof_doc_id', 'occupation', 'is_card_activated', 'activation_date', 'is_card_dispatched', 'card_dispatch_date', 'by_ops_id', 'by_corporate_id', 'by_agent_id', 'batch_id', 'batch_name', 'date_created', 'date_updated', 'date_approval', 'date_toggle_kyc', 'date_activation', 'date_blocked', 'narration', 'txn_code', 'failed_reason', 'date_failed', 'date_crn_update', 'status_ecs', 'status_ops', 'status', 'aml_status'
        ))
                ->where("kc.status = '". STATUS_ACTIVE."'")
                ->order('kc.id');
        return $this->_db->fetchAll($select);
    }
    
    
    
    /*
     * $params['ProductId']
     * $params['CustomerType'] - 'kyc','non_kyc'
     * $params['CardNumber'] If CardPackId is null then this is mandatory
     * $params['CardPackId'] If CardNumber is null then this is mandatory
     * $params['MemberId'] O
     * $params['Title'] M
     * $params['FirstName'] M
     * $params['MiddleName'] O
     * $params['LastName'] M
     * $params['NameOnCard'] M
     * $params['Gender'] M - male, female
     * $params['DateOfBirth'] M - yyyy-mm-dd
     * $params['Mobile'] M
     * $params['Mobile2'] 0
     * $params['Email'] O
     * $params['MotherMaidenName'] M
     * $params['IdentityProofType'] O
     * $params['IdentityProofDetail'] O
     * $params['AddressProofType'] O
     * $params['AddressProofDetail'] O
     * $params['Landline'] O
     * $params['AddressLine1'] M
     * $params['AddressLine2'] O
     * $params['City'] M
     * $params['State'] M
     * $params['Country'] Default Value India
     * $params['Pincode'] M
     * $params['CommAddressLine1'] O
     * $params['CommAddressLine2'] O
     * $params['CommCity'] O
     * $params['CommState'] O
     * $params['CommPin'] O
     * $params['EmployerName'] O
     * $params['Occupation'] O
     *      * $params['IsCardActivated'] O, Y or N
     * $params['ActivationDate'] O, If activated, yyyy-mm-dd HH:MM:SS
     * $params['IsCardDispatch'] O, Y or N
     * $params['CardDispatchDate'] O, If dispatched, yyyy-mm-dd HH:MM:SS 
     * 
     * $params['OTP']
     * EmployerAddressLine1
        EmployerAddressLine2
        EmployerCity
        EmployerState
        EmployerCountry
        EmployerPin
     * by_api_user_id
     */
    
    public function addCustomer($dataArr = array()) {
        //echo '<pre>';print_r($dataArr);exit;
        if (empty($dataArr)) {
            throw new Exception('Data missing for add cardholder');
        }
        elseif (empty($dataArr['ProductId'])) {
            throw new Exception('Product missing for add cardholder');
        }
        $custProductModel = new Corp_Ratnakar_CustomerProduct();
        $custPurseModel = new Corp_Ratnakar_CustomerPurse();
        $masterPurseModel = new MasterPurse();
        $objCustomerMaster = new CustomerMaster();
        $customerTrackModel = new CustomerTrack();
        $bankModel = new Banks();
        $productModel = new Products();
        $crnMaster = new CRNMaster();
        $m = new \App\Messaging\Corp\Ratnakar\Operation();
        $str = '';
        try {
//            $this->_db->beginTransaction();
            
            $paramChk = array(
                'mobile' => $dataArr['Mobile'],
                'medi_assist_id' => $dataArr['MemberId'],
                'card_number' => $dataArr['CardNumber'],
                'product_id' => $dataArr['ProductId']
            );
            $check = $this->checkCardholderDuplication($paramChk);
            if (!$check) {
                $this->setError("Duplicate Record");
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
                    'aadhaar_no' => (isset($dataArr['IdentityProofType']) && strtolower($dataArr['IdentityProofType']) == 'aadhar card') ? $dataArr['IdentityProofDetail'] : '',
                    'pan' => (isset($dataArr['IdentityProofType']) && strtolower($dataArr['IdentityProofType']) == 'pan card') ? $dataArr['IdentityProofDetail'] : '',
                    'mobile_country_code' => isset($dataArr['mobile_country_code']) ? $dataArr['mobile_country_code'] : '',
                    'mobile' => $dataArr['Mobile'],
                    'email' => $dataArr['Email'],
                    'gender' => $dataArr['Gender'],
                    'date_of_birth' => isset($dataArr['DateOfBirth']) ? $dataArr['DateOfBirth'] : '',
                    'status' => STATUS_ACTIVE,
                    'bank_id' => $productDetail['bank_id']
                );
                
                $ratCustomerId = $this->addRatCustomerMaster($ratCustomerMasterData);
                //echo 'ratCustomerId=>'.$ratCustomerId; exit;
                //insert into customer purse
                $purseDetails = $masterPurseModel->getPurseDetailsbyBankIdProductId($dataArr['ProductId'], $productDetail['bank_id']);
                foreach ($purseDetails as $purseDetail) {
                    $purseArr = array(
                        'customer_master_id' => $customerMasterId,
                        'rat_customer_id' => $ratCustomerId,
                        'product_id' => $dataArr['ProductId'],
                        'purse_master_id' => $purseDetail['id'],
                        'bank_id' => $productDetail['bank_id'],
                        //'by_ops_id' => $user->id,
                        'date_updated' => new Zend_Db_Expr('NOW()')
                    );
                    $purseParam = array('rat_customer_id' => $ratCustomerId, 'purse_master_id' => $purseDetail['id']);
                    $purseDetails = $custPurseModel->getCustPurseDetails($purseParam);
                    if (empty($purseDetails)) { // If purse entry not found
                        $custPurseModel->save($purseArr);
                    }
                } 
	    
                $dataCardholder = array(
                    'product_id' => $dataArr['ProductId'],
                    'rat_customer_id' => $ratCustomerId,
                    'customer_master_id' => $customerMasterId,
                    'customer_type' => $dataArr['customer_type'],
                    'afn' => $dataArr['afn'],
                    'crn' => Util::insertCardCrn($dataArr['CardNumber']),
                    'card_number' => Util::insertCardCrn($dataArr['CardNumber']),
                    'card_pack_id' => $dataArr['CardPackId'],
                    'medi_assist_id' => $dataArr['MemberId'],
                    'employee_id' => $dataArr['employee_id'],
                    'title' => $dataArr['Title'],
                    'first_name' => $dataArr['FirstName'],
                    'middle_name' => $dataArr['MiddleName'],
                    'last_name' => $dataArr['LastName'],
                    'name_on_card' => $dataArr['NameOnCard'],
                    'gender' => $dataArr['Gender'],
                    'date_of_birth' =>$dataArr['DateOfBirth'],
                    'mobile' => $dataArr['Mobile'],
                    'mobile2' => $dataArr['Mobile2'],
                    'email' => $dataArr['Email'],
                    'landline' => $dataArr['Landline'],
                    'address_line1' => $dataArr['AddressLine1'],
                    'address_line2' => $dataArr['AddressLine2'],
                    'city' => $dataArr['City'],
                    'state' => $dataArr['State'],
                    'pincode' => $dataArr['Pincode'],
                    'country' => $dataArr['Country'],
                    'mother_maiden_name' => $dataArr['MotherMaidenName'],
                    'employer_name' => $dataArr['EmployerName'],
                    'corporate_id' => $dataArr['corporate_id'],
                    'corp_address_line1' => $dataArr['corp_address_line1'],
                    'corp_address_line2' => $dataArr['corp_address_line2'],
                    'corp_city' => $dataArr['corp_city'],
                    'corp_state' => $dataArr['corp_state'],
                    'corp_pin' => $dataArr['corp_pin'],
                    'corp_country' => $dataArr['corp_country'],
                    'id_proof_type' => $dataArr['id_proof_type'],
                    'id_proof_number' => $dataArr['id_proof_number'],
                    'address_proof_type' => $dataArr['address_proof_type'],
                    'address_proof_number' => $dataArr['address_proof_number'],
                    'occupation' => $dataArr['Occupation'],
                    'is_card_activated' => $dataArr['IsCardActivated'],
                    'activation_date' => $dataArr['ActivationDate'],
                    'is_card_dispatched' => $dataArr['IsCardDispatch'],
                    'card_dispatch_date' => $dataArr['CardDispatchDate'],
                    'by_agent_id' => $dataArr['by_api_user_id'],
                    'by_ops_id' => 0,
                    'by_corporate_id' => $dataArr['by_corporate_id'],
                    'batch_id' => 0,
                    'batch_name' => '',
                    'date_created' => new Zend_Db_Expr('NOW()'),
                    'status_ops' => $dataArr['status_ops'],
                    'status' => STATUS_ECS_PENDING,
                    'status_ecs' => STATUS_WAITING,
                    'bank_id' => $productDetail['bank_id'],
		    'channel' => $dataArr['channel'],
                );
                 if(!empty($user->corporate_code)){
                    $dataCardholder['by_ops_id']='';
                    $dataCardholder['by_corporate_id']=$user->id;
                 }
             
                $this->insert($dataCardholder);
                $cardholderId = $this->_db->lastInsertId(DbTable::TABLE_RAT_CORP_CARDHOLDER, 'id');

                // Entry in Customer product table
                $prodArr = array('product_customer_id' => $cardholderId,
                    'product_id' => $dataArr['ProductId'],
                    'rat_customer_id' => $ratCustomerId,
                    'program_type' => $productDetail['program_type'],
                    'bank_id' => $productDetail['bank_id'],
                    'by_agent_id' => $dataArr['by_api_user_id'],
                    'by_ops_id' => 0,
                    'by_corporate_id' => $dataArr['corporate_id'],
                    'date_created' => new Zend_Db_Expr('NOW()'),
                    'status' => STATUS_ACTIVE);
                $custProductModel->save($prodArr);

               $custDetailArr = array(
                    'rat_cardholder_id' => $cardholderId,
                    'product_id' => $dataArr['ProductId'],
                    'employer_name' => $dataArr['EmployerName'],
                    'emp_address_line1' => $dataArr['EmployerAddressLine1'],
                    'emp_address_line2' => $dataArr['EmployerAddressLine2'],
                    'emp_city' => $dataArr['EmployerCity'],
                    'emp_state' => $dataArr['EmployerState'],
                    'emp_pin' => $dataArr['EmployerPin'],
                    'date_created' => new Zend_Db_Expr('NOW()'),
                    'status' => STATUS_ACTIVE,
                    'bank_id' => $productDetail['bank_id']
               );
               
                $this->insertCardhoderDetail($custDetailArr);
                $dataCardholder['id'] = $cardholderId;
                $cardholderArray = $this->getCardholderArray($dataCardholder);
                //$sentToECS = TRUE;                    
                
                try {
                    $ecsApi = new App_Api_ECS_Corp_Ratnakar();
                    $resp = $ecsApi->mediAssistCardholderRegistration($cardholderArray);
                } catch (Exception $e) {
          
                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    $msg = $ecsApi->getError();
                    $msg = empty($msg) ? $e->getMessage() : $msg ;
                    //echo $msg.'::';exit;
                    $updateArr = array('status' => STATUS_ECS_FAILED, 'failed_reason' => $msg,
                        'date_failed' => new Zend_Db_Expr('NOW()'));
                    $this->setError($msg);
                    $this->update($updateArr, "id= $cardholderId");
                    return FALSE;
                }
                if ($resp == false) {
                    $msg = $ecsApi->getError();
                    //echo $msg.'::';exit;
                    $updateArr = array('status' => STATUS_ECS_FAILED, 'failed_reason' => $msg,
                        'date_failed' => new Zend_Db_Expr('NOW()'));
                    $this->setError($msg);
                    $this->update($updateArr, "id= $cardholderId");
                    return FALSE;
                } else {
                    $updateArr = array('status' => STATUS_ACTIVE, 'date_activation' => new Zend_Db_Expr('NOW()'), 'failed_reason' =>'');
                    $this->update($updateArr, "id= $cardholderId");
                    // get CRN info
                    $crnInfo = $crnMaster->getCRNInfo($dataArr['CardNumber'], $dataArr['CardPackId'], $dataArr['MemberId']);
                    if(!empty($crnInfo)){
                    // update status CRN
                    $crnMaster->updateStatusById(array('status' => STATUS_USED), $crnInfo->id);
                    }
	    
                    $customerTrackArr =  array(
                         'card_number' =>  Util::insertCardCrn($dataArr['CardNumber']),
                         'card_pack_id' => $dataArr['CardPackId'],
                         'member_id' => $dataArr['MemberId'],
                         'crn' => Util::insertCardCrn($dataArr['CardNumber']),
                         'mobile' => $dataArr['Mobile'],
                         'email' => $dataArr['Email'],
                         'name_on_card' => $dataArr['NameOnCard'],
                     );
                    $customerTrackModel->customerDetails($customerTrackArr, $dataArr['ProductId'], $cardholderId);
                //  Send SMS
                    $userData = array('last_four' =>substr($dataArr['CardNumber'], -4),
                        'product_name' => $productDetail['name'],
                        'mobile' => $dataArr['Mobile'],
                    );
                $resp = $m->apiCardActivation($userData);
                    
                    
                    
                    }
                
                
            } 
                    
            $this->_db->commit();
            $this->setTxncode($cardholderId);
            return $cardholderId;
            //echo '<pre>vijay';print_r($cardholderId);exit;
        } catch (Exception $e) {
           App_Logger::log($e->getMessage(), Zend_Log::ERR);            
            $msg = $e->getMessage();
            //print_r($msg); exit;
            if(isset($cardholderId) && $cardholderId > 0) {
                $updateArr = array('status' => STATUS_ECS_FAILED, 'failed_reason' => $msg,
                    'date_failed' => new Zend_Db_Expr('NOW()'));
                $this->setError($msg);
                $this->update($updateArr, "id= $cardholderId");
            }
            return FALSE;
          //  echo '<pre>';print_r($e);exit;
            // If any of the queries failed and threw an exception,
            // we want to roll back the whole transaction, reversing
            // changes made in the transaction, even those that succeeded.
            // Thus all changes are committed together, or none are.
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
//            $this->_db->rollBack();
            //throw new Exception ("Transaction not completed due to system failure");
            throw new Exception($e->getMessage());
        }
       
        return TRUE;
    }
    
    
   public function insertCardhoderDetail($params){
        try{
           $this->_db->insert(DbTable::TABLE_RAT_CORP_CARDHOLDER_DETAILS,$params);
           return TRUE;
        }catch (Exception $e) {
              App_Logger::log($e->getMessage(), Zend_Log::ERR);
              return FALSE;
        }
}

    public function insertCardhoderAPI($params){  
        try{
            if((isset($params['crn'])) && ($params['crn'] != '')){
                $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
                $params['crn'] = new Zend_Db_Expr("AES_ENCRYPT('".$params['crn']."','".$encryptionKey."')");
            }
            if((isset($params['card_number'])) && ($params['card_number'] != '')){
                $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
                $params['card_number'] = new Zend_Db_Expr("AES_ENCRYPT('".$params['card_number']."','".$encryptionKey."')");
            }
           $this->_db->insert(DbTable::TABLE_RAT_CORP_CARDHOLDER,$params);
           return TRUE;
        }catch (Exception $e) {
              App_Logger::log($e->getMessage(), Zend_Log::ERR);
              return FALSE;
        }
}

  public function checkDuplicateMobile($params){
        $select = $this->select();
        $select->from($this->_name, array('id', 'txn_code'));
        $select->where('product_id = ?', $params['product_id']);
        $select->where('mobile = ?', $params['mobile']);  
       // $select->where('status = ?', STATUS_ACTIVE); 
        $select->where("status IN ('".STATUS_ACTIVE."', '".STATUS_BLOCKED."')");
        $rs = $this->fetchRow($select);
        if( !empty($rs) ){
            //return TRUE;
            return array('id'=>$rs['id'], 'txncode'=>$rs['txn_code']);
        }
        else
            return FALSE;
   }
   
   public function checkDuplicateTransNum($params){
        $select = $this->select();
        $select->from($this->_name, array('id', 'txn_code'));
        $select->where('product_id = ?', $params['product_id']);
        $select->where('txnrefnum = ?', $params['txnrefnum']);  
        $select->where("status IN ('".STATUS_ACTIVE."', '".STATUS_BLOCKED."')");
        $rs = $this->fetchRow($select);
        if( !empty($rs) ){
            //return TRUE;
            return array('id'=>$rs['id'], 'txncode'=>$rs['txn_code']);
        }
        else
            return FALSE;
   }
   
   public function checkEditDuplicateMobile($params,$mobile){
        $chkMOB = isset($params['mobile']) ? $params['mobile'] : '';
        $chkPAR = isset($params['partner_ref_no']) ? $params['partner_ref_no'] : '';
       
        $select = $this->select();
        $select->from($this->_name);
       // $select->where('product_id = ?', $params['product_id']);
        $select->where('bank_id = ?', $params['bank_id']);
        $select->where('mobile = ?', $mobile);
        if($chkPAR !=''){
        $select->where('partner_ref_no != ?',$params['partner_ref_no']);
        }else if($chkMOB !=''){
        $select->where('mobile != ?',$params['mobile']);
        }
       // $select->where('status = ?', STATUS_ACTIVE); 
        $select->where("status IN ('".STATUS_ACTIVE."', '".STATUS_BLOCKED."')");
        $rs = $this->fetchRow($select);
        if( !empty($rs) ){
            return TRUE;
        }
        else
            return FALSE;
   }
   
   public function checkDuplicatePartnerRefNo($params){
        $select = $this->select();
        $select->from($this->_name, array('id', 'txn_code'));
        $select->where('product_id = ?', $params['product_id']);
        $select->where('partner_ref_no = ?', $params['partner_ref_no']); 
       // $select->where('status = ?', STATUS_ACTIVE); 
        $select->where("status IN ('".STATUS_ACTIVE."', '".STATUS_BLOCKED."')");
       
        $rs = $this->fetchRow($select);
        if( !empty($rs) ){
            //return TRUE;
            return array('id'=>$rs['id'], 'txncode'=>$rs['txn_code']);
        }
        else
            return FALSE;
   }
   
   public function checkDuplicateEmail($params){
        $select = $this->select();
        $select->from($this->_name);
        $select->where('product_id = ?', $params['product_id']);
        $select->where('email = ?', $params['email']);  
        //$select->where('status = ?', STATUS_ACTIVE); 
        $select->where("status IN ('".STATUS_ACTIVE."', '".STATUS_BLOCKED."')");
       
        $rs = $this->fetchRow($select);
        if( !empty($rs) ){
            return TRUE;
        }
        else
            return FALSE;
   }
   
   public function checkEditDuplicateEmail($params,$email){
         $chkMOB = isset($params['mobile']) ? $params['mobile'] : '';
        $chkPAR = isset($params['partner_ref_no']) ? $params['partner_ref_no'] : '';
       
        $select = $this->select();
        $select->from($this->_name);
        $select->where('product_id = ?', $params['product_id']);
        $select->where('email = ?', $email);
        if($chkPAR !=''){
        $select->where('partner_ref_no != ?',$params['partner_ref_no']);
        }else if($chkMOB !=''){
        $select->where('mobile != ?',$params['mobile']);
        }
        //$select->where('status = ?', STATUS_ACTIVE);
        $select->where("status IN ('".STATUS_ACTIVE."', '".STATUS_BLOCKED."')");
       
        $rs = $this->fetchRow($select);
        if( !empty($rs) ){
            return TRUE;
        }
        else
            return FALSE;
   }
    public function ratCorpRegisterActivationPending($data) {
        $custPurseModel = new Corp_Ratnakar_CustomerPurse();
        $masterPurseModel = new MasterPurse();
        $productModel = new Products();
        $custProductModel = new Corp_Ratnakar_CustomerProduct();
        $customerTrackModel = new CustomerTrack();
        $m = new \App\Messaging\Corp\Ratnakar\Operation();
        //$user = Zend_Auth::getInstance()->getIdentity();
        
        $product = App_DI_Definition_BankProduct::getInstance(BANK_RATNAKAR_CORP);
        $productUnicode = $product->product->unicode;
        $mediProduct = $productModel->getProductInfoByUnicode($productUnicode);
        
        
            $id = $data['id'];
            $msg = '';
            try {
                $crnMaster = new CRNMaster();
                $crnInfo = $crnMaster->getInfoByCardNumberNPackId(array(
                   'card_number'    => $data['card_number'],
                   'card_pack_id'   => $data['card_pack_id'],
                   'product_id'     => $data['product_id'],
                   'status'         => STATUS_FREE
                ));
                $crnInfo = Util::toArray($crnInfo);

                $sentToECS = FALSE;
                if(!empty($crnInfo)) {
                    $resp = TRUE;
                    

                    if($resp) {
                        $cardholderArray = $this->getCardholderArray($data);
                        $sentToECS = TRUE;                    
                        $ecsApi = new App_Api_ECS_Corp_Ratnakar();
                        $resp = $ecsApi->mediAssistCardholderRegistration($cardholderArray);
                        if ($resp == false) {
                            $msg = $ecsApi->getError();
                        }
                    }
                } else {
                    $resp = false;
                    $msg = 'Initial Validation failed: Card Number or Card Pack Id not found in system';
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
                    'mobile_country_code' => isset($data['mobile_country_code']) ? $data['mobile_country_code'] : '', 'mobile' => $data['mobile'],
                    'email' => $data['email'],
                    'gender' => $data['gender'],
                    'date_of_birth' => isset($data['date_of_birth']) ? $data['date_of_birth'] : '',
                    'status' => STATUS_ACTIVE,
                );

                $ratCustomerId = $this->addRatCustomerMaster($ratCustomerMasterData);

                //insert into customer purse
                $purseDetails = $masterPurseModel->getPurseDetailsbyBankIdProductId($data['product_id'], $productDetail['bank_id']);
                foreach ($purseDetails as $purseDetail) {
                    $purseArr = array(
                        'customer_master_id' => $customerMasterId,
                        'rat_customer_id' => $ratCustomerId,
                        'product_id' => $data['product_id'],
                        'purse_master_id' => $purseDetail['id'],
                        'bank_id' => $productDetail['bank_id'],
                        //'by_ops_id' => $user->id,
                        'date_updated' => new Zend_Db_Expr('NOW()')
                    );
                    $purseParam = array('rat_customer_id' => $ratCustomerId, 'purse_master_id' => $purseDetail['id']);
                    $purseDetails = $custPurseModel->getCustPurseDetails($purseParam);
                    if (empty($purseDetails)) { // If purse entry not found
                        $custPurseModel->save($purseArr);
                    }
                }
                // Get Customer product details
                //Update customer product
                  $prodUpdateArr = array(
                        'rat_customer_id' => $ratCustomerId,
                    );
                 $custProductModel->update($prodUpdateArr,"product_customer_id = $id");
                
                // update the status to STATUS_ACTIVE in cardholders
                $updateArr = array(
                    'status' => STATUS_ACTIVE, 
                    'customer_master_id' => $customerMasterId, 
                    'rat_customer_id' => $ratCustomerId,
                    'date_activation' => new Zend_Db_Expr('NOW()'), 
                    'failed_reason' =>'',
                    'narration' => $data['narration'],
                    'txn_code' => $data['txn_code'],
                    );
                $this->_db->update(DbTable::TABLE_RAT_CORP_CARDHOLDER, $updateArr, "id= $id");
                
                $crnMaster->updateStatusByCardNumberNPackId(array(
                   'card_number'    =>  $data['card_number'],
                   'card_pack_id'   =>  $data['card_pack_id'],
                   'product_id'     =>  $data['product_id'],
                   'status'         =>  STATUS_USED
                ));
             // Insert into customer Track
                $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
                $card_number = new Zend_Db_Expr("AES_ENCRYPT('".$data['card_number']."','".$encryptionKey."')");
                $customerTrackArr =  array(
                    'card_number' => $card_number,
                    'card_pack_id' => $data['card_pack_id'],
                    'member_id' => $data['medi_assist_id'],
                    'crn' => $data['card_number'],
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
                // 
            } else {
                //On Failure
                if(!$sentToECS) { 
                    $updateArr = array('failed_reason' => $msg,
                        'date_failed' => new Zend_Db_Expr('NOW()'));
                    $this->update($updateArr, "id= $id");
                } else {
                    $updateArr = array('status' => STATUS_ECS_FAILED, 'failed_reason' => $msg,
                        'date_failed' => new Zend_Db_Expr('NOW()'));
                    $this->update($updateArr, "id= $id");
                }
                $updateprodArr = array('status' => STATUS_INACTIVE);
                $this->_db->update(DbTable::TABLE_RAT_CUSTOMER_PRODUCT, $updateprodArr, "rat_customer_id= $id");
            }
         $this->setError($msg);
         return $resp;
    }

    
    public function getCardholderInfoForActivationAPI($param) {
        $crnKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_num = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$crnKey."') ");
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number");
        $crn = new Zend_Db_Expr("AES_DECRYPT(`crn`,'".$decryptionKey."') as crn");
        
        $select = $this->_db->select()
            ->from(DbTable::TABLE_RAT_CORP_CARDHOLDER,array(
            'id', 'bank_id', 'product_id', 'rat_customer_id', 'customer_master_id', 'customer_type', $crn, 'unicode', $card_number, 'card_pack_id', 'afn', 'medi_assist_id', 'employee_id', 'partner_ref_no', 'txnrefnum', 'title', 'first_name', 'middle_name', 'last_name', 'name_on_card', 'gender', 'date_of_birth', 'aadhaar_no', 'pan', 'mobile', 'mobile2', 'email', 'landline', 'address_line1', 'address_line2', 'city', 'state', 'pincode', 'country', 'mother_maiden_name', 'employer_name', 'corporate_id', 'corp_address_line1', 'corp_address_line2', 'corp_city', 'corp_state', 'corp_pin', 'corp_country', 'id_proof_type', 'id_proof_number', 'id_proof_doc_id', 'address_proof_type', 'address_proof_number', 'address_proof_doc_id', 'occupation', 'is_card_activated', 'activation_date', 'is_card_dispatched', 'card_dispatch_date', 'by_ops_id', 'by_corporate_id', 'by_agent_id', 'batch_id', 'batch_name', 'date_created', 'date_updated', 'date_approval', 'date_toggle_kyc', 'date_activation', 'date_blocked', 'narration', 'txn_code', 'failed_reason', 'date_failed', 'date_crn_update', 'status_ecs', 'status_ops', 'status', 'aml_status'
                ))
            //->where("status =?", STATUS_ECS_PENDING) 
            ->where("date_of_birth = ?", $param['date_of_birth'])
            ->where("mobile = ?", $param['mobile'])
            ->where("SUBSTR(".$card_num." , -4) = ?", $param['last_4_digit']) 
            ->where("product_id = ?", $param['product_id']); 
        return $this->_db->fetchRow($select);   
    }
    
            
    public function getCardholderCount($param)
    {
        $fromDate = isset($param['from']) ? $param['from'] : '';
        $toDate = isset($param['to']) ? $param['to'] : '';
        $select = $this->_db->select()
                ->from(DbTable::TABLE_RAT_CORP_CARDHOLDER, array('COUNT(' . $this->_primary . ') as cnt'));
        if(isset($param['status']) && !empty($param['status'])) {    
            $select->where("status =?", $param['status']);
        }
        if(isset($param['product_id']) && !empty($param['product_id'])) {        
            $select->where("product_id =?", $param['product_id']);
        }
        if(isset($param['by_corporate_id']) && !empty($param['by_corporate_id'])) {            
            $select->where("by_corporate_id =?", $param['by_corporate_id']);
        }
        if($fromDate!="" && $toDate!=""){
            $select->where('date_created >= ?', $fromDate);
            $select->where('date_created <= ?', $toDate);
        }
        //echo $select;//exit;
        $data = $this->_db->fetchRow($select);
        if(isset($data['cnt'])){
            return $data['cnt'];
        }
        else {
            return 0;
        }
    }
    /* getCardholderSearch() will search cardholder of medi assist of ratnakar bank
     * param: medi assis id, employer name, card number, mobile, email,aadhaar no, pan
     * any of above params can be accepted
     */

    public function getCardholdersData($param, $page, $paginate = NULL) {
        $result = $this->getCardholders($param);
        $result = $result->toArray();
        return $result;
    }
    
    
    public function exportBatchDetailsByDate($batchArr) {
        
        $select = $this->_db->select()
                ->from(DbTable::TABLE_RAT_CORP_CARDHOLDER_BATCH . " as cb", array('cb.*','cb.failed_reason as batch_failed_reason'))
                ->joinLeft(DbTable::TABLE_RAT_CORP_CARDHOLDER . ' as c', "c.batch_id = cb.id AND cb.upload_status = '" . STATUS_PASS . "' ", array('c.status as cardholder_status','c.failed_reason'))
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
        if(isset($batchArr['status']) && !empty($batchArr['status'])) {
            $select->where('c.status = ?', $batchArr['status']);
        }
        if(isset($batchArr['by_corporate_id']) && !empty($batchArr['by_corporate_id'])) {
            $select->where('c.by_corporate_id = ?', $batchArr['by_corporate_id']);
        }
        $data = array();
        //echo $select; //exit;
        $rs = $this->_db->fetchAll($select);
        $i = 0;
        foreach($rs as $val)
        {
             
            $data[$i]['card_number'] = $val['card_number'];
            $data[$i]['card_pack_id'] = $val['card_pack_id'];
            $data[$i]['afn'] = $val['afn'];
            $data[$i]['medi_assist_id'] = $val['medi_assist_id'];
            $data[$i]['employee_id'] = $val['employee_id'];
            $data[$i]['first_name'] = $val['first_name'];
            $data[$i]['middle_name'] = $val['middle_name'];
            $data[$i]['last_name'] = $val['last_name'];
            $data[$i]['name_on_card'] = $val['name_on_card'];
            $data[$i]['gender'] = $val['gender'];
            $data[$i]['date_of_birth'] = $val['date_of_birth'];
            $data[$i]['mobile'] = $val['mobile'];
            $data[$i]['email'] = $val['email'];
            $data[$i]['landline'] = $val['landline'];
            $data[$i]['address_line1'] = $val['address_line1'];
            $data[$i]['address_line2'] = $val['address_line2'];
            $data[$i]['city'] = $val['city'];
            $data[$i]['pincode'] = $val['pincode'];
            $data[$i]['mother_maiden_name'] = $val['mother_maiden_name'];
            $data[$i]['employer_name'] = $val['employee_name'];
            $data[$i]['corporate_id'] = $val['corporate_id'];
            $data[$i]['corp_address_line1'] = $val['corp_address_line1'];
            $data[$i]['corp_address_line2'] = $val['corp_address_line2'];
            $data[$i]['corp_city'] = $val['corp_city'];
            $data[$i]['corp_pin'] = $val['corp_pin'];
            $data[$i]['id_proof_type'] = $val['id_proof_type'];
            $data[$i]['id_proof_number'] = $val['id_proof_number'];
            $data[$i]['address_proof_type'] = $val['address_proof_type'];
            $data[$i]['address_proof_number'] = $val['address_proof_number'];
            
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
    
    
    public function getpendingcardholders ($param, $page = 1, $paginate = NULL, $force = FALSE){
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number");
        $crn = new Zend_Db_Expr("AES_DECRYPT(`crn`,'".$decryptionKey."') as crn");
        
        $details = $this->_db->select();
        $details->from(DbTable::TABLE_RAT_CORP_CARDHOLDER, array('id', $crn,'customer_master_id', $card_number, 'afn', 'medi_assist_id', 'employee_id', 'concat(first_name," ",last_name) as name', 'gender', 'date_of_birth', 'mobile', 'email', 'employer_name', 'corporate_id', 'status', 'name_on_card', 'date_created' , 'date_approval' ,'date_failed', 'failed_reason'));
        $details->where("status_ops = '".STATUS_PENDING."'");
        
        if(isset($param['product_id']) && !empty($param['product_id'])) {
        $details->where('product_id = ?', $param['product_id']);
        }
               
        $details->order('date_created ASC');

        //return $this->_db->fetchAll($details);
        return $this->_paginate($details, $page, $paginate);
    
    }
    
    
    public function exportpendingcardholdersdetails($data) {
         $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number");
        $crn = new Zend_Db_Expr("AES_DECRYPT(`crn`,'".$decryptionKey."') as crn");
        
        $details = $this->_db->select();
        $details->from(DbTable::TABLE_RAT_CORP_CARDHOLDER, array('id', $crn,'customer_master_id', $card_number, 'afn', 'medi_assist_id', 'employee_id', 'concat(first_name," ",last_name) as name', 'gender', 'date_of_birth', 'mobile', 'email', 'employer_name', 'corporate_id', 'status', 'name_on_card', 'date_created' , 'date_approval' ,'date_failed', 'failed_reason'));
        $details->where("status_ops = '".STATUS_PENDING."'");
        
        if(isset($data['product_id']) && !empty($data['product_id'])) {
        $details->where('product_id = ?', $data['product_id']);
        }
               
        $details->order('date_created ASC');
        $data = $this->_db->fetchAll($details);
        
        $rctModel = new RctMaster();
        $retData = array();
        
        if (!empty($data)) {
            foreach ($data as $key => $data) {
                $retData[$key]['medi_assist_id'] = $data['medi_assist_id'];
                $retData[$key]['name'] = ucfirst($data['name']);
                $retData[$key]['card_number'] = $data['card_number'];
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
        $dataArr = array('status_ops' => $params['status'], 'date_approval' => new Zend_Db_Expr('NOW()'), 'date_activation' => new Zend_Db_Expr('NOW()'));
        
        if(isset($params['status_basic']) && !empty($params['status_basic'])) {
        $dataArr['status'] = $params['status_basic'];
        }
        
        $id = $params['id'];
        return $this->update($dataArr, "id = $id");
    }
    
    public function bulkApproval($params) {
        
        $user = Zend_Auth::getInstance()->getIdentity();
        $ip = $this->formatIpAddress(Util::getIP());
        
        $customerLogModel = new Corp_Ratnakar_CustomersLog();
        $objCustomerDetailModel = new Corp_Ratnakar_CustomerDetail();
        
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
    
    public function checkRatAFNDuplication($afn, $productId) {

        if (trim($afn) == ''){
            throw new Exception('AFN not found!');
        }

        $where = "rhc.afn ='" . $afn . "'";
        $where .= " AND (rhc.status='" . STATUS_ACTIVE . "' OR rhc.status='" . STATUS_INACTIVE . "')";

        $select = $this->_db->select()
                ->from(DbTable::TABLE_RAT_CORP_CARDHOLDER . ' as rhc', array('id'))
                ->where($where)
                ->where("product_id = ?", $productId);
        
        $row = $this->_db->fetchRow($select);
        if (empty($row))
            return false;
        else
            throw new Exception('AFN already exists!');
    }
    
    public function checkRatAadhaarDuplication($aadhaarNo, $productId) {

        if (trim($aadhaarNo) == '')
            throw new Exception('Aadhaar number not found!');

        $where = "rcm.aadhaar_no ='" . $aadhaarNo . "'";
        $where .= " AND (rcm.status='" . STATUS_ACTIVE . "' OR rcm.status='" . STATUS_INACTIVE . "')";

        $select = $this->_db->select()
                ->from(DbTable::TABLE_RAT_CORP_CARDHOLDER . ' as rcm', array('id'))
                ->where($where)
                ->where("product_id = ?", $productId);
        $row = $this->_db->fetchRow($select);
        if (empty($row))
            return false;
        else
            throw new Exception('Cardholder with same Aadhaar Number already exists!');
    }
    
    public function checkRatPANDuplication($pan, $productId) {

        if (trim($pan) == '')
            throw new Exception('PAN not found!');

        $where = "lower(rcm.id_proof_type) ='" . strtolower('PAN card') . "'";
        $where = "lower(rcm.id_proof_number) ='" . strtolower($pan) . "'";
        $where .= " AND (rcm.status='" . STATUS_ACTIVE . "' OR rcm.status='" . STATUS_INACTIVE . "')";

        $select = $this->_db->select()
                ->from(DbTable::TABLE_RAT_CORP_CARDHOLDER . ' as rcm', array('id'))
                ->where($where)
                ->where("product_id = ?", $productId);
        $row = $this->_db->fetchRow($select);
        if (empty($row))
            return false;
        else
            throw new Exception('Cardholder with same PAN already exists!');
	}

     public function exportSampleLoadRequests($param){
        //echo "<pre>vijay"; print_r($params); exit; 
        $apprvfrom = isset($param['apprvfrom']) ? $param['apprvfrom'] : '';
        $apprvto = isset($param['apprvto']) ? $param['apprvto'] : '';
        $product = isset($param['product_id']) && $param['product_id'] > 0 ? $param['product_id'] : '';
        $corporateId = isset($param['by_corporate_id']) ? $param['by_corporate_id'] : '';
        $status = isset($param['status']) ? $param['status'] : '';
        $department = isset($param['department']) ? $param['department'] : '';
        $location = isset($param['location']) ? $param['location'] : '';
        //echo $apprvfrom;exit;
        $select = $this->select();
        $select->from(DbTable::TABLE_RAT_CORP_CARDHOLDER . " as rhc",array('*','concat(rhc.first_name," ",rhc.last_name) as cardholder_name'));
        $select->setIntegrityCheck(false);
        $select->joinLeft(DbTable::TABLE_PRODUCTS . " as p", "rhc.product_id  = p.id",array('p.name as product_name'));
        $select->joinLeft(DbTable::TABLE_RAT_CORP_CARDHOLDER_DETAILS . " as rhcd", "rhcd.rat_cardholder_id  = rhc.id",array());
        $select->joinLeft(DbTable::TABLE_PURSE_MASTER . ' as pm',"rhc.product_id = pm.product_id",array('pm.code as wallet_code'));
        if ($product != ''){
            $select->where("rhc.product_id = '" . $product . "'");
        }
         if ($corporateId != ''){
            $select->where("rhc.by_corporate_id = '" . $corporateId . "'");
        }
        
        if ($apprvfrom != '' && $apprvto != ''){
            $select->where("rhc.date_approval >=  '" . $apprvfrom . "'");
            $select->where("rhc.date_approval <= '" . $apprvto . "'");
            $select->where("rhc.status_ops =  '" . STATUS_APPROVED . "'");
        }
        if($department!=''){
            $select->where("rhc.employer_name LIKE  '%" . $department . "%'");
        }
        if($location!=''){
            $select->where("rhcd.emp_city LIKE  '%" . $location . "%'");
        }
        $select->where("rhc.status =  '" . STATUS_ACTIVE . "'");
        //echo $select; //exit;
        $rs = $this->fetchAll($select);
        //echo "<pre>"; print_r($rs); exit;
        if(count($rs))
        { 
            $i=0;         
            foreach($rs as $data){
                if(!empty($data['card_number'])){
                    $retData[$i]['txn_identifier_type']  = CORP_WALLET_TXN_IDENTIFIER_CN;
                    $retData[$i]['card_number']          = $data['card_number'];
                }elseif($data['medi_assist_id']){
                    $retData[$i]['txn_identifier_type']  = CORP_WALLET_TXN_IDENTIFIER_MI;
                    $retData[$i]['card_number']          = $data['medi_assist_id'];
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
            //echo "<pre>"; print_r($retData); exit;
            return $retData;
        }else{
            throw new Exception('No Records');
            return false;
        }
    }
    public function updateCRNforApprovedCustomer($limit = RAT_CRN_UPDATE_LIMIT) {
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
           'member_id' => $customer['medi_assist_id'],
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
           'member_id' => $customer['medi_assist_id'],
           'card_pack_id' => $customer['card_pack_id'],
           'product_id' => $customer['product_id'],
        ));
    }
    
    private function updateCRNRecordForCustomer($customer) {
        $crnMaster = new CRNMaster();
        $rs = $this->getCRNByMemberIdCardpackId($customer);
        if(!empty($rs)) {
            $crnMaster->updateStatusByMemberIdCardpackId(array(
                'status' => STATUS_USED,
                'member_id' => $customer['medi_assist_id'],
                'card_pack_id' => $customer['card_pack_id'],
                'product_id' => $customer['product_id'],
            ));
            
            $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
            $rs['card_number'] = new Zend_Db_Expr("AES_ENCRYPT('".$rs['card_number']."','".$encryptionKey."')");
            
            $updateArr  = array(
                'card_number'       =>  $rs['card_number'],
                'card_pack_id'      =>  $rs['card_pack_id'],
                'medi_assist_id'    =>  $rs['member_id'],
                'status_ecs'        =>  STATUS_WAITING,
                'date_crn_update'   =>  new Zend_Db_Expr('NOW()') 
                );
            $whereCon = ' id="'.$customer['id'].'"';
            return $this->update($updateArr,$whereCon);
        }
        return FALSE;
    }
      public function getApprovedCustomerForCRNUpdate($limit) {
        $gprArr = array(PRODUCT_CONST_RAT_GPR);
        
        $productModel = new Products();
        $productid_gpr = $productModel->getProductIDbyConstArr($gprArr); 
        
        $details = $this->_db->select()
                ->from(DbTable::TABLE_RAT_CORP_CARDHOLDER, array('id', 'concat(first_name," ",last_name) as name', 'medi_assist_id', 'status', 'failed_reason', 'product_id', 'card_pack_id'))
                ->where("status = '". STATUS_PENDING." ' OR status = '" .STATUS_ECS_PENDING. "'")
                ->where("product_id IN ($productid_gpr)")
                ->where("status_ops = ?", STATUS_APPROVED)
                ->order('date_created DESC');
        
        return $this->_db->fetchAll($details);
    }
    
    public function ratGPRECSRegn() {
        $productModel = new Products();
        $custProductModel = new Corp_Ratnakar_CustomerProduct();
        $customerTrackModel = new CustomerTrack();
        $m = new \App\Messaging\Corp\Ratnakar\Operation();
        $user = Zend_Auth::getInstance()->getIdentity();
        $rat_arr = array(PRODUCT_CONST_RAT_GPR);
        $productid_rat = $productModel->getProductIDbyConstArr($rat_arr); 
        
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`rcc`.`card_number`,'".$decryptionKey."') as card_number");
        $crn = new Zend_Db_Expr("AES_DECRYPT(`rcc`.`crn`,'".$decryptionKey."') as crn");
        
        $select = $this->_db->select() 
                ->from(DbTable::TABLE_RAT_CORP_CARDHOLDER .' as rcc', array(
            'id', 'bank_id', 'product_id', 'rat_customer_id', 'customer_master_id', 'customer_type', $crn, 'unicode', $card_number, 'card_pack_id', 'afn', 'medi_assist_id', 'employee_id', 'partner_ref_no', 'txnrefnum', 'title', 'first_name', 'middle_name', 'last_name', 'name_on_card', 'gender', 'date_of_birth', 'aadhaar_no', 'pan', 'mobile', 'mobile2', 'email', 'landline', 'address_line1', 'address_line2', 'city', 'state', 'pincode', 'country', 'mother_maiden_name', 'employer_name', 'corporate_id', 'corp_address_line1', 'corp_address_line2', 'corp_city', 'corp_state', 'corp_pin', 'corp_country', 'id_proof_type', 'id_proof_number', 'id_proof_doc_id', 'address_proof_type', 'address_proof_number', 'address_proof_doc_id', 'occupation', 'is_card_activated', 'activation_date', 'is_card_dispatched', 'card_dispatch_date', 'by_ops_id', 'by_corporate_id', 'by_agent_id', 'batch_id', 'batch_name', 'date_created', 'date_updated', 'date_approval', 'date_toggle_kyc', 'date_activation', 'date_blocked', 'narration', 'txn_code', 'failed_reason', 'date_failed', 'date_crn_update', 'status_ecs', 'status_ops', 'status', 'aml_status'
                ))
                ->where("status_ops =?", STATUS_APPROVED)
                ->where("status = '". STATUS_PENDING."' OR status = '".STATUS_ECS_PENDING."'")
                ->where("status_ecs = '" . STATUS_WAITING . "' OR status_ecs = '" . STATUS_FAILURE . "'")
                ->where("product_id IN(?)", $productid_rat)
                ->order('id')
                ->limit(RAT_CORP_ECS_REGN_LIMIT);
                
        $dataArr = $this->_db->fetchAll($select);
        $numCust = 0;
        foreach ($dataArr as $data) {
            
            $id = $data['id'];


            $msg = '';
            try {
                   
                        $cardholderArray = $this->getCardholderArray($data);
                        $ecsApi = new App_Api_ECS_Corp_Ratnakar();
                        $resp = $ecsApi->mediAssistCardholderRegistration($cardholderArray);
                        if ($resp == false) {
                            $msg = $ecsApi->getError();
                        }
                
               
              
            
            if ($resp == true) {
                //On Success
                // update the status to STATUS_ACTIVE in cardholders
                $updateArr = array('status' => STATUS_ACTIVE, 'status_ecs' => STATUS_SUCCESS, 'date_activation' => new Zend_Db_Expr('NOW()'), 'failed_reason' =>'');
                $this->_db->update(DbTable::TABLE_RAT_CORP_CARDHOLDER, $updateArr, "id= $id");
                
             // Insert into customer Track
               $customerTrackArr =  array(
                    'card_number' => $data['card_number'],
                    'card_pack_id' => $data['card_pack_id'],
                    'member_id' => $data['medi_assist_id'],
                    'crn' => $data['card_number'],
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
                // 
            } else {
              
                    $updateArr = array('status' => STATUS_ECS_FAILED,'status_ecs' => STATUS_FAILURE, 'failed_reason' => $msg,
                        'date_failed' => new Zend_Db_Expr('NOW()'));
                    $this->update($updateArr, "id= $id");
               
                $updateprodArr = array('status' => STATUS_INACTIVE);
                $this->_db->update(DbTable::TABLE_RAT_CUSTOMER_PRODUCT, $updateprodArr, "product_customer_id= $id");
            }
        } catch (App_Exception $e) {
                $resp = false;
                $msg = $e->getMessage();
            } catch (Exception $e) {
                $resp = false;
                $msg = $e->getMessage();
            }
            $numCust++;
        }
        return $numCust;
    }

    public function searchcustomerKYC($param) {
        $productId = isset($param['product_id']) && $param['product_id'] > 0 ? $param['product_id'] : '';
        $status = isset($param['status']) ? $param['status'] : '';
        $columnName = $param['searchCriteria'];
        $keyword = $param['keyword'];
        
        if($columnName == 'aadhaar card' || $columnName == 'passport' ){
            $whereString = "kc.id_proof_type = '$columnName' AND kc.id_proof_number = '$keyword'";
        }else{
        $whereString = "kc.$columnName LIKE '%$keyword%'";
        }
        
        //Decryption of Card Number and CRN
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`kc`.`card_number`,'".$decryptionKey."') as card_number");
        $crn = new Zend_Db_Expr("AES_DECRYPT(`kc`.`crn`,'".$decryptionKey."') as crn");
        
        $details = $this->_db->select()
                ->from(DbTable::TABLE_RAT_CORP_CARDHOLDER .' as kc', array('kc.id', $crn,'kc.customer_master_id', $card_number, 'kc.afn', 'concat(kc.first_name," ",kc.last_name) as name', 'kc.gender', 'kc.date_of_birth', 'kc.mobile', 'kc.email','kc.medi_assist_id', 'kc.status', 'kc.name_on_card', 'kc.date_failed', 'kc.failed_reason','kc.status_ops','kc.card_pack_id','kc.employee_id','kc.employer_name','kc.corporate_id','kc.status','kc.customer_type'))
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
    
    public function updateKYC($params,$id) {
        $user = Zend_Auth::getInstance()->getIdentity();
        if (empty($params))
            throw new Exception('Data missing for processing');
        try {
           
                $this->update($params,"id = $id");
           

        } catch (Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            throw new Exception($e->getMessage());
        }
        return TRUE;
    }
    
    /*
     * Gets all the failed cardholders during bulk uploading
     */
    public function showFailedPendingCardholderDetails($batchName,$productId = 0, $page = 1, $paginate = NULL, $force = FALSE) {
        switch (CURRENT_MODULE) {
            case MODULE_AGENT: 
                $colName = 'by_agent_id';
                break;
            case MODULE_CORPORATE: 
                $colName = 'by_corporate_id';
                break;
            case MODULE_OPERATION:
                $colName = 'by_ops_id';
                break;
        }
        
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number"); 
        
        $user = Zend_Auth::getInstance()->getIdentity();
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_RAT_CORP_CARDHOLDER_BATCH,array('id', 'bank_id', 'product_id', $card_number, 'card_pack_id', 'afn', 'medi_assist_id', 'employee_id', 'first_name', 'middle_name', 'last_name', 'name_on_card', 'gender', 'date_of_birth', 'mobile', 'email', 'landline', 'address_line1', 'address_line2', 'city', 'pincode', 'mother_maiden_name', 'employer_name', 'corporate_id', 'corp_address_line1', 'corp_address_line2', 'corp_city', 'corp_pin', 'id_proof_type', 'id_proof_number', 'address_proof_type', 'address_proof_number', 'by_ops_id', 'by_agent_id', 'by_corporate_id', 'batch_name', 'date_created', 'date_updated', 'upload_status', 'failed_reason'));
        $select->where('upload_status = ?', STATUS_FAILED);
        $select->where('batch_name = ?', $batchName);
        $select->where('product_id = ?', $productId);
        $select->where("$colName = ?", $user->id);
        $select->order('id ASC');

        return $this->_db->fetchAll($select);
    }
    
    /*
     * Validates the fields of bulk uploaded file
     */
    public function isValid($param) {
        
        $v = new Validator();
        $card_number = isset($param[0]) ? $param[0] : '';
        $card_pack_id = isset($param[1]) ? $param[1] : '';
        $employee_id = isset($param[4]) ? $param[4] : '';
        $corporate_id = isset($param[20]) ? $param[20] : '';
        $employer_name = isset($param[19]) ? $param[19] : '';
        $first_name = isset($param[5]) ? $param[5] : '';
        $last_name = isset($param[7]) ? $param[7] : '';
        $name_on_card = isset($param[8]) ? $param[8] : '';        
        $email = isset($param[12]) ? $param[12] : '';
        $dob = isset($param[10]) ? $param[10] : '';
        $gender = isset($param[9]) ? $param[9] : '';
        $city = isset($param[16]) ? $param[16] : '';
        $pincode = isset($param[17]) ? $param[17] : '';
        $line1 = isset($param[14]) ? $param[14] : '';                
        $commcity = isset($param[23]) ? $param[23] : '';
        $commpincode = isset($param[24]) ? $param[24] : '';
        $commline1 = isset($param[21]) ? $param[21] : '';
        $mobile = isset($param[11]) ? $param[11] : '';
        $mother_maiden_name = isset($param[18]) ? $param[18] : '';
        $id_proof_type = isset($param[25]) ? $param[25] : '';
        $add_proof_type = isset($param[27]) ? $param['27'] : '';
        $id_proof_num = isset($param[26]) ? $param[26] : '';
        $address_proof_num = isset($param[28]) ? $param[28] : '';
        
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

        if($employee_id=='' || strlen($employee_id) <=0 || strlen($employee_id) > 15  || !(ctype_alnum($employee_id))){
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
    
     public function checkBatchFilename($fileName, $productId = 0) {
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_RAT_CORP_CARDHOLDER_BATCH, array('batch_name'));
        $select->where("batch_name =?", $fileName);
        if($productId > 0) {
            $select->where("product_id = ?", $productId);
        }
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
            
        if(isset($param['card_number'])){
            $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
            $cardNumber = new Zend_Db_Expr("AES_ENCRYPT('".$param['card_number']."','".$encryptionKey."')");
        } else {
            $cardNumber = '';
        }
          if (isset($cardNumber) && $cardNumber != '') {
               $select = $this->select();
               $select->from(DbTable::TABLE_RAT_CORP_CARDHOLDER, array('id'));
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
        if(isset($param['card_number'])){
            $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
            $cardNumber = new Zend_Db_Expr("AES_ENCRYPT('".$param['card_number']."','".$encryptionKey."')");
        } else {
            $cardNumber = '';
        }
        $batch_name = isset($param['batch_name']) ? $param['batch_name'] : '';  
          if (isset($cardNumber) && $cardNumber != '') {
               $select = $this->_db->select();
               $select->from(DbTable::TABLE_RAT_CORP_CARDHOLDER_BATCH, array('id'));
               $select->where("card_number =?", $cardNumber);
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
     
    public function updateAmlRatnakarCardholders(){
        $details = $this->_db->select()
                       ->from($this->_name,array('status','id','first_name', 'last_name'))
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

public function ratECSRegn($productConst) {
        $productModel = new Products();
        $custProductModel = new Corp_Ratnakar_CustomerProduct();
        $customerTrackModel = new CustomerTrack();
        $m = new \App\Messaging\Corp\Ratnakar\Operation();
        $user = Zend_Auth::getInstance()->getIdentity();
        $rat_arr = array($productConst);
        $productid_rat = $productModel->getProductIDbyConstArr($rat_arr); 
         
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`rcc`.`card_number`,'".$decryptionKey."') as card_number");
        $crn = new Zend_Db_Expr("AES_DECRYPT(`rcc`.`crn`,'".$decryptionKey."') as crn");
            
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_RAT_CORP_CARDHOLDER .' as rcc', array(
            'id', 'bank_id', 'product_id', 'rat_customer_id', 'customer_master_id', 'customer_type', $crn, 'unicode', $card_number, 'card_pack_id', 'afn', 'medi_assist_id', 'employee_id', 'partner_ref_no', 'txnrefnum', 'title', 'first_name', 'middle_name', 'last_name', 'name_on_card', 'gender', 'date_of_birth', 'aadhaar_no', 'pan', 'mobile', 'mobile2', 'email', 'landline', 'address_line1', 'address_line2', 'city', 'state', 'pincode', 'country', 'mother_maiden_name', 'employer_name', 'corporate_id', 'corp_address_line1', 'corp_address_line2', 'corp_city', 'corp_state', 'corp_pin', 'corp_country', 'id_proof_type', 'id_proof_number', 'id_proof_doc_id', 'address_proof_type', 'address_proof_number', 'address_proof_doc_id', 'occupation', 'is_card_activated', 'activation_date', 'is_card_dispatched', 'card_dispatch_date', 'by_ops_id', 'by_corporate_id', 'by_agent_id', 'batch_id', 'batch_name', 'date_created', 'date_updated', 'date_approval', 'date_toggle_kyc', 'date_activation', 'date_blocked', 'narration', 'txn_code', 'failed_reason', 'date_failed', 'date_crn_update', 'status_ecs', 'status_ops', 'status', 'aml_status'
                ));
        $select->where("status_ops =?", STATUS_APPROVED)
                ->where("status = '". STATUS_PENDING."' OR status = '".STATUS_ECS_PENDING."'")
                ->where("status_ecs = '" . STATUS_WAITING . "' OR status_ecs = '" . STATUS_FAILURE . "'")
                ->where("product_id IN(?)", $productid_rat)
                ->order('id')
                ->limit(RAT_CORP_ECS_REGN_LIMIT); 
        $dataArr = $this->_db->fetchAll($select);
        $numCust = 0;
        foreach ($dataArr as $data) {
            
            $id = $data['id'];


            $msg = '';
            try {
                   
                        $cardholderArray = $this->getCardholderArray($data);
                        $ecsApi = new App_Api_ECS_Corp_Ratnakar();
                        $resp = $ecsApi->mediAssistCardholderRegistration($cardholderArray);
                        if ($resp == false) {
                            $msg = $ecsApi->getError();
                        }
                
               
              
            
            if ($resp == true) {
                //On Success
                // update the status to STATUS_ACTIVE in cardholders
                $updateArr = array('status' => STATUS_ACTIVE, 'status_ecs' => STATUS_SUCCESS, 'date_activation' => new Zend_Db_Expr('NOW()'), 'failed_reason' =>'');
                $this->_db->update(DbTable::TABLE_RAT_CORP_CARDHOLDER, $updateArr, "id= $id");
                
             // Insert into customer Track
               $customerTrackArr =  array(
                    'card_number' => $data['card_number'],
                    'card_pack_id' => $data['card_pack_id'],
                    'member_id' => $data['medi_assist_id'],
                    'crn' => $data['card_number'],
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
                // 
            } else {
              
                    $updateArr = array('status' => STATUS_ECS_FAILED,'status_ecs' => STATUS_FAILURE, 'failed_reason' => $msg,
                        'date_failed' => new Zend_Db_Expr('NOW()'));
                    $this->update($updateArr, "id= $id");
               
                $updateprodArr = array('status' => STATUS_INACTIVE);
                $this->_db->update(DbTable::TABLE_RAT_CUSTOMER_PRODUCT, $updateprodArr, "product_customer_id= $id");
            }
        } catch (App_Exception $e) {
                $resp = false;
                $msg = $e->getMessage();
            } catch (Exception $e) {
                $resp = false;
                $msg = $e->getMessage();
            }
            $numCust++;
        }
        return $numCust;
    }
	
	public function queryregistraton($params){ 
    
        $select = $this->_db->select();
        $select->from($this->_name . " as cr", array('txn_code', 'product_id', 'mobile', 'email', 'status'));
        $select->joinLeft(DbTable::TABLE_AGENTS . ' as t', "cr.by_agent_id = t.id", array('agent_code'));
        $select->where('cr.txn_code = ?', $params['txn_code']);
        //$select->where('cr.status = ?', STATUS_ACTIVE); 

        $rs = $this->_db->fetchRow($select);
        $rs['response_code'] = 0;
        $rs['response_message'] = "Query Registration Successful";
        
        
        if($rs['status'] == CARDHOLDER_ACTIVE_STATUS){
            $rs['status'] = STATUS_SUCCESS;
        }
        
        if($rs['status'] == STATUS_ECS_PENDING || $rs['status'] = STATUS_ACTIVATION_PENDING){
            $rs['status'] = STATUS_PENDING;
        }
        
        if($rs['status'] == STATUS_ECS_FAILED){
            $rs['status'] = STATUS_FAILED;
        }
        
        
        if( !empty($rs) ){
            return TRUE;
        }
        else
            return FALSE;
   }
   
   public function querycustomer($params){ 
    
        $select = $this->_db->select();
        $select->from($this->_name . " as cr", array('card_pack_id','product_id', 'title', 'first_name', 'middle_name', 'last_name', 'gender', 'date_of_birth', 'mobile', 'mobile2', 'email', 'mother_maiden_name', 'landline', 'address_line1', 'address_line2', 'city', 'state', 'country', 'pincode'));
        $select->joinLeft(DbTable::TABLE_AGENTS . ' as t', "cr.by_agent_id = t.id", array('agent_code'));
        $select->where('t.agent_code = ?', $params['agent_code']);
        $select->where('cr.product_id = ?', $params['product_id']); 
        $select->where('cr.mobile = ?', $params['mobile']);
        $select->where('cr.email = ?', $params['email']); 

        $rs = $this->_db->fetchRow($select);
        
        if( !empty($rs) ){
            return TRUE;
        }
        else
            return FALSE;
   }
   
   /*
     * $param expects all cardholder details and cardholder_id
     */
    public function mapCardholderToRemitter($param,$cardholderID) {
    	
        $ratRemitterObj = new Remit_Ratnakar_Remitter();
        $objBaseTxn = new BaseTxn();
        $objFeePlan = new FeePlan();
        $objectRelation = new ObjectRelations();
        $bank = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR);
        
        $chkRemitterExists = $ratRemitterObj->remitterExists($param);
        // Check if remitter exists with mobile no and product_id = 0
       if(!empty($chkRemitterExists)){
          //Exists - get remitter_id and update cardholder
            $remitterID =  $chkRemitterExists['id'];
           
          }
            else{
                // Doen Not exists then add remitter with the details  
                
                // get fee
                if($param['manageType'] == CORPORATE_MANAGE_TYPE){
                $arrDetails = $objFeePlan->getCorpRemitterRegistrationFee($param['product_id'], $param['by_agent_id']);
                    
                }else{
                $arrDetails = $objFeePlan->getRemitterRegistrationFee($param['product_id'], $param['by_agent_id']);
                }
                $fee = isset($arrDetails['txn_flat'])?$arrDetails['txn_flat']:0;
                // Check allow remitter regn

//                $paramsTxnLimit = array('agent_id'  => $param['by_agent_id'],
//                                       'amount'    => $fee,
//                                       'bank_unicode' => $bank->bank->unicode);  
//               
//                $flgTxnLimit = $objBaseTxn->chkAllowRemitterRegn($paramsTxnLimit);
                $flgTxnLimit = TRUE;
                $fee = '0';
                 if($flgTxnLimit){
                $ip = $ratRemitterObj->formatIpAddress(Util::getIP());
                $remitterArr = array(  
                                        'bank_id' => $param['bank_id'],
                                        'name' => $param['name'],
                                        'middle_name' => $param['middle_name'],
                                        'last_name' => $param['last_name'],
                                        'product_id' => 0,
                                        'bank_name' => '', 
                                        'ifsc_code' => '',
                                        'bank_account_number' => '',
                                        'branch_name' => '', 
                                        'branch_city' => '',
                                        'branch_address' => '',  
                                        'bank_account_type' => '',
                                        'address' => $param['address'],
                                        'address_line2' => $param['address_line2'],
                                        'city' => $param['city'],
                                        'state' => $param['state'],
                                        'pincode' => $param['pincode'],
                                        'mobile_country_code' => '91',
                                        'mobile' => $param['mobile'],                            
                                        'dob' => $param['dob'],                                                       
                                        'mother_maiden_name' => $param['mother_maiden_name'],                                                       
                                        'email' => $param['email'],
                                        'legal_id' => '',
                                        'txn_code' => $param['txn_code'],
                                        'ip' => $ip,
                                        'date_created' => date('Y-m-d H:i:s'),
                                        'status' => STATUS_ACTIVE                                          
                                     ); 
                 if(($param['manageType'] == CORPORATE_MANAGE_TYPE)){
                    $remitterArr['by_corporate_id']=$param['by_agent_id'];
                 }else{
                   $remitterArr['by_agent_id']=$param['by_agent_id'];  
                 }
             
            $ratRemitterObj->insert($remitterArr);
               // get last inserted id 
             $remitterID = $this->_db->lastInsertId(DbTable::TABLE_RATNAKAR_REMITTERS, 'id');
             
               if($fee > 0){
               // remitter regn fee
                 $feeComponent = Util::getFeeComponents($fee);

                                // checking for transaction response
                                $txnCode = 0;
                                $txnResponse = FALSE;
                                $remitterTxnData = array(
                                                    'remitter_id'=> $remitterID,
                                                    'agent_id'=> $param['by_agent_id'],
                                                    'product_id'=>$param['product_id'],
                                                    'fee_amt'=>$feeComponent['partialFee'],
                                                    'service_tax'=>$feeComponent['serviceTax'],
                                                    'bank_unicode' => $bank->bank->unicode
                                                    );

                                try{
                                    $txnCode = $objBaseTxn->remitterRegnFee($remitterTxnData);
                                    $txnResponse = TRUE;                                 
                                }catch (Exception $e ){ 
                                    App_Logger::log($e->getMessage(), Zend_Log::ERR);                    
                                   // $this->_helper->FlashMessenger(array( 'msg-error' => $e->getMessage(),));
                                    $code = $e->getCode();
                                    $code = (empty($code)) ? ErrorCodes::ERROR_CUSTOMER_REGISTRATION_FAIL_CODE : $code;
                                    $message = $e->getMessage();
                                    $message = (empty($message)) ? ErrorCodes::ERROR_CUSTOMER_REGISTRATION_FAIL_MSG : $message;
                                    throw new App_Exception($message, $code);
                                }
                            }else{
                                $txnResponse = TRUE;
                                $feeComponent['partialFee'] = 0;
                                $feeComponent['serviceTax'] = 0;
                            }
                            if($txnResponse){
                                try{
                                    $ratRemitterObj->updateRemitter(array('regn_fee'=>$feeComponent['partialFee'],'service_tax'=>$feeComponent['serviceTax'], 'txn_code' => $txnCode, 'status'=>STATUS_ACTIVE), $remitterID);
                                }catch (Exception $e ){ 
                                    App_Logger::log($e->getMessage(), Zend_Log::ERR); 
                                    //$this->_helper->FlashMessenger(array( 'msg-error' => $e->getMessage(),));
                                    $code = $e->getCode();
                                    $code = (empty($code)) ? ErrorCodes::ERROR_CUSTOMER_REGISTRATION_FAIL_CODE : $code;
                                    $message = $e->getMessage();
                                    $message = (empty($message)) ? ErrorCodes::ERROR_CUSTOMER_REGISTRATION_FAIL_MSG : $message;
                                    throw new App_Exception($message, $code);
                                }
                            }
             }// If  remitter regn limit allowed  
           
          }
           
            
            //and insert remitter ID and cardholder ID in Object relation
            $objRelationTypeId = $objectRelation->getRelationTypeId(RAT_MAPPER);
            $relationData =  array(
                'from_object_id' => $cardholderID,
                'to_object_id' => $remitterID,
                'object_relation_type_id' => $objRelationTypeId
                );
           
             $objectRelation->insert($relationData);
             
           // $updateArr = array('remitter_id' => $remitterID);
           // $this->updateCardholder($updateArr,$cardholderID);
            return TRUE;
        }
		
		public function updateCardholderAPI($arr, $id) {
        $flgUpdate = $this->_db->update(DbTable::TABLE_RAT_CORP_CARDHOLDER, $arr, "id=$id");
        if($flgUpdate){
        return TRUE;
        }else{
        return FALSE;  
        }
    }
    
    public function updateRemitterAPI($arr, $id) {
        $flgUpdate = $this->_db->update(DbTable::TABLE_RATNAKAR_REMITTERS, $arr, "id=$id");
        if($flgUpdate){
        return TRUE;
        }else{
        return FALSE;  
        }
    }
    
     public function updateCardholderMasterAPI($arr, $id) {
        
        $flgUpdate = $this->_db->update(DbTable::TABLE_RAT_CUSTOMER_MASTER, $arr, "id=$id");
        if($flgUpdate){
        return TRUE;
        }else{
        return FALSE;  
        }
    }

   public function generateSMSDetails($params, $smsType) {
        $m = new \App\Messaging\Remit\Ratnakar\Api();
        //$productModel = new Products();
        $refObject = new Reference();
        //$productDetails = $productModel->getProductInfo($params['product_id']);
        $productSupportInfo = $refObject->getProductSupportInfo($params['product_id']);
        $productName = $productSupportInfo['product_name'];
        $productsupportEmail = $productSupportInfo['support_email'];
        $productSMSsender = $productSupportInfo['sms_sender'];
        $productSMSname = $productSupportInfo['smsname'];
        if(isset($params['card_number'])){
           $lastFour =  substr($params['card_number'], -4);
        }elseif(isset($params['mobile'])){
            $lastFour =  substr($params['mobile'], -4);
        }else{
            $lastFour =  '';
        }
        $smsData = array(
            'product_name' => $productName,
            'balance' => $params['balance'],
            'amount' => $params['amount'],
            'bene_name' => $params['bene_name'],
            'wallet_code' => $params['wallet_code'],
            'ref_num' => $params['ref_num'],
            'mobile' => $params['mobile'],
            'last_four' => $lastFour,
            'product_supportemail' => $productsupportEmail,
            'sms_sender' => $productSMSsender,
            'sms_name' => $productSMSname,
        );
        switch ($smsType) {
            case 'CUST_REGISTRATION' :
                $m->cardActivation($smsData);
                break;
            case 'CARD_BLOCK_SMS' :
                $m->cardBlock($smsData);
                break;
            case 'CARD_UNBLOCK_SMS' :
                $m->cardUnblock($smsData);
                break;
            case 'BALANCE_ENQUIRY_SMS' :
                $m->balanceEnquiry($smsData);
                break;
             case 'TRANSACTION_REQUEST_CR' :
                $m->cardLoad($smsData);
                break;
            case 'TRANSACTION_REQUEST_DR' :
                $m->cardDebit($smsData);
                break;
            case 'REMITTANCE' :
                $m->remittance($smsData);
                break;
            case 'TRANSFER' :
                $m->transfer($smsData);
               break;
            case 'UPDATE_CUST_SMS' :
                $m->cardUpdation($smsData);
                break;
            case 'BENE_REG_SMS' :
                $m->cardBeneReg($smsData);
                break;
            case 'CUST_CARD_BLOCK_SMS' :
                $m->custCardBlock($smsData);
                break;
            default :
                return FALSE;
        }
    }
   
/*
      * addCustomerAPI : add new customer without ECS, which is register by API  
      */
     
     public function addCustomerAPI($dataArr = array()) {
         
        //echo '<pre>';print_r($dataArr);exit;
        if (empty($dataArr)) {
            throw new Exception(ErrorCodes::ERROR_INVALID_DATA_ADD_CUST_FAILURE_MSG, ErrorCodes::ERROR_INVALID_DATA_ADD_CUST_FAILURE_CODE); 
        }
        elseif (empty($dataArr['ProductId'])) {
            throw new Exception(ErrorCodes::ERROR_EDIGITAL_PRODUCT_ID_MISS_MSG, ErrorCodes::ERROR_EDIGITAL_PRODUCT_ID_MISS_CODE);
        }
        
        $custProductModel = new Corp_Ratnakar_CustomerProduct();
        $custPurseModel = new Corp_Ratnakar_CustomerPurse();
        $masterPurseModel = new MasterPurse();
        $objCustomerMaster = new CustomerMaster();
        $customerTrackModel = new CustomerTrack();
        $objCustomers = new Customers();
        $productModel = new Products();
        $customersArr = array();
        
        $productDetail = $productModel->getProductInfo($dataArr['ProductId']);
        $dataArr['bank_id'] = $productDetail['bank_id'];        
        try {
//            $this->_db->beginTransaction();
            
                $customerMasterId = $objCustomerMaster->generateCustomerId(array('bank_id' => $dataArr['bank_id']));
               
                $custMasterDetails = $objCustomerMaster->findById($customerMasterId);
                
                $custMasterDetail = Util::toArray($custMasterDetails);
                // adding data in rat_customer_master table
                
                $ratCustomerMasterData = array(
                    'bank_id' => $dataArr['bank_id'],
                    'customer_master_id' => $customerMasterId,
                    'shmart_crn' => $custMasterDetail['shmart_crn'],
                    'first_name' => $dataArr['FirstName'],
                    'middle_name' => $dataArr['MiddleName'],
                    'last_name' => $dataArr['LastName'],
                    'aadhaar_no' => '',
                    'pan' => '',
                    'mobile_country_code' => isset($dataArr['mobile_country_code']) ? $dataArr['mobile_country_code'] : '',
                    'mobile' => $dataArr['Mobile'],
                    'email' => $dataArr['Email'],
                    'gender' => $dataArr['Gender'],
                    'date_of_birth' => isset($dataArr['DateOfBirth']) ? $dataArr['DateOfBirth'] : '',
                    'status' => STATUS_ACTIVE,
                );
                 
                $ratCustomerId = $this->addRatCustomerMaster($ratCustomerMasterData);
                 
                //echo 'ratCustomerId=>'.$ratCustomerId; exit;
                //insert into customer purse
                $purseDetails = $masterPurseModel->getPurseDetailsbyBankIdProductId($dataArr['ProductId'], $productDetail['bank_id']);
                foreach ($purseDetails as $purseDetail) {
                    $purseArr = array(
                        'customer_master_id' => $customerMasterId,
                        'rat_customer_id' => $ratCustomerId,
                        'product_id' => $dataArr['ProductId'],
                        'purse_master_id' => $purseDetail['id'],
                        'bank_id' => $dataArr['bank_id'],
                        //'by_ops_id' => $user->id,
                        'date_updated' => new Zend_Db_Expr('NOW()')
                    );
                    $purseParam = array('rat_customer_id' => $ratCustomerId, 'purse_master_id' => $purseDetail['id']);
                    
                    $purseDetails = $custPurseModel->getCustPurseDetails($purseParam);
                    if (empty($purseDetails)) { // If purse entry not found
                        
                        $custPurseModel->save($purseArr);
                       
                    }
                }
                 // Add Customers record for global products  
                $customersArr['mobile'] = $dataArr['Mobile'];
                $customersArr['bank_id'] = $dataArr['bank_id'];
                $customersArr['product_id'] = $dataArr['ProductId'];
                $customersArr['bank_customer_id'] = $ratCustomerId;
                $customersArr['customer_type'] = $dataArr['customer_type'];
                 
                $txnCode = $dataArr['txnCode']; 
                if($dataArr['CardNumber'] !='')
                {
                $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
                $cardNumberEnc = new Zend_Db_Expr("AES_ENCRYPT('".$dataArr['CardNumber']."','".$encryptionKey."')");
                }  else {
                  $cardNumberEnc = '';  
                }
                $dataCardholder = array(
                    'bank_id' => $dataArr['bank_id'],
                    'product_id' => $dataArr['ProductId'],
                    'rat_customer_id' => $ratCustomerId,
                    'customer_master_id' => $customerMasterId,
                    'customer_type' => $dataArr['customer_type'],
                    'afn' => $dataArr['afn'],
                    'crn' => $cardNumberEnc,
                    'card_number' => $cardNumberEnc,
                    'card_pack_id' => $dataArr['CardPackId'],
                    'medi_assist_id' => $dataArr['MemberId'],
                    'employee_id' => $dataArr['employee_id'],
                    'partner_ref_no' => $dataArr['PartnerRefNo'],
                    'txnrefnum' => $dataArr['TransactionRefNo'],
                    'title' => $dataArr['Title'],
                    'first_name' => $dataArr['FirstName'],
                    'middle_name' => $dataArr['MiddleName'],
                    'last_name' => $dataArr['LastName'],
                    'name_on_card' => $dataArr['NameOnCard'],
                    'gender' => $dataArr['Gender'],
                    'date_of_birth' =>$dataArr['DateOfBirth'],
                    'aadhaar_no' =>$dataArr['aadhaar_no'],
                    'pan' =>$dataArr['pan'],
                    'mobile' => $dataArr['Mobile'],
                    'mobile2' => $dataArr['Mobile2'],
                    'email' => $dataArr['Email'],
                    'landline' => $dataArr['Landline'],
                    'address_line1' => $dataArr['AddressLine1'],
                    'address_line2' => $dataArr['AddressLine2'],
                    'city' => $dataArr['City'],
                    'state' => $dataArr['State'],
                    'pincode' => $dataArr['Pincode'],
                    'country' => $dataArr['Country'],
                    'mother_maiden_name' => $dataArr['MotherMaidenName'],
                    'employer_name' => $dataArr['EmployerName'],
                    'corporate_id' => $dataArr['corporate_id'],
                    'corp_address_line1' => $dataArr['corp_address_line1'],
                    'corp_address_line2' => $dataArr['corp_address_line2'],
                    'corp_city' => $dataArr['corp_city'],
                    'corp_state' => $dataArr['corp_state'],
                    'corp_pin' => $dataArr['corp_pin'],
                    'corp_country' => $dataArr['corp_country'],
                    'id_proof_type' => $dataArr['id_proof_type'],
                    'id_proof_number' => '',
                    'address_proof_type' => '',
                    'address_proof_number' => '',
                    'occupation' => '',
                    'is_card_activated' => $dataArr['IsCardActivated'],
                    'activation_date' => $dataArr['ActivationDate'],
                    'is_card_dispatched' => $dataArr['IsCardDispatch'],
                    'card_dispatch_date' => $dataArr['CardDispatchDate'],
                    'by_ops_id' => 0,
                    'batch_id' => 0,
                    'batch_name' => '',
                    'date_created' => new Zend_Db_Expr('NOW()'),
                    'status_ops' => $dataArr['status_ops'],
                    'status' => $dataArr['status'],
                    'status_ecs' => $dataArr['status_ecs'],
		    'channel' => $dataArr['channel']
                );
               
               
                 if(($dataArr['manageType'] == CORPORATE_MANAGE_TYPE)){
                    $dataCardholder['by_ops_id']='';
                    $dataCardholder['by_corporate_id']=$dataArr['by_api_user_id'];
                 }else{
                   $dataCardholder['by_agent_id']=$dataArr['by_api_user_id'];  
                 }
                 
                $this->insert($dataCardholder);
                $cardholderId = $this->_db->lastInsertId(DbTable::TABLE_RAT_CORP_CARDHOLDER, 'id');
             
                // Entry in Customer product table
                $prodArr = array('product_customer_id' => $cardholderId,
                    'product_id' => $dataArr['ProductId'],
                    'rat_customer_id' => $ratCustomerId,
                    'program_type' => $productDetail['program_type'],
                    'bank_id' => $dataArr['bank_id'],
                    'by_ops_id' => 0,
                    'date_created' => new Zend_Db_Expr('NOW()'),
                    'status' => STATUS_ACTIVE);
                 if(($dataArr['manageType'] == CORPORATE_MANAGE_TYPE)){
                    $prodArr['by_corporate_id']=$dataArr['by_api_user_id'];
                 }else{
                   $prodArr['by_agent_id']=$dataArr['by_api_user_id'];  
                 }
               $custProductModel->save($prodArr);
                
               $custDetailArr = array(
                    'bank_id' => $dataArr['bank_id'],
                    'rat_cardholder_id' => $cardholderId,
                    'product_id' => $dataArr['ProductId'],
                    'employer_name' => $dataArr['EmployerName'],
                    'emp_address_line1' => $dataArr['EmployerAddressLine1'],
                    'emp_address_line2' => $dataArr['EmployerAddressLine2'],
                    'emp_city' => $dataArr['EmployerCity'],
                    'emp_state' => $dataArr['EmployerState'],
                    'emp_pin' => $dataArr['EmployerPin'],
                    'date_created' => new Zend_Db_Expr('NOW()'),
                    'status' => STATUS_ACTIVE
               );
              
                $this->insertCardhoderDetail($custDetailArr);
                $dataCardholder['id'] = $cardholderId;
                
                if($dataArr['CardNumber'] !=''){
                    
                
                $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
                $cardNumEnc = new Zend_Db_Expr("AES_ENCRYPT('".$dataArr['CardNumber']."','".$encryptionKey."')");
                }  else {
                 $cardNumEnc = '';  
                }
                $customerTrackArr =  array(
                         'card_number' => $cardNumEnc,
                         'card_pack_id' => $dataArr['CardPackId'],
                         'partner_ref_no' => $dataArr['PartnerRefNo'],
                         'crn' => $cardNumEnc,
                         'mobile' => $dataArr['Mobile'],
                         'email' => $dataArr['Email'],
                         'name_on_card' => $dataArr['NameOnCard'],
                         'bank_id' => $dataArr['bank_id'],
                     );
                    $customerTrackModel->customerDetailsAPI($customerTrackArr, $dataArr['ProductId'], $cardholderId, $dataArr['bank_id']);

           
            if(isset($cardholderId) && $cardholderId > 0) {
             
             $updateArr = array('status' => STATUS_ACTIVE,'txn_code' => $txnCode);  
              $resp = $this->update($updateArr, "id= $cardholderId");
               
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
                   $newcustDetailID = $objCustomers->addCustomerDetails($custDetailArr);
                  
                  }
            }
//            $this->_db->commit();
            $this->setTxncode($cardholderId);
            return $cardholderId;
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
          //  echo '<pre>';print_r($e);exit;
            // If any of the queries failed and threw an exception,
            // we want to roll back the whole transaction, reversing
            // changes made in the transaction, even those that succeeded.
            // Thus all changes are committed together, or none are.
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
//            $this->_db->rollBack();
            //throw new Exception ("Transaction not completed due to system failure");
            $code = $e->getCode();
            $code = (empty($code)) ? ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_CODE : $code;
            //$message = $e->getMessage();
            $message = (empty($msg)) ? ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_MSG : $msg;

            
            throw new Exception($message, $code);
        }
       
        return TRUE;
    }

    
    public function showOpsrejectedCustomerDetails($page = 1,$param = array(), $paginate = NULL, $force = FALSE) {
        $productId = isset($param['product_id']) && $param['product_id'] > 0 ? $param['product_id'] : '';
        $user = Zend_Auth::getInstance()->getIdentity();    
        $select = $this->select();
        $select->from(DbTable::TABLE_RAT_CORP_CARDHOLDER . ' as c');
        if(!empty($user->corporate_code)){
           $select->where("c.by_corporate_id = '" . $user->id . "'");
        }else{
            $select->where("c.by_agent_id = '" . $user->id . "'");
        }
        $select->where("c.status_ops = '" . STATUS_REJECTED . "'");
       
        if($productId != ''){
             $select->where("c.product_id =?" , $productId);
        }
      
        $select->order('id ASC');
        return $this->_paginate($select, $page, $paginate);
    }


    /*
     * editCustomerAPI : Edit customer detail
     */
     public function editCustomerAPI($dataArr = array()) {         
        if (empty($dataArr)) {
            throw new Exception(ErrorCodes::ERROR_INVALID_DATA_EDIT_CUSTOMER_MSG, ErrorCodes::ERROR_INVALID_DATA_EDIT_CUSTOMER_CODE);
        }
        elseif (empty($dataArr['ProductId'])) {
            throw new Exception(ErrorCodes::ERROR_EDIGITAL_PRODUCT_ID_MISS_MSG, ErrorCodes::ERROR_EDIGITAL_PRODUCT_ID_MISS_CODE);
        }else if(empty($dataArr['partner_ref_no']) && empty($dataArr['mobile'])) {
            throw new Exception(ErrorCodes::ERROR_INVALID_CUSTOMER_IDENTIFIER_EDIT_CUSTOMER_MSG, ErrorCodes::ERROR_INVALID_CUSTOMER_IDENTIFIER_EDIT_CUSTOMER_CODE);
        }
       $objectRelation = new ObjectRelations();
       $customerTrackModel = new CustomerTrack();
       $objCustomers = new Customers();
       $customersArr = array();
       $custRecord = array(
                    'mobile' => $dataArr['mobile'],
                    'partner_ref_no' => $dataArr['partner_ref_no'],
                    'txnrefnum' => $dataArr['TransactionRefNo'],
                    'product_id' => $dataArr['ProductId'],
                );
       
       $custDetail = $this->getCustomerInfoBy($custRecord);
      
       $custDetails = Util::toArray($custDetail);
      
        $custID = $custDetails['id'];
     
       if($custID > 0){
       $remitterID = $objectRelation->getToObjectInfo($custID, RAT_MAPPER);
       $custRemitterID = $remitterID['to_object_id']; 
       $customerMasterID = $custDetails['customer_master_id'];
       $customerID = $custDetails['rat_customer_id'];
       
       $customersArr['bank_customer_id'] = $customerID;
       $customersArr['product_customer_id'] = $custID;
       $customersArr['product_id'] = $dataArr['ProductId'];
       $customersArr['mobile'] = $dataArr['Mobile'];
       
      $checkDupliCustomer = $objCustomers->checkDupliCustomerMobile($customersArr);
        if($checkDupliCustomer){
          throw new Exception(ErrorCodes::ERROR_EDIGITAL_MOBILE_USED_MSG, ErrorCodes::ERROR_EDIGITAL_MOBILE_USED_CODE);   
        }
       
       }else{
          throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE);  
       }
       
       $dataCardholder = array();
       $dataCardholder_remit = array();
       $customerTrackArr = array();
       $custMobile = '';
       if(!empty($dataArr['Mobile'])){
           $dataCardholder['mobile'] = $dataArr['Mobile'];
           $dataCardholder_remit['mobile'] = $dataArr['Mobile'];
           $customerTrackArr['mobile'] = $dataArr['Mobile'];
           $custMobile = $dataArr['Mobile'];
       }
       
       if(!empty($dataArr['Email'])){
           $dataCardholder['email'] = $dataArr['Email'];
           $dataCardholder_remit['email'] = $dataArr['Email'];
           $customerTrackArr['email'] = $dataArr['Email'];
       }
        if(!empty($dataArr['Landline'])){
           $dataCardholder['landline'] = $dataArr['Landline'];
       }
       if(!empty($dataArr['AddressLine1'])){
           $dataCardholder['address_line1'] = $dataArr['AddressLine1'];
           $dataCardholder_remit['address'] = $dataArr['AddressLine1'];
       }
       if(!empty($dataArr['AddressLine2'])){
           $dataCardholder['address_line2'] = $dataArr['AddressLine2'];
           $dataCardholder_remit['address_line2'] = $dataArr['AddressLine2'];
       }
       if(!empty($dataArr['City'])){
           $dataCardholder['city'] = $dataArr['City'];
           $dataCardholder_remit['city'] = $dataArr['City'];
       }
       if(!empty($dataArr['State'])){
           $dataCardholder['state'] = $dataArr['State'];
           $dataCardholder_remit['state'] = $dataArr['State'];
       }
       if(!empty($dataArr['Pincode'])){
           $dataCardholder['pincode'] = $dataArr['Pincode'];
           $dataCardholder_remit['pincode'] = $dataArr['Pincode'];
       }
       if(!empty($dataArr['Country'])){
           $dataCardholder['country'] = $dataArr['Country'];
       }
      
       try{
           
           if( ($custID > 0) && ($custRemitterID >0) ){
               $custDetails['product_customer_id'] = $custDetails['id'];
               unset($custDetails['id']);
              
             $resp = $this->updateCardholderAPI($dataCardholder, $custID);
           //  if(($custRemitterID > 0)){
             //if(($custRemitterID > 0) && ($resp) ){
                $this->updateRemitterAPI($dataCardholder_remit, $custRemitterID);
                 $checkCustomer = $objCustomers->checkCustomerDetails($customersArr);
                
                if(!empty($checkCustomer) && ($custMobile !='') ){
                    
                    $updateCustArr = array('mobile' => $custMobile); 
                    $objCustomers->updateBankCustomers($updateCustArr, $checkCustomer);
                  }
                $this->_db->insert(DbTable::TABLE_RAT_UPDATE_CORP_CARDHOLDERS_LOG, $custDetails);
                  
                if(!empty($customerTrackArr)){
                $customerTrackModel->editCustomerDetails($customerTrackArr, $dataArr['ProductId'], $custID);
                $this->updateCardholderMasterAPI($customerTrackArr, $customerID);
               
                }
                
          }else{
             // throw new Exception('Data missing for edit cardholder'); 
              return false;
          }
          
       } catch (Exception $ex) {
        //    throw new Exception('Data is not changed, may be record is already updated');
           return true;
       }
    
       return true;
        
     }
     
     public function getCustomerInfoByPartnerRefNo($productID, $partnerRefNo){
        
        $select = $this->select();
        $select->where("product_id = ?",$productID);
        $select->where("partner_ref_no = ?",$partnerRefNo);
        $select->where("status = '".STATUS_ACTIVE."'");
       
        $rs = $this->fetchRow($select);  
        if(!empty($rs)) {
            return $rs;
        }
        return FALSE;
     }
     
     
      public function getCustomerInfoBy($param){
         
        $partnerRefNo = isset($param['partner_ref_no']) ? $param['partner_ref_no'] : '';
        $productID = isset($param['product_id']) ? $param['product_id'] : '';
        $mobile = isset($param['mobile']) ? $param['mobile'] : '';
        
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number");
        
        $select = $this->select()
                  ->from($this->_name, array('id', 'mobile', 'txn_code','customer_master_id','rat_customer_id','partner_ref_no','product_id',$card_number));
        if ($productID == ''){
         throw new Exception('Product code is mandatory');
        }
        $select->where("product_id = '" . $productID . "'");
        if ($mobile != ''){
            $select->where("mobile = '" . $mobile . "'");
        }
        if ($partnerRefNo != ''){
            $select->where("partner_ref_no = '" . $partnerRefNo . "'");
        }
       
         $select->where("status = '".STATUS_ACTIVE."'");
         $rs = $this->fetchRow($select);  
        if(!empty($rs)) {
            return $rs;
         
        }else{
        return FALSE;
        }
     }
     
     /* getCardholderInfo() will find cardholder details
     * param: cardholder id
     */
 
     public function getCardholderInfoByCard($cardNumber = '', $chkActive = FLAG_NO) {

         if($cardNumber == '') {
             return array();
         }
        $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $cardNumber = new Zend_Db_Expr("AES_ENCRYPT('".$cardNumber."','".$encryptionKey."')");
            
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`rhc`.`card_number`,'".$decryptionKey."') as card_number");
        
        $select = $this->select();
        $select->from(DbTable::TABLE_RAT_CORP_CARDHOLDER . " as rhc",
                array('rhc.id as id','rhc.customer_master_id', $card_number,'rhc.rat_customer_id',
                    'rhc.medi_assist_id', 'rhc.employee_id',
                    'concat(rhc.first_name," ",rhc.last_name) as cardholder_name', 'rhc.gender',
                    'rhc.mobile', 'rhc.email', 'rhc.employer_name', 'rhc.status as cardholder_status',
                    'rhc.afn', 'rhc.mobile', 'rhc.first_name', 'rhc.middle_name', 'rhc.last_name',
                    'rhc.date_of_birth', 'rhc.batch_name', 'rhc.corporate_id', 'rhc.product_id', 'rhc.city', 'rhc.customer_type','rhc.partner_ref_no'));
        $select->where("rhc.card_number = ?", $cardNumber);
        if($chkActive == FLAG_YES) {
            $select->where("rhc.status = ?", STATUS_ACTIVE);
        }
        return $this->fetchRow($select);
    }

    
     /*
      * addCustomerECSAPI : add new customer without ECS, which is register by API  
      */
     
     public function addCustomerECSAPI($dataArr = array()) {
         
        //echo '<pre>';print_r($dataArr);exit;
        if (empty($dataArr)) {
            throw new Exception('Data missing for add cardholder');
        }
        elseif (empty($dataArr['ProductId'])) {
            throw new Exception('Product missing for add cardholder');
        }
        
        $custProductModel = new Corp_Ratnakar_CustomerProduct();
        $custPurseModel = new Corp_Ratnakar_CustomerPurse();
        $masterPurseModel = new MasterPurse();
        $objCustomerMaster = new CustomerMaster();
        $customerTrackModel = new CustomerTrack();
        $objCustomers = new Customers();
        $bankModel = new Banks();
        $productModel = new Products();
        $crnMaster = new CRNMaster();
        $m = new \App\Messaging\Corp\Ratnakar\Operation();
        $str = '';
        $customersArr = array();
        
        $productDetail = $productModel->getProductInfo($dataArr['ProductId']);
        $dataArr['bank_id'] = $productDetail['bank_id'];        
        try {
//            $this->_db->beginTransaction();
            
           
            $paramChk = array(
                'mobile' => $dataArr['Mobile'],
                'partner_ref_no' => $dataArr['PartnerRefNo'],
                'card_number' => $dataArr['CardNumber'],
                'product_id' => $dataArr['ProductId']
            );
            
                $customerMasterId = $objCustomerMaster->generateCustomerId(array('bank_id' => $dataArr['bank_id']));
               
                $custMasterDetails = $objCustomerMaster->findById($customerMasterId);
                
                $custMasterDetail = Util::toArray($custMasterDetails);
                // adding data in rat_customer_master table
                
                $ratCustomerMasterData = array(
                    'bank_id' => $dataArr['bank_id'],
                    'customer_master_id' => $customerMasterId,
                    'shmart_crn' => $custMasterDetail['shmart_crn'],
                    'first_name' => $dataArr['FirstName'],
                    'middle_name' => $dataArr['MiddleName'],
                    'last_name' => $dataArr['LastName'],
                    'aadhaar_no' => '',
                    'pan' => '',
                    'mobile_country_code' => isset($dataArr['mobile_country_code']) ? $dataArr['mobile_country_code'] : '',
                    'mobile' => $dataArr['Mobile'],
                    'email' => $dataArr['Email'],
                    'gender' => $dataArr['Gender'],
                    'date_of_birth' => isset($dataArr['DateOfBirth']) ? $dataArr['DateOfBirth'] : '',
                    'status' => STATUS_ACTIVE,
                );
                 
                $ratCustomerId = $this->addRatCustomerMaster($ratCustomerMasterData);
            
                //insert into customer purse
                $purseDetails = $masterPurseModel->getPurseDetailsbyBankIdProductId($dataArr['ProductId'], $productDetail['bank_id']);
                foreach ($purseDetails as $purseDetail) {
                    $purseArr = array(
                        'customer_master_id' => $customerMasterId,
                        'rat_customer_id' => $ratCustomerId,
                        'product_id' => $dataArr['ProductId'],
                        'purse_master_id' => $purseDetail['id'],
                        'bank_id' => $dataArr['bank_id'],
                        //'by_ops_id' => $user->id,
                        'date_updated' => new Zend_Db_Expr('NOW()')
                    );
                    $purseParam = array('rat_customer_id' => $ratCustomerId, 'purse_master_id' => $purseDetail['id']);
                    
                    $purseDetails = $custPurseModel->getCustPurseDetails($purseParam);
                    if (empty($purseDetails)) { // If purse entry not found
                        
                        $custPurseModel->save($purseArr);
                       
                    }
                }
                 // Add Customers record for global products  
                $customersArr['mobile'] = $dataArr['Mobile'];
                $customersArr['bank_id'] = $dataArr['bank_id'];
                $customersArr['product_id'] = $dataArr['ProductId'];
                $customersArr['bank_customer_id'] = $ratCustomerId;
                $customersArr['customer_type'] = $dataArr['customer_type'];
                 
                $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
                $cardNumberEnc = new Zend_Db_Expr("AES_ENCRYPT('".$dataArr['CardNumber']."','".$encryptionKey."')");
            
                $txnCode = $dataArr['txnCode'];
                $dataCardholder = array(
                    'bank_id' => $dataArr['bank_id'],
                    'product_id' => $dataArr['ProductId'],
                    'rat_customer_id' => $ratCustomerId,
                    'customer_master_id' => $customerMasterId,
                    'customer_type' => $dataArr['customer_type'],
                    'afn' => $dataArr['afn'],
                    'crn' => $cardNumberEnc,
                    'card_number' => $cardNumberEnc,
                    'card_pack_id' => $dataArr['CardPackId'],
                    'medi_assist_id' => $dataArr['MemberId'],
                    'employee_id' => $dataArr['employee_id'],
                    'partner_ref_no' => $dataArr['PartnerRefNo'],
                    'txnrefnum' => $dataArr['TransactionRefNo'],
                    'title' => $dataArr['Title'],
                    'first_name' => $dataArr['FirstName'],
                    'middle_name' => $dataArr['MiddleName'],
                    'last_name' => $dataArr['LastName'],
                    'name_on_card' => $dataArr['NameOnCard'],
                    'gender' => $dataArr['Gender'],
                    'date_of_birth' =>$dataArr['DateOfBirth'],
                    'aadhaar_no' =>$dataArr['aadhaar_no'],
                    'pan' =>$dataArr['pan'],
                    'mobile' => $dataArr['Mobile'],
                    'mobile2' => $dataArr['Mobile2'],
                    'email' => $dataArr['Email'],
                    'landline' => $dataArr['Landline'],
                    'address_line1' => $dataArr['AddressLine1'],
                    'address_line2' => $dataArr['AddressLine2'],
                    'city' => $dataArr['City'],
                    'state' => $dataArr['State'],
                    'pincode' => $dataArr['Pincode'],
                    'country' => $dataArr['Country'],
                    'mother_maiden_name' => $dataArr['MotherMaidenName'],
                    'employer_name' => $dataArr['EmployerName'],
                    'corporate_id' => $dataArr['corporate_id'],
                    'corp_address_line1' => $dataArr['corp_address_line1'],
                    'corp_address_line2' => $dataArr['corp_address_line2'],
                    'corp_city' => $dataArr['corp_city'],
                    'corp_state' => $dataArr['corp_state'],
                    'corp_pin' => $dataArr['corp_pin'],
                    'corp_country' => $dataArr['corp_country'],
                    'id_proof_type' => $dataArr['id_proof_type'],
                    'id_proof_number' => '',
                    'address_proof_type' => '',
                    'address_proof_number' => '',
                    'occupation' => '',
                    'is_card_activated' => $dataArr['IsCardActivated'],
                    'activation_date' => $dataArr['ActivationDate'],
                    'is_card_dispatched' => $dataArr['IsCardDispatch'],
                    'card_dispatch_date' => $dataArr['CardDispatchDate'],
                    'by_ops_id' => 0,
                    'batch_id' => 0,
                    'batch_name' => '',
                    'date_created' => new Zend_Db_Expr('NOW()'),
                    'status_ops' => $dataArr['status_ops'],
                    'status' => $dataArr['status'],
                    'status_ecs' => $dataArr['status_ecs'],
		    'channel' => $dataArr['channel'],
                );
               
               
                 if(($dataArr['manageType'] == CORPORATE_MANAGE_TYPE)){
                    $dataCardholder['by_ops_id']='';
                    $dataCardholder['by_corporate_id']=$dataArr['by_api_user_id'];
                 }else{
                   $dataCardholder['by_agent_id']=$dataArr['by_api_user_id'];  
                 }
                 
                $this->insert($dataCardholder);
                $cardholderId = $this->_db->lastInsertId(DbTable::TABLE_RAT_CORP_CARDHOLDER, 'id');
             
                // Entry in Customer product table
                $prodArr = array('product_customer_id' => $cardholderId,
                    'product_id' => $dataArr['ProductId'],
                    'rat_customer_id' => $ratCustomerId,
                    'program_type' => $productDetail['program_type'],
                    'bank_id' => $dataArr['bank_id'],
                    'by_ops_id' => 0,
                    'date_created' => new Zend_Db_Expr('NOW()'),
                    'status' => STATUS_ACTIVE);
                 if(($dataArr['manageType'] == CORPORATE_MANAGE_TYPE)){
                    $prodArr['by_corporate_id']=$dataArr['by_api_user_id'];
                 }else{
                   $prodArr['by_agent_id']=$dataArr['by_api_user_id'];  
                 }
               $custProductModel->save($prodArr);
                
               $custDetailArr = array(
                    'bank_id' => $dataArr['bank_id'],
                    'rat_cardholder_id' => $cardholderId,
                    'product_id' => $dataArr['ProductId'],
                    'employer_name' => $dataArr['EmployerName'],
                    'emp_address_line1' => $dataArr['EmployerAddressLine1'],
                    'emp_address_line2' => $dataArr['EmployerAddressLine2'],
                    'emp_city' => $dataArr['EmployerCity'],
                    'emp_state' => $dataArr['EmployerState'],
                    'emp_pin' => $dataArr['EmployerPin'],
                    'date_created' => new Zend_Db_Expr('NOW()'),
                    'status' => STATUS_ACTIVE
               );
              
                $this->insertCardhoderDetail($custDetailArr);
                $dataCardholder['id'] = $cardholderId;
                $cardholderArray = $this->getCardholderArray($dataCardholder);
                
                try {
                    $ecsApi = new App_Api_ECS_Corp_Ratnakar();
//                    $resp = $ecsApi->mediAssistCardholderRegistration($cardholderArray);
                    $resp = TRUE;
                    } catch (Exception $e) {
          
                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    $msg = $ecsApi->getError();
                    $msg = empty($msg) ? $e->getMessage() : $msg ; 
                    $updateArr = array('status' => STATUS_ECS_FAILED, 'failed_reason' => $msg,
                        'date_failed' => new Zend_Db_Expr('NOW()'));
                    $this->setError($msg);
                    $this->update($updateArr, "id= $cardholderId");
                    return FALSE;
                }
                  if ($resp == false) {
                    $msg = $ecsApi->getError(); 
                    $updateArr = array('status' => STATUS_ECS_FAILED, 'failed_reason' => $msg,
                        'date_failed' => new Zend_Db_Expr('NOW()'));
                    $this->setError($msg);
                    $this->update($updateArr, "id= $cardholderId");
                    return FALSE;
                } else {
                $updateArr = array('status' => STATUS_ACTIVE, 'date_activation' => new Zend_Db_Expr('NOW()'), 'failed_reason' =>'');
                    $this->update($updateArr, "id= $cardholderId");
                    // get CRN info
                    $crnInfo = $crnMaster->getCRNInfo($dataArr['CardNumber'], $dataArr['CardPackId'], $dataArr['MemberId']);
                    if(!empty($crnInfo)){
                    // update status CRN
                    $crnMaster->updateStatusById(array('status' => STATUS_USED), $crnInfo->id);
                    }
                $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
                $cardNumberEnc = new Zend_Db_Expr("AES_ENCRYPT('".$dataArr['CardNumber']."','".$encryptionKey."')");
                $customerTrackArr =  array(
                         'card_number' => $cardNumberEnc,
                         'card_pack_id' => $dataArr['CardPackId'],
                         'partner_ref_no' => $dataArr['PartnerRefNo'],
                         'crn' => $cardNumberEnc,
                         'mobile' => $dataArr['Mobile'],
                         'email' => $dataArr['Email'],
                         'name_on_card' => $dataArr['NameOnCard'],
                         'bank_id' => $dataArr['bank_id'],
                     );
                    $customerTrackModel->customerDetails($customerTrackArr, $dataArr['ProductId'], $cardholderId);

           
            if(isset($cardholderId) && $cardholderId > 0) {
             
             $updateArr = array('status' => STATUS_ACTIVE,'txn_code' => $txnCode);  
              $response = $this->update($updateArr, "id= $cardholderId");
               
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
                   $newcustDetailID = $objCustomers->addCustomerDetails($custDetailArr);
                  
                  }
            }
        }
//            $this->_db->commit();
            $this->setTxncode($cardholderId);
            return $cardholderId;
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
          //  echo '<pre>';print_r($e);exit;
            // If any of the queries failed and threw an exception,
            // we want to roll back the whole transaction, reversing
            // changes made in the transaction, even those that succeeded.
            // Thus all changes are committed together, or none are.
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
//            $this->_db->rollBack();
            //throw new Exception ("Transaction not completed due to system failure");
            throw new Exception($e->getMessage());
        }
       
        return TRUE;
    }
    

     public function getCustomerDetails($param){
         
        $partnerRefNo = isset($param['partner_ref_no']) ? $param['partner_ref_no'] : '';
        $productID = isset($param['product_id']) ? $param['product_id'] : '';
        $mobile = isset($param['mobile']) ? $param['mobile'] : '';
        
	if($mobile == '' && $partnerRefNo == ''){
	    return FALSE;
	} else {
	    $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
	    $card_number = new Zend_Db_Expr("AES_DECRYPT(`rc`.`card_number`,'".$decryptionKey."') as card_number");
	    $select = $this->select()
		      ->from(DbTable::TABLE_RAT_CORP_CARDHOLDER ." as rc" , array('id', 'mobile', 'txn_code','customer_master_id','rat_customer_id','partner_ref_no','product_id', $card_number, 'status'));
	    if ($productID == ''){
		throw new Exception('Product code is mandatory');
	    }
	    $select->where("product_id = '" . $productID . "'");
	    if ($mobile != ''){
		$select->where("mobile = '" . $mobile . "'");
	    }
	    if ($partnerRefNo != ''){
		$select->where("partner_ref_no = '" . $partnerRefNo . "'");
	    }

	    $select->where("status IN ('".STATUS_ACTIVE."', '".STATUS_BLOCKED."')");
	    $rs = $this->fetchRow($select);  
	    if(!empty($rs)) {
		return $rs;         
	    } else {
		return FALSE;
	    }
	} 
    }

    public function checkDuplicateCardPackId($param) { 
        $card_pack_id = (isset($param['card_pack_id'])) ? $param['card_pack_id'] : ''; 
        if (isset($card_pack_id) && $card_pack_id != '') {
            $select = $this->select();
            $select->from(DbTable::TABLE_RAT_CORP_CARDHOLDER, array('id'));
            $select->where("card_pack_id =?", $card_pack_id);
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
    
    public function findById($id, $force = FALSE){
        if (!is_numeric($id)) {
            return array();
        }
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`rc`.`card_number`,'".$decryptionKey."') as card_number");
        $crn = new Zend_Db_Expr("AES_DECRYPT(`rc`.`crn`,'".$decryptionKey."') as crn");
        $select = $this->select();
        $select->from(DbTable::TABLE_RAT_CORP_CARDHOLDER ." as rc" , array(
            'id', 'bank_id', 'product_id', 'rat_customer_id', 'customer_master_id', 'customer_type', $crn, 'unicode', $card_number, 'card_pack_id', 'afn', 'medi_assist_id', 'employee_id', 'partner_ref_no', 'txnrefnum', 'title', 'first_name', 'middle_name', 'last_name', 'name_on_card', 'gender', 'date_of_birth', 'aadhaar_no', 'pan', 'mobile', 'mobile2', 'email', 'landline', 'address_line1', 'address_line2', 'city', 'state', 'pincode', 'country', 'mother_maiden_name', 'employer_name', 'corporate_id', 'corp_address_line1', 'corp_address_line2', 'corp_city', 'corp_state', 'corp_pin', 'corp_country', 'id_proof_type', 'id_proof_number', 'id_proof_doc_id', 'address_proof_type', 'address_proof_number', 'address_proof_doc_id', 'occupation', 'is_card_activated', 'activation_date', 'is_card_dispatched', 'card_dispatch_date', 'by_ops_id', 'by_corporate_id', 'by_agent_id', 'batch_id', 'batch_name', 'date_created', 'date_updated', 'date_approval', 'date_toggle_kyc', 'date_activation', 'date_blocked', 'narration', 'txn_code', 'failed_reason', 'date_failed', 'date_crn_update', 'status_ecs', 'status_ops', 'status', 'aml_status'
        ));
        $column = $this->_extractTableAlias($select) . '.' . $this->_primary[1];
        $select->where($column . ' = ?', $id);
        return $this->fetchRow($select); 
    }
    
    public function mapCardAPI($data, $crnInfo) {
        $productModel = new Products();
        $crnMaster = new CRNMaster();
        $customerTrackModel = new CustomerTrack();
        $custPurseModel = new Corp_Ratnakar_CustomerPurse();
        $refObject = new Reference();
        $m = new \App\Messaging\Corp\Ratnakar\Operation();
        $productDetail = $productModel->getProductInfo($data['product_id']);
        $apiResp = TRUE;
        $msg = '';
        try {
            $resp = true;

            if($resp) {
                $cardholderArray = $this->getMappingCardholderArray($data, $crnInfo['card_number']);                
                $ecsApi = new App_Api_ECS_Corp_Ratnakar();
                if(DEBUG_MVC) {
                $resp = TRUE;
                } else {
                $resp = $ecsApi->mediAssistCardholderRegistration($cardholderArray);
                }
                //$resp = true;
                if ($resp == false) {
                    $msg = $ecsApi->getError();
                }
            }                

            if ($resp == true) {
                // update the status to STATUS_ACTIVE in cardholders
                $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
                $cardNumber = new Zend_Db_Expr("AES_ENCRYPT('".$crnInfo['card_number']."','".$encryptionKey."')");
                $updateArr = array('status' => STATUS_ACTIVE, 'status_ecs' => STATUS_SUCCESS, 'crn' => $cardNumber, 'card_number' => $cardNumber, 'card_pack_id' => $crnInfo['card_pack_id'], 'medi_assist_id' => $crnInfo['member_id'], 'failed_reason' => '');
                $this->_db->update(DbTable::TABLE_RAT_CORP_CARDHOLDER, $updateArr, "id=". $data['id']);
                                
                $crnMaster->updateStatusByCardNumberNPackId(array(
                   'card_number'    => $crnInfo['card_number'],
                   'card_pack_id'   => $crnInfo['card_pack_id'],
                   'product_id'     => $crnInfo['product_id'],
                   'status'         => STATUS_USED
                ));
                          
                // update customer_track table for cardholder
                $customerTrackArr =  array(
                         'card_number' => $cardNumber,
                         'card_pack_id' => $crnInfo['card_pack_id'],
                         'crn' => $cardNumber
                    );
                $customerTrackModel->editCustomerDetails($customerTrackArr, $data['product_id'], $data['id'], $data['bank_id']);
                
                $userData = array('last_four' =>substr($crnInfo['card_number'], -4), 'product_name' => $productDetail['name'],
                    'mobile' => $data['mobile']
                );
                
                if($productDetail['const'] != PRODUCT_CONST_RAT_SMP) {
                    $m->cardActivation($userData);
                }
                
                $resp = array('status_map' => STATUS_SUCCESS);
                
                // Get customer general wallet balance
                $custPurse = $custPurseModel->getCustomerGenWalletBalance($data['customer_master_id'], $data['rat_customer_id'], $data['product_id']);
                
                if($custPurse['sum'] > 0) {
                    
                    // insert into t_ref table
                    $refArr = array(
                        'product_id' => $data['product_id'],
                        'val' => $custPurse['sum'],
                        'label' => TXNTYPE_FIRST_LOAD,
                        'user_id' => $data['id'],
                        'user_type' => 'RAT_CUST',
                        'method' => TXNTYPE_FIRST_LOAD,
                        'request' => TXNTYPE_FIRST_LOAD,
                        'response' => STATUS_PENDING,
                        'user_ip' => $this->formatIpAddress(Util::getIP()),
                        'date_created' => new Zend_Db_Expr('NOW()')
                    );
                    $refId = $refObject->insertData($refArr);
                    
                    $baseTxn = new BaseTxn();
                    $txnCode = $baseTxn->generateTxncode();
                    
                    $cardLoadData = array(
                                    'amount' => $custPurse['sum'],
                                    'crn' => $crnInfo['card_number'],
                                    'agentId' => $data['by_agent_id'],
				    'transactionId' => $txnCode,
				    'currencyCode' => CURRENCY_INR_CODE,
				    'countryCode' => COUNTRY_IN_CODE
				    );
                    if(DEBUG_MVC) {
                        $apiResp = TRUE;
                    } else {
                        $ecsApi = new App_Socket_ECS_Corp_Transaction();
                        $apiResp = $ecsApi->cardLoad($cardLoadData);
                    }
                    
                    if($apiResp == TRUE) {
                        $this->_db->update(DbTable::TABLE_REFERENCE, array('response' => STATUS_SUCCESS), "id=$refId");
                        $resp = array(
                            'status_map' => STATUS_SUCCESS,
                            'status_load' => STATUS_SUCCESS);
                    } else {
                        $this->_db->update(DbTable::TABLE_REFERENCE, array('exception' => $ecsApi->getError()), "id=$refId");
                        $resp = array(
                            'status_map' => STATUS_SUCCESS,
                            'status_load' => STATUS_FAILED);
                    }
                }
            } else {
                $updateArr = array('failed_reason' => $msg, 'status_ecs' => STATUS_FAILURE, 'date_failed' => new Zend_Db_Expr('NOW()'));
                $this->update($updateArr, "id=".$data['id']);
                $this->setError($msg);
            }
        } catch (App_Exception $e) {
            $msg = $e->getMessage();
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->setError($msg);
            $updateArr = array('failed_reason' => $msg, 'status_ecs' => STATUS_FAILURE, 'date_failed' => new Zend_Db_Expr('NOW()'));
            $this->update($updateArr, "id=".$data['id']);
        } catch (Exception $e) {
            $msg = $e->getMessage();
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->setError($msg);
            $updateArr = array('failed_reason' => $msg, 'status_ecs' => STATUS_FAILURE, 'date_failed' => new Zend_Db_Expr('NOW()'));
            $this->update($updateArr, "id=".$data['id']);
        }          
        return $resp;
    }
    
    public function getMappingCardholderArray($param, $card_number='') {
        $ECSModel = new ECS();
        $state = new CityList();
        $dob = Util::returnDateFormatted($param['date_of_birth'], "Y-m-d", "d-m-Y", "-");
        $cityCode = $state->getCityCode(ucfirst(strtolower($param['city'])));
        if(!empty($param['unicode'])) {
            $ECSModel->assignMediassistCRN($param['id']);
        }

        if(!empty($card_number)) {
            $paramArray['cardNumber'] = $card_number;
        } else {
            $cardholderDetails = $this->findById($param['id']);
            $cardholder = Util::toArray($cardholderDetails);

            $paramArray['cardNumber'] = $cardholder['card_number'];
        }
        
        $paramArray['address1'] = (!empty($param['address_line1'])) ? $param['address_line1'] : 'Mumbai';
        $paramArray['address2'] = $param['address_line2'];
        $paramArray['address3'] = '';
        $paramArray['address4'] = 'MAH';
        $paramArray['bankcode'] = '';
        $paramArray['birthcity'] = '';
        $paramArray['birthcountry'] = '';
        $paramArray['birthdate'] = (!empty($dob)) ? preg_replace('/-|:/', null, $dob) : '01011970';
        $paramArray['citycode'] = (!empty($cityCode)) ? $cityCode : 'MUM';
        $paramArray['countrycode'] = COUNTRY_IN_CODE;
        $paramArray['emailid'] = $param['email'];
        $paramArray['embossedname'] = $param['name_on_card'];
        $paramArray['employer'] = '';
        $paramArray['employmentstatus'] = '';
        $paramArray['familyname'] = (!empty($param['last_name'])) ? $param['last_name'] : $param['first_name'];
        $paramArray['firstname'] = $param['first_name'];
        
        $paramArray['legalid'] = (isset($param['medi_assist_id']) && !empty($param['medi_assist_id'])) ? $param['medi_assist_id'] : '';
        $paramArray['mothersmaidenname'] = (!empty($param['mother_maiden_name'])) ? $param['mother_maiden_name'] : 'Mother';
        $paramArray['phonemobile'] = $param['mobile'];
        $paramArray['zipcode'] = (!empty($param['pincode'])) ? $param['pincode'] : '400000';
        if(isset($param['gender']) && in_array(strtolower($param['gender']), array('male','female'))) {
            if(strtolower($param['gender']) == 'male') $param['gender'] = 'M';
            if(strtolower($param['gender']) == 'female') $param['gender'] = 'F';
        }
            
        $paramArray['gender'] = !empty($param['gender']) ? $param['gender'] : 'M';

        return $paramArray;
    }
    
    public function byAgentCardload($param) {
        $baseTxn = new BaseTxn();
        $bankObject = new Banks();
        $prdObj = new Products(); 
        $obj = new Corp_Ratnakar_Cardload();
            
        //Generate TXNCode 
        $txnCode = $baseTxn->generateTxncode();
        //Get the bank Id 
        $bankInfo = $bankObject->getBankidByProductid($param['product_id']);
        //Get general Wallet
        $productWallet = App_DI_Definition_BankProduct::getInstance($param['bank_product_const']); 
        $genwalletCode = $productWallet->purse->code->genwallet;
            
        $params['cardholder_id'] = (string) trim($param['ch_id']);
        $params['product_id'] =(string) trim($param['product_id']);
        $params['amount'] = (string) trim($param['amount']); 
        $params['mode'] = TXN_MODE_CR;
        $params['bank_id'] = $bankInfo['bank_id'];  
        $params['corporate_id'] = 0;
        $params['by_api_user_id'] = (string) trim($param['agent_id']);
        $params['manageType'] = AGENT_MANAGE_TYPE;
        $params['txn_code'] = (string) trim($txnCode);
        $params['txn_no'] = (string) trim($txnCode);
        $params['bank_product_const'] = $param['bank_product_const']; 
        $params['txn_identifier_type'] = TXN_IDENTIFIER_TYPE_MOBILE;//mob  
        $params['txn_identifier_num'] = (string) trim($param['ch_mobile']); 
        $params['wallet_code'] = $genwalletCode;
        $params['narration'] = '';
        $params['card_type']= FLAG_N; 
        $params['sms_from_agent']= FLAG_Y; 
        $params['sms_flag']= FLAG_N;
        $params['channel']= $param['channel'];
        
        $flg = $obj->doCardloadAPI($params);
        return $flg;
    }
    
    public function getRatCardholderPursesDetail($param) { 
        $customer_id = isset($param['customer_id']) ? $param['customer_id'] : ''; 
        $rat_customer_id = isset($param['rat_customer_id']) ? $param['rat_customer_id'] : '';
        $wallet_code = isset($param['wallet_code']) ? strtoupper($param['wallet_code']) : '';
        $product_id = isset($param['product_id']) ? strtoupper($param['product_id']) : '';
        
        $status = isset($param['status']) ? $param['status'] : ''; 
        
        $select = $this->select()
                ->setIntegrityCheck(false)
                ->from(DbTable::TABLE_RAT_CORP_CARDHOLDER . " as rcc", array('mobile','customer_master_id','rat_customer_id'))
                ->joinLeft(DbTable::TABLE_RAT_CUSTOMER_PURSE . ' as cp', "cp.rat_customer_id = rcc.rat_customer_id", array('id as customer_purse_id'))
                ->joinLeft(DbTable::TABLE_PURSE_MASTER . ' as p', "p.id = cp.purse_master_id", array('id as purse_master_id','bank_id','product_id'))
                ->where('p.id = cp.purse_master_id')
                ->where('cp.rat_customer_id >0');
        
        if($customer_id !=''){
            $select->where('rcc.id =?', $customer_id);
        }
                
        if($rat_customer_id!=''){
            $select->where('rcc.rat_customer_id =?', $rat_customer_id);
        }

        if($wallet_code!=''){
            $select->where('p.code =?', $wallet_code);
        }
        if($product_id !=''){
            $select->where('p.product_id =?', $product_id);
        }
        if($status !=''){
            $select->where('rcc.status =?', $status);
        } 
        return $this->_db->fetchRow($select); 
    }
    
    public function checkCardholder($param) {
        $product_id = isset($param['product_id']) ? $param['product_id'] : '';
        $mobile_number = isset($param['mobile_number']) ? $param['mobile_number'] : '';

        if ($mobile_number != '' && $product_id != '') {
            $sql = $this->_db->select();
            $sql->from(DbTable::TABLE_RAT_CORP_CARDHOLDER ." AS c", array(
                'id', 'mobile', 'concat(first_name," ",last_name) as name', 'email',
                'product_id','rat_customer_id','customer_master_id',
            ));
            $sql->joinLeft(DbTable::TABLE_PRODUCTS." as p", "c.product_id = p.id", array('p.name AS product_name')); 
            $sql->where('c.product_id = ?', $product_id);
            $sql->where('c.mobile = ?', $mobile_number);
            $sql->where('c.status = ?', STATUS_ACTIVE);
            return $this->_db->fetchRow($sql);
        } else {
            return array();
        }
    }
    
    public function addMapperRemitterAPI($params) { 
        $objectRelation = new ObjectRelations(); 
        $objRelationTypeId = $objectRelation->getRelationTypeId(RAT_MAPPER);
        $cardholderID = isset($params['cardholder_id']) && $params['cardholder_id'] > 0 ? $params['cardholder_id'] : '';
        $remitterID = isset($params['remitter_id']) && $params['remitter_id'] > 0 ? $params['remitter_id'] : '';
         try{
            if(($cardholderID == '') || ($remitterID == '') ){
                throw('Cardholder / Remitter Id is not provided.');
            } 
            
            $sqlCheck = $this->_db->select();
            $sqlCheck->from(DbTable::TABLE_BIND_OBJECT_RELATION, array('id'));
            $sqlCheck->where('to_object_id = ?', $remitterID);
            $sqlCheck->where('object_relation_type_id = ?', $objRelationTypeId);
            $rows =$this->_db->fetchRow($sqlCheck); 
            if($rows){
                $UpArr = array('from_object_id'=>$cardholderID);
                $id = $rows['id'];
                $this->_db->update(DbTable::TABLE_BIND_OBJECT_RELATION, $UpArr, "id = $id"); 
            } else {
                $relationData =  array(
                    'from_object_id'    =>  $cardholderID,
                    'to_object_id'      =>  $remitterID,
                    'object_relation_type_id'   =>  $objRelationTypeId
                ); 
                $objectRelation->insert($relationData);
             }
             return true;
        } catch (Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR); 
            throw new Exception($e->getMessage());
        }
     }
     
     public function checkCardholderByMobile($params){
        $productId = (isset($params['product_id'])) ? $params['product_id'] : '';
        $select = $this->select();
        $select->from($this->_name, array('id', 'txn_code'));
        if($productId){
        $select->where('product_id = ?', $params['product_id']);
        }
        $select->where('mobile = ?', $params['mobile']);  
       // $select->where('status = ?', STATUS_ACTIVE); 
        $select->where("status IN ('".STATUS_ACTIVE."', '".STATUS_BLOCKED."')");
        $rs = $this->fetchRow($select);
        if( !empty($rs) ){
            return TRUE;
           
        }
        else
            return FALSE;
   }
   
   public function cardloadByAgent($param) {
       
        $baseTxn = new BaseTxn();
        $bankObject = new Banks();
        $prdObj = new Products(); 
        $obj = new Corp_Ratnakar_Cardload();
            
        //Generate TXNCode 
        $txnCode = $baseTxn->generateTxncode();
        //Get the bank Id 
        $bankInfo = $bankObject->getBankidByProductid($param['product_id']);
        //Get general Wallet
        $productWallet = App_DI_Definition_BankProduct::getInstance($param['bank_product_const']); 
        $genwalletCode = $productWallet->purse->code->genwallet;
        
        $productID = $param['product_id'];
        $agentID = $param['agent_id'];
        $amountPaisa = $param['amount'];
        /*=================================
        
                    /*
                     * Agent Load Fee Plan
                     */
                    $cardloadFee = 0;
                    $cardloadServiceTax = 0;
                    $productID = $productID;
                    $feeplan = new FeePlan();
                    $feeArr = $feeplan->getRemitterFee($productID, $agentID);
                    if (!empty($feeArr)) {
                    $cardloadAmount = $param['amount']; 
                    $cardloadAmount = Util::convertToRupee($cardloadAmount);
        
                    $feeAmount = '0.00';
                        //Fees Check
                        
                        foreach ($feeArr as $val) {
                            if ($val['typecode'] == TXNTYPE_CARD_RELOAD) {
                                $val['amount'] = $cardloadAmount;
                                $val['return_type'] = TYPE_FEE;
                                $feeAmount = Util::calculateRoundedFee($val);
                              //  App_Logger::log($fee, Zend_Log::ERR);
                                break;
                            }
                        }
                        
                        $feeComponent = Util::getFeeComponents($feeAmount);
                        
                        $cardloadFee = isset($feeComponent['partialFee']) ? $feeComponent['partialFee'] : 0;
                        $cardloadServiceTax = isset($feeComponent['serviceTax']) ? $feeComponent['serviceTax'] : 0;
                      
                    } 
        
        /*============*/
        
        $params['cardholder_id'] = (string) trim($param['ch_id']);
        $params['product_id'] =$productID;
        $params['amount'] = $amountPaisa; 
        $params['mode'] = TXN_MODE_CR;
        $params['bank_id'] = $bankInfo['bank_id'];  
        $params['corporate_id'] = 0;
        $params['by_api_user_id'] = $agentID;
        $params['manageType'] = AGENT_MANAGE_TYPE;
        $params['txn_code'] = (string) trim($txnCode);
        $params['txn_no'] = (string) trim($txnCode);
        $params['bank_product_const'] = $param['bank_product_const']; 
        $params['txn_identifier_type'] = TXN_IDENTIFIER_TYPE_MOBILE;//mob  
        $params['txn_identifier_num'] = (string) trim($param['ch_mobile']); 
        $params['wallet_code'] = $genwalletCode;
        $params['narration'] = '';
        $params['card_type']= FLAG_N; 
        $params['sms_from_agent']= FLAG_Y; 
        $params['sms_flag']= FLAG_N;
        $params['channel']= $param['channel'];
        $params['fee'] = $cardloadFee;
        $params['service_tax'] = $cardloadServiceTax;
        $params['voucher_num'] = '';
        $params['date_expiry'] = '';
        $params['Filler1'] = '';
        $flg = $obj->doCardloadByAgent($params);
        return $flg;
    }

   public function addCorpCustomer($dataArr = array()) {
        //echo '<pre>';print_r($dataArr);exit;
        if (empty($dataArr)) {
            throw new Exception('Data missing for add cardholder');
        }
        elseif (empty($dataArr['ProductId'])) {
            throw new Exception('Product missing for add cardholder');
        }
        $custProductModel = new Corp_Ratnakar_CustomerProduct();
        $custPurseModel = new Corp_Ratnakar_CustomerPurse();
        $masterPurseModel = new MasterPurse();
        $objCustomerMaster = new CustomerMaster();
        $customerTrackModel = new CustomerTrack();
        $bankModel = new Banks();
        $productModel = new Products();
        $crnMaster = new CRNMaster();
        $str = '';
        try {
//            $this->_db->beginTransaction();
            
            $paramChk = array(
                'mobile' => $dataArr['Mobile'],
                'medi_assist_id' => $dataArr['MemberId'],
                'card_number' => $dataArr['CardNumber'],
                'product_id' => $dataArr['ProductId']
            );
            $check = $this->checkCardholderDuplication($paramChk);
            if (!$check) {
                $this->setError("Duplicate Record");
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
                    'aadhaar_no' => (isset($dataArr['IdentityProofType']) && strtolower($dataArr['IdentityProofType']) == 'aadhar card') ? $dataArr['IdentityProofDetail'] : '',
                    'pan' => (isset($dataArr['IdentityProofType']) && strtolower($dataArr['IdentityProofType']) == 'pan card') ? $dataArr['IdentityProofDetail'] : '',
                    'mobile_country_code' => isset($dataArr['mobile_country_code']) ? $dataArr['mobile_country_code'] : '',
                    'mobile' => $dataArr['Mobile'],
                    'email' => $dataArr['Email'],
                    'gender' => $dataArr['Gender'],
                    'date_of_birth' => isset($dataArr['DateOfBirth']) ? $dataArr['DateOfBirth'] : '',
                    'status' => STATUS_ACTIVE,
                );
                
                $ratCustomerId = $this->addRatCustomerMaster($ratCustomerMasterData);
                //echo 'ratCustomerId=>'.$ratCustomerId; exit;
                //insert into customer purse
                $purseDetails = $masterPurseModel->getPurseDetailsbyBankIdProductId($dataArr['ProductId'], $productDetail['bank_id']);
                foreach ($purseDetails as $purseDetail) {
                    $purseArr = array(
                        'customer_master_id' => $customerMasterId,
                        'rat_customer_id' => $ratCustomerId,
                        'product_id' => $dataArr['ProductId'],
                        'purse_master_id' => $purseDetail['id'],
                        'bank_id' => $productDetail['bank_id'],
                        //'by_ops_id' => $user->id,
                        'date_updated' => new Zend_Db_Expr('NOW()')
                    );
                    $purseParam = array('rat_customer_id' => $ratCustomerId, 'purse_master_id' => $purseDetail['id']);
                    $purseDetails = $custPurseModel->getCustPurseDetails($purseParam);
                    if (empty($purseDetails)) { // If purse entry not found
                        $custPurseModel->save($purseArr);
                    }
                }
                
                $dataCardholder = array(
                    'bank_id' => $productDetail['bank_id'] ,
                    'product_id' => $dataArr['ProductId'],
                    'rat_customer_id' => $ratCustomerId,
                    'customer_master_id' => $customerMasterId,
                    'customer_type' => $dataArr['customer_type'],
                    'afn' => $dataArr['afn'],
                    'crn' => $dataArr['CardNumber'],
                    'card_number' => $dataArr['CardNumber'],
                    'card_pack_id' => $dataArr['CardPackId'],
                    'medi_assist_id' => $dataArr['MemberId'],
                    'employee_id' => $dataArr['employee_id'],
                    'title' => $dataArr['Title'],
                    'first_name' => $dataArr['FirstName'],
                    'middle_name' => $dataArr['MiddleName'],
                    'last_name' => $dataArr['LastName'],
                    'name_on_card' => $dataArr['NameOnCard'],
                    'gender' => $dataArr['Gender'],
                    'date_of_birth' =>$dataArr['DateOfBirth'],
                    'mobile' => $dataArr['Mobile'],
                    'mobile2' => $dataArr['Mobile2'],
                    'email' => $dataArr['Email'],
                    'landline' => $dataArr['Landline'],
                    'address_line1' => $dataArr['AddressLine1'],
                    'address_line2' => $dataArr['AddressLine2'],
                    'city' => $dataArr['City'],
                    'state' => $dataArr['State'],
                    'pincode' => $dataArr['Pincode'],
                    'country' => $dataArr['Country'],
                    'mother_maiden_name' => $dataArr['MotherMaidenName'],
                    'employer_name' => $dataArr['EmployerName'],
                    'corporate_id' => $dataArr['corporate_id'],
                    'corp_address_line1' => $dataArr['corp_address_line1'],
                    'corp_address_line2' => $dataArr['corp_address_line2'],
                    'corp_city' => $dataArr['corp_city'],
                    'corp_state' => $dataArr['corp_state'],
                    'corp_pin' => $dataArr['corp_pin'],
                    'corp_country' => $dataArr['corp_country'],
                    'id_proof_type' => $dataArr['id_proof_type'],
                    'id_proof_number' => $dataArr['id_proof_number'],
                    'address_proof_type' => $dataArr['address_proof_type'],
                    'address_proof_number' => $dataArr['address_proof_number'],
                    'occupation' => $dataArr['Occupation'],
                    'is_card_activated' => $dataArr['IsCardActivated'],
                    'activation_date' => $dataArr['ActivationDate'],
                    'is_card_dispatched' => $dataArr['IsCardDispatch'],
                    'card_dispatch_date' => $dataArr['CardDispatchDate'],
                    'by_agent_id' => $dataArr['by_api_user_id'],
                    'by_ops_id' => 0,
                    'by_corporate_id' => $dataArr['by_corporate_id'],
                    'batch_id' => 0,
                    'batch_name' => '',
                    'date_created' => new Zend_Db_Expr('NOW()'),
                    'status_ops' => $dataArr['status_ops'],
                    'status' => STATUS_ECS_PENDING,
                    'status_ecs' => STATUS_WAITING,
		    'channel' => $dataArr['channel'],
                );
                 if(!empty($user->corporate_code)){
                    $dataCardholder['by_ops_id']='';
                    $dataCardholder['by_corporate_id']=$user->id;
                 }
             
                $this->insert($dataCardholder);
                $cardholderId = $this->_db->lastInsertId(DbTable::TABLE_RAT_CORP_CARDHOLDER, 'id');

                // Entry in Customer product table
                $prodArr = array('product_customer_id' => $cardholderId,
                    'product_id' => $dataArr['ProductId'],
                    'rat_customer_id' => $ratCustomerId,
                    'program_type' => $productDetail['program_type'],
                    'bank_id' => $productDetail['bank_id'],
                    'by_agent_id' => $dataArr['by_api_user_id'],
                    'by_ops_id' => 0,
                    'by_corporate_id' => $dataArr['corporate_id'],
                    'date_created' => new Zend_Db_Expr('NOW()'),
                    'status' => STATUS_ACTIVE);
                $custProductModel->save($prodArr);

               $custDetailArr = array(
                    'rat_cardholder_id' => $cardholderId,
                    'product_id' => $dataArr['ProductId'],
                    'employer_name' => $dataArr['EmployerName'],
                    'emp_address_line1' => $dataArr['EmployerAddressLine1'],
                    'emp_address_line2' => $dataArr['EmployerAddressLine2'],
                    'emp_city' => $dataArr['EmployerCity'],
                    'emp_state' => $dataArr['EmployerState'],
                    'emp_pin' => $dataArr['EmployerPin'],
                    'date_created' => new Zend_Db_Expr('NOW()'),
                    'status' => STATUS_ACTIVE
               );
               
                $this->insertCardhoderDetail($custDetailArr);
                $dataCardholder['id'] = $cardholderId;
                $cardholderArray = $this->getCardholderArray($dataCardholder);
              
                // get CRN info
                    $crnInfo = $crnMaster->getCRNInfo($dataArr['CardNumber'], $dataArr['CardPackId'], $dataArr['MemberId']);
                    if(!empty($crnInfo)){
                    // update status CRN
                    $crnMaster->updateStatusById(array('status' => STATUS_USED), $crnInfo->id);
                    }
                
                
            } 
                    
            $this->_db->commit();
            $this->setTxncode($cardholderId);
            return $cardholderId;
            //echo '<pre>vijay';print_r($cardholderId);exit;
        } catch (Exception $e) {
           App_Logger::log($e->getMessage(), Zend_Log::ERR);            
            $msg = $e->getMessage();
            //print_r($msg); exit;
            if(isset($cardholderId) && $cardholderId > 0) {
                $updateArr = array('status' => STATUS_ECS_FAILED, 'failed_reason' => $msg,
                    'date_failed' => new Zend_Db_Expr('NOW()'));
                $this->setError($msg);
                $this->update($updateArr, "id= $cardholderId");
            }
            return FALSE;
          //  echo '<pre>';print_r($e);exit;
            // If any of the queries failed and threw an exception,
            // we want to roll back the whole transaction, reversing
            // changes made in the transaction, even those that succeeded.
            // Thus all changes are committed together, or none are.
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
//            $this->_db->rollBack();
            //throw new Exception ("Transaction not completed due to system failure");
            throw new Exception($e->getMessage());
        }
       
        return TRUE;
    }    

}
