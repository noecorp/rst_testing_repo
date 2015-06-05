<?php

/**
 * Model that manages the products
 *
 * @package Operation_Models
 * @copyright transerv
 */
class Corp_Boi_DeliveryFlag extends Corp_Boi {

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
    protected $_name = DbTable::TABLE_BOI_DELIVERY_FLAG_MASTER;

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
    
   public function insertDeliveryFile($dataArr) {
       $data = array(
           
           'sol_id' => $dataArr[0],
           'title' => $dataArr[1],
           'name' => $dataArr[2],
           'occupation' => $dataArr[3],
           'gender' => $dataArr[4],
           'date_of_birth' => $dataArr[5],
           'address_type' => $dataArr[6],
           'address_line1' => $dataArr[7],
           'address_line2' => $dataArr[8],
           'city' => $dataArr[9],
           'state' => $dataArr[10],
           'country_code' => $dataArr[11],
           'pincode' => $dataArr[12],
           'comm_address_line1' => $dataArr[13],
           'comm_address_line2' => $dataArr[14],
           'comm_city' => $dataArr[15],
           'comm_state' => $dataArr[16],
           'comm_country_code' => $dataArr[17],
           'comm_pin' => $dataArr[18],
           'landline' => $dataArr[19],
           'mobile' => $dataArr[20],
           'email' => $dataArr[21],
           'pan' => $dataArr[22],
           'uid_no' => $dataArr[23],
           'nre_flag' => $dataArr[24],
           'nre_nationality' => $dataArr[25],
           'passport' => $dataArr[26],
           'passport_issue_date' => $dataArr[27],
           'passport_expiry_date' => $dataArr[28],
           'marital_status' => $dataArr[29],
           'cust_comm_code' => $dataArr[30],
           'other_bank_account_no' => $dataArr[31],
           'other_bank_account_type' => $dataArr[32],
           'other_bank_name' => $dataArr[33],
           'other_bank_branch' => $dataArr[34],
           'employer_name' => $dataArr[35],
           'employer_address_line1' => $dataArr[36],
           'employer_address_line2' => $dataArr[37],
           'employer_address_city' => $dataArr[38],
           'employer_address_state' => $dataArr[39],
           'employer_address_country_code' => $dataArr[40],
           'employer_address_pincode' => $dataArr[41],
           'employer_contact_no' => $dataArr[42],
           'minor_flg' => $dataArr[43],
           'minor_guardian_code' => $dataArr[44],
           'minor_guardian_name' => $dataArr[45],
           'minor_guardian_address_line1' => $dataArr[46],
           'minor_guardian_address_line2' => $dataArr[47],
           'minor_guardian_city' => $dataArr[48],
           'minor_guardian_state' => $dataArr[49],
           'minor_guardian_pincode' => $dataArr[50],
           'minor_guardian_country_code' => $dataArr[51],
           'mode_of_operation' => $dataArr[52],
           'nomination_flg' => $dataArr[53],
           'nominee_name' => $dataArr[54],
           'nominee_add_line1' => $dataArr[55],
           'nominee_add_line2' => $dataArr[56],
           'nominee_relationship' => $dataArr[57],
           'nominee_city_cd' => $dataArr[58],
           'nominee_minor_guradian_cd' => $dataArr[59],
           'nominee_dob' => $dataArr[60],
           'nominee_minor_flag' => $dataArr[61],
           'amount_open' => $dataArr[62],
           'mode_of_payment_open' => $dataArr[63],
           'account_no' => $dataArr[64],
           'member_id' => $dataArr[64],
           'cust_id' => $dataArr[65],
           'sqlid' => $dataArr[66],
           'finacle_status' => $dataArr[67],
           'update_sql_status' => $dataArr[68],
           'staff_flg' => $dataArr[69],
           'staff_no' => $dataArr[70],
           'minor_title_guradian_code' => $dataArr[71],
           'passport_details' => $dataArr[72],
           'introducer_title_code' => $dataArr[73],
           'introducer_name' => $dataArr[74],
           'existing_cust_flg' => $dataArr[75],
           'account_currency_code' => $dataArr[76],
           'cust_id_ver_flg' => $dataArr[77],
           'account_id_ver_flg' => $dataArr[78],
           'schm_code' => $dataArr[79],
           'orgaization_type' => $dataArr[80],
           'introducer_flg' => $dataArr[81],
           'introducer_cust_id' => $dataArr[82],
           'cust_currency_code' => $dataArr[83],
           'date_created' => $dataArr[84],
           'account_type_id' => $dataArr[85],
           'ref_num' => $dataArr[86],
           'debit_mandate_amount' => $dataArr[87],
           'batch_name' => $dataArr['batch_name'],
           'product_id' => $dataArr['product_id'],
           
        );
        $this->save($data);

        return TRUE;
    }
    
       public function checkBatchName($batchName, $productId = 0) {
      
         $select = $this->select()
                ->where("batch_name = ?",$batchName);
         if($productId > 0) {
             $select->where("product_id = ?",$productId);
         }
        $res = $this->fetchRow($select);
        if(!isset($res) && empty($res)){
           return TRUE; 
        }
        else
        {
            return FALSE;
        }
    }
    
     public function getBatchName() {
      
         $select = $this->select()
                   ->distinct(TRUE)
                   ->from(DbTable::TABLE_BOI_DELIVERY_FLAG_MASTER,array('batch_name'))
                   ->order("date_created DESC");
        $res = $this->fetchAll($select);
        
        $dataArray = array();
        $dataArray[''] = 'Select File Name';
        foreach ($res as $val) {
            $dataArray[$val['batch_name']] = $val['batch_name'];
        }
        return $dataArray;   
        
    }
    
    
     public function findByBatchName ($page = 1,$batchName,$paginate = NULL, $force = FALSE){
        
       $details = $this->select()
                ->setIntegrityCheck(false) 
               ->from(DbTable::TABLE_BOI_DELIVERY_FLAG_MASTER .' as df')
               ->joinLeft(DbTable::TABLE_PRODUCTS .' as p',"df.product_id = p.id",array('name'))
               ->where("batch_name ='".$batchName."'");
      return $this->_paginate($details, $page, $paginate);
        
        
    }
    
    
    
    /* exportfindByBatchName function will find data for batch name
    */
    public function exportfindByBatchName($param){ 
        $batchName = $param['batchname'];
        $details = $this->select()
                ->setIntegrityCheck(false) 
               ->from(DbTable::TABLE_BOI_DELIVERY_FLAG_MASTER .' as df')
               ->joinLeft(DbTable::TABLE_PRODUCTS .' as p',"df.product_id = p.id",array('name'))
               ->where("batch_name ='".$batchName."'");
        $data = $this->fetchAll($details);
                
        $retData = array();
        
        if(!empty($data))
        { 
            foreach($data as $key=>$data){
    
                $retData[$key]['name']          = $data['name'];
                $retData[$key]['member_id']          = $data['member_id'];
                $retData[$key]['card_number']          = $data['card_number'];
                $retData[$key]['card_pack_id']          = $data['card_pack_id'];
                $retData[$key]['date_created']          = $data['date_created'];
                $retData[$key]['delivery_status']          = $data['delivery_status'];
                $retData[$key]['date_ecs']          = $data['date_ecs'];
                $retData[$key]['failed_reason']          = $data['failed_reason'];
                $retData[$key]['status']          = $data['status'];
                
   
          }
        }
        
        return $retData;
    }
    
    
    
/*    
    public function getDeliveryStatus ($page = 1,$data,$paginate = NULL, $force = FALSE){
      
                 
                $details = $this->getDeliveryStatusSql($data);
                    
                return $this->_paginate($details, $page, $paginate);
        
        
    }*/

public function getDeliveryStatus ($page = 1,$data,$paginate = NULL, $force = FALSE){
      
                 
                $details = $this->getDeliveryStatusSql($data);
                    
                return $this->_paginate($details, $page, $paginate);
        
        
    }
    
    private function getDeliveryStatusSql($data) {
         //Enable DB Slave
         $this->_enableDbSlave();
         $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
         $card_number = new Zend_Db_Expr("AES_DECRYPT(`df`.`card_number`,'".$decryptionKey."') as card_number");
        
        $from_date = isset($data['from']) ? $data['from'] : '';
        $to_date = isset($data['to']) ? $data['to'] : '';
        $batchname = isset($data['batchname']) ? $data['batchname'] : '';

        //$details = $this->select()
        $details = $this->select();
        $details->setIntegrityCheck(false); 
        $details->from(DbTable::TABLE_BOI_DELIVERY_FLAG_MASTER .' as df',array('id', 'product_id', 'nsdc_enrollment_no', 'sol_id', 'title', 'name', 'occupation', 'gender', 'date_of_birth', 'address_type', 'address_line1', 'address_line2', 'city', 'state', 'pincode', 'country_code', 'comm_address_line1', 'comm_address_line2', 'comm_city', 'comm_pin', 'comm_state', 'comm_country_code', 'landline', 'mobile', 'email', 'pan', 'uid_no', 'nre_flag', 'nre_nationality', 'passport', 'passport_issue_date', 'passport_expiry_date', 'marital_status', 'cust_comm_code', 'other_bank_account_no', 'other_bank_account_type', 'other_bank_name', 'other_bank_branch', 'employer_name', 'employer_address_line1', 'employer_address_line2', 'employer_address_city', 'employer_address_state', 'employer_address_country_code', 'employer_address_pincode', 'employer_contact_no', 'minor_flg', 'minor_guardian_code', 'minor_guardian_name', 'minor_guardian_address_line1', 'minor_guardian_address_line2', 'minor_guardian_city', 'minor_guardian_state', 'minor_guardian_pincode', 'minor_guardian_country_code', 'mode_of_operation', 'nomination_flg', 'nominee_name', 'nominee_relationship', 'nominee_add_line1', 'nominee_add_line2', 'nominee_city_cd', 'nominee_minor_guradian_cd', 'nominee_dob', 'nominee_minor_flag', 'amount_open', 'mode_of_payment_open', 'account_no', 'cust_id', 'sqlid', 'finacle_status', 'update_sql_status', 'staff_flg', 'staff_no', 'minor_title_guradian_code', 'passport_details', 'introducer_title_code', 'introducer_code', 'introducer_name', 'existing_cust_flg', 'account_currency_code', 'cust_id_ver_flg', 'account_id_ver_flg', 'schm_code', 'orgaization_type', 'introducer_flg', 'introducer_cust_id', 'cust_currency_code', 'account_type_id', 'ref_num', $card_number, 'card_pack_id', 'debit_mandate_account', 'debit_mandate_amount', 'boi_account_number', 'boi_customer_id', 'member_id', 'date_created', 'delivery_date', 'delivery_status', 'batch_name', 'date_ecs', 'failed_reason', 'status'));
        $details->joinLeft(DbTable::TABLE_PRODUCTS .' as p',"df.product_id = p.id",array('name'));

         if ($from_date != '') {
             //$details->where("df.date_created >= '" . $data['from_date'] . "'");
             $details->where("STR_TO_DATE(df.date_created, '%d-%m-%Y') >= '" . $data['from'] . "'");
         }
         if ($to_date != '') {
             $details->where("STR_TO_DATE(df.date_created, '%d-%m-%Y') <= '" . $data['to'] . "'");
         }
         if ($batchname != '') {
             $details->where("batch_name ='".$data['batchname']."'");
         } 
         //Disable DB Slave
         $this->_disableDbSlave();
         return $details;
    }
    
    public function exportDeliveryStatus($param,$page = 1){ 
        
        /*
        $batchName = $param['batchname'];
        $details = $this->select()
                ->setIntegrityCheck(false) 
               ->from(DbTable::TABLE_BOI_DELIVERY_FLAG_MASTER .' as df')
               ->joinLeft(DbTable::TABLE_PRODUCTS .' as p',"df.product_id = p.id",array('name'))
               ->where("batch_name ='".$batchName."'");
        $data = $this->fetchAll($details);
         */       
        
        $details = $this->getDeliveryStatusSql($param);
        $data = $this->fetchAll($details);
        $retData = array();
        
        if(!empty($data))
        { 
            foreach($data as $key=>$data){
    
                $retData[$key]['name']          = $data['name'];
                $retData[$key]['member_id']          = $data['member_id'];
                $retData[$key]['card_number']          = $data['card_number'];
                $retData[$key]['card_pack_id']          = $data['card_pack_id'];
                $retData[$key]['date_created']          = $data['date_created'];
                $retData[$key]['delivery_status']          = $data['delivery_status'];
                $retData[$key]['date_ecs']          = $data['date_ecs'];
                $retData[$key]['failed_reason']          = $data['failed_reason'];
                $retData[$key]['status']          = $data['status'];
                
   
          }
        }
        
        return $retData;
    }
    
    
    
    public function getPendingRecords($limit = ''){
        
        $select = $this->_db->select();
       
        $select->from(DbTable::TABLE_BOI_DELIVERY_FLAG_MASTER .' as df');
                $select->where("df.status = '". STATUS_PENDING."'")
                ->order('df.id');
        if($limit != ''){
            $select->limit($limit);
        }
       
        return $this->_db->fetchAll($select);
    }
    
      public function updateRecord($param){
       $ref_num = isset($param['ref_num']) ? $param['ref_num'] : ''; 
       $reason = isset($param['failed_reason']) ? $param['failed_reason'] : ''; 
       $status = isset($param['status']) ? $param['status'] : ''; 
       $id = isset($param['id']) ? $param['id'] : 0; 
       
       $data = array('failed_reason' => $reason,'status' => $status);
       $this->update($data,"id = $id");
        
    
    }
}
