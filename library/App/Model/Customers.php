<?php
/**
 * Model that manages the customer master
 *
 * @copyright transerv
 */

class Customers extends BaseUser
{
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
    protected $_name = DbTable::TABLE_CUSTOMERS;
    
    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    
    protected $_CRN;    
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
   
   public function addCustomers($data){
       if(empty($data))
           throw new Exception(ErrorCodes::ERROR_INVALID_DATA_ADD_CUST_FAILURE_MSG, ErrorCodes::ERROR_INVALID_DATA_ADD_CUST_FAILURE_CODE); 
       
           $this->_db->insert(DbTable::TABLE_CUSTOMERS,$data);
           return $this->_db->lastInsertId(DbTable::TABLE_CUSTOMERS, 'id');
        
   }
   
   public function checkCustomer($params) {
        $mobile = isset($params['mobile']) ? $params['mobile'] : '';
        $bankID = isset($params['bank_id']) ? $params['bank_id'] : '';
       
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_CUSTOMERS, array('id','customer_type'));
       if ($mobile != ''){
            $select->where("mobile = '" . $mobile . "'");
        }
        if ($bankID != ''){
            $select->where("bank_id = '" . $bankID . "'");
        }
        $rs = $this->_db->fetchRow($select);
       
         if(!empty($rs)) {
            return $rs;
        }
        return FALSE;
        
    }
    
    public function addCustomerDetails($data){
       if(empty($data))
           throw new Exception(ErrorCodes::ERROR_INVALID_DATA_ADD_CUST_FAILURE_MSG, ErrorCodes::ERROR_INVALID_DATA_ADD_CUST_FAILURE_CODE); 
       
           $this->_db->insert(DbTable::TABLE_CUSTOMERS_DETAIL,$data);
           return $this->_db->lastInsertId(DbTable::TABLE_CUSTOMERS_DETAIL, 'id');
        
   }
   
   public function checkCustomerDetails($params) {
       
        $productID = isset($params['product_id']) ? $params['product_id'] : '';
        $bankID = isset($params['bank_id']) ? $params['bank_id'] : '';
        $bankCustID = isset($params['bank_customer_id']) ? $params['bank_customer_id'] : '';
        $proCustID = isset($params['product_customer_id']) ? $params['product_customer_id'] : '';
        if( ($productID == '') || ($bankCustID == '') || ($proCustID == '') ){
          return FALSE;   
        }
        
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_CUSTOMERS_DETAIL, array('customer_id','bank_id','product_id','bank_customer_id','product_customer_id'));
       if ($productID != ''){
            $select->where("product_id = '" . $productID . "'");
        }
        if ($bankID != ''){
            $select->where("bank_id = '" . $bankID . "'");
        }
        if ($bankCustID != ''){
            $select->where("bank_customer_id = '" . $bankCustID . "'");
        }
        if ($proCustID != ''){
            $select->where("product_customer_id = '" . $proCustID . "'");
        }
      
        $rs = $this->_db->fetchRow($select);
       
         if(!empty($rs)) {
            return $rs;
        }
        return FALSE;
        
    }
    
    public function getBankCustomerDetails($params) {
       
        $custID = isset($params['customer_id']) ? $params['customer_id'] : '';
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_CUSTOMERS_DETAIL, array('bank_id','product_id','bank_customer_id','product_customer_id'));
       
        $select->where("customer_id = '" . $custID . "'");
        $rs = $this->_db->fetchAll($select);
       
         if(!empty($rs)) {
            return $rs;
        }
        return FALSE;
        
    }
    public function updateCustomer($arr, $id) {
     $this->_db->update(DbTable::TABLE_CUSTOMERS, $arr, "id=$id");

        return TRUE;
    }
    
    
    public function updateBankCustomers($updateCustArr, $customerDetail) {
        
        if(!empty($customerDetail) ){
         $custID = $customerDetail['customer_id'];
        
         $customer_product_ids ='';   
         $customerRecords = $this->getBankCustomerDetails($customerDetail);
         $bankRecords = $this->getBankUnicodebyCustomerID($custID);
        //mobile_country_code
          $custLOGArr = array(
              'customer_id'=> $custID,
              'bank_id'=> $bankRecords['bank_id'],
              'mobile_country_code'=> $bankRecords['mobile_country_code'],
              'old_mobile'=> $bankRecords['mobile'],
              'new_mobile'=> $updateCustArr['mobile'],
              'product_id'=>$customerDetail['product_id'],
              'bank_customer_id'=>$customerDetail['bank_customer_id'],
              'product_customer_id'=>$customerDetail['product_customer_id'],
         );
       
         $bankUnicodeArr = Util::bankUnicodesArray();
         
         foreach($customerRecords as $customerInfo){
             $customer_product_ids .= $customerInfo['product_customer_id'].",";
         }
         $customer_product_ids = rtrim($customer_product_ids,',');
         
            switch ($bankRecords['bank_unicode']) {
                case $bankUnicodeArr['2']:
                     $this->_db->update(DbTable::TABLE_RAT_CORP_CARDHOLDER, $updateCustArr, "id IN ( ".$customer_product_ids." ) AND STATUS = '".STATUS_ACTIVE."'");
                    break;
            }
            $updateCustomerTrackInfo = array(
                'info'=>$updateCustArr['mobile']
            );
             foreach($customerRecords as $custDetail){
               
               $this->_db->update(DbTable::TABLE_CUSTOMER_TRACK, $updateCustomerTrackInfo, "product_id =".$custDetail['product_id']." AND customer_id =".$custDetail['product_customer_id']." AND flag = ".CUSTOMER_TRACK_MOBILE_FLAG);
             }
            
             $this->_db->insert(DbTable::TABLE_CUSTOMER_UPDATE_LOG, $custLOGArr);
             $this->updateCustomer($updateCustArr, $custID);  
        }
        return TRUE;
    }
    
    
     public function getBankUnicodebyCustomerID($custID) {
        
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_CUSTOMERS .' as t', array('t.id as customerID','t.mobile','t.bank_id','t.mobile_country_code'));
        $select->joinLeft(DbTable::TABLE_BANK . ' as b', 'b.id= t.bank_id', array('b.unicode as bank_unicode'));
        $select->where('b.status =?',STATUS_ACTIVE);
        $select->where('t.status =?',STATUS_ACTIVE);
        $select->where('t.id =?',$custID);
        $rs = $this->_db->fetchRow($select);
        if(!empty($rs)) {
            return $rs;
        }
        return FALSE;
    }
    
    public function checkDupliCustomerMobile($params) {
       
        $existCust = $this->checkCustomerDetails($params);
        if(!empty($existCust)) {
        $customerID = $existCust['customer_id'];    
        $mobile = isset($params['mobile']) ? $params['mobile'] : '';
        $sql = $this->_db->select();
        $sql->from(DbTable::TABLE_CUSTOMERS, array('id'));
        $sql->where('mobile =?',$mobile);
        $sql->where('id !=?',$customerID);
        $rs = $this->_db->fetchRow($sql);
        if(!empty($rs)) {
            return TRUE;
          }else{
              return FALSE;
          }
        }
        return FALSE;
        
    }

    public function checkBankCustomer($params) {
        if ($params['bank_id'] == '' || $params['product_id'] == '' || $params['rat_customer_id'] == '' 
             || $params['cardholder_id'] == '' ){
            throw new App_Exception('Insufficient Data for validating Customer Type');
        }
        
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_CUSTOMERS ." as c", array('id', 'customer_type'));
        $select->join(DbTable::TABLE_CUSTOMERS_DETAIL ." as cd", "c.id = cd.customer_id", array('id as cust_details_id'));
        $select->where("cd.bank_id = '" . $params['bank_id'] . "'");
        $select->where("cd.product_id = '" . $params['product_id'] . "'");
        $select->where("cd.bank_customer_id = '" . $params['rat_customer_id'] . "'");
        $select->where("cd.product_customer_id = '" . $params['cardholder_id'] . "'");
        
        $rs = $this->_db->fetchRow($select);

        if(!empty($rs)) {
            return $rs;
        }
        return FALSE;        
    }
    
    public function getCustomerId($customerId) {
       
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_CUSTOMERS_DETAIL. " as cd", array('product_customer_id'));
        $select->join(DbTable::TABLE_RAT_CORP_CARDHOLDER. " as rcc", "cd.product_customer_id=rcc.id", array('rat_customer_id', 'customer_master_id'));
        $select->where("cd.customer_id = '" . $customerId . "'");
        $select->where("rcc.status = '" . STATUS_ACTIVE . "'");
        
        return $this->_db->fetchAll($select);
    }
}
