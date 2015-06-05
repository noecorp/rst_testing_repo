<?php
/**
 * Model that manages the customer master
 *
 * @copyright transerv
 */

class CustomerMaster extends BaseUser
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
    protected $_name = DbTable::TABLE_CUSTOMER_MASTER;
    
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
   
   public function add($data){
       if(empty($data))
           throw new Exception('Data missing while adding customer');
       
           $this->_db->insert(DbTable::TABLE_CUSTOMER_MASTER,$data);
           return $this->_db->lastInsertId(DbTable::TABLE_CUSTOMER_MASTER, 'id');
        
   }
   
    /**
     * 
     * @param integer $resourceId
     * @access public
     * @return void
     */
   
   public function generateCustomerId($data){
       
       if(empty($data)) {
           throw new Exception(ErrorCodes::ERROR_INVALID_DATA_ADD_CUST_FAILURE_MSG, ErrorCodes::ERROR_INVALID_DATA_ADD_CUST_FAILURE_CODE); 
       }
       if($this->setupShmartCRN()) {
            $this->insert(array(
                'shmart_crn' => $this->_CRN,
                'bank_id'    => $data['bank_id'],
                'password'   => (!isset($data['password']) ||  $data['password'] != '') ? '' : $data['password'],
                'status'     => (!isset($data['status']) ||  $data['status'] != '') ? STATUS_PENDING : $data['status']
            ));
            return $this->getAdapter()->lastInsertId();
       } else {
          
           throw new Exception(ErrorCodes::ERROR_UNABLE_GENERATE_SHMART_CRN_FAILURE_MSG, ErrorCodes::ERROR_UNABLE_GENERATE_SHMART_CRN_FAILURE_CODE); 
       }
       
   }
   
     /**
     * setupUnicode
     * Setup Unicode used to generate unique unicode
     * @return boolean
     * @throws Exception
     */
    private function setupShmartCRN()
    {
            unset($this->_CRN);
            $this->_CRN = mt_rand('10000000', '99999999');
            if($this->validateGeneratedCRN() === false) {
                self::setupShmartCRN();
            }
            return true;
                            
    }
    
     /**
     * validateGeneratedUnicode
     * Validate Generated Unicode into DB, This will help to make it unique
     * @return boolean
     */
    private function validateGeneratedCRN() {
        $unicodeData = $this->fetchRow($this->select()
                                ->from($this->_name, array('id'))
                                ->where(" shmart_crn = '".$this->_CRN."' ")
        );
        if(!empty($unicodeData)) {
            return false;
        }
        return true;
    }
    
    
    private function getCustomerTableById($customerId) {
        //print $customerId;exit;
        $select = $this->select()
                ->setIntegrityCheck(false)
                //->from($this->_name . " as  cm",array('id','shmart_crn'))
                ->from("customer_master as  cm", array())
                ->join(DbTable::TABLE_BANK . " as b", "cm.bank_id = b.id", array('table_prefix'))
                ->where('cm.id =?', $customerId);
        //print $select->__toString();exit;
        $rs = $this->fetchRow($select);
        //print_r($rs);exit;
        return $rs['table_prefix'];
    }

    public function getUserInfo($id) {
        $tablePrefix = $this->getCustomerTableById($id);
        //print 'here';exit;
        $select = $this->select()
                ->setIntegrityCheck(false)
                ->from("customer_master as  cm", array('is_portal_access','shmart_crn','last_password_update'))
                ->join($tablePrefix . "_customer_master as ecm", "cm.id = ecm.customer_master_id", array('first_name', 'middle_name', 'last_name', 'mobile', 'email', 'status'))
                ->where('cm.id =?', $id);
        //print $select->__toString();exit;
        return $this->fetchRow($select);
    }
    
    public function getCustomerMasterTable($id) {
        $prefix = $this->getCustomerTableById($id);
        return $prefix.'_customer_master';
    }
    
    
    public function updateCustomerNumLoginAttempts($username,$tablename=  DbTable::TABLE_CUSTOMER_MASTER)
    { 

        $updateString = "id='$username'";
        $config = App_DI_Container::get('ConfigObject');
        $numAllowed = $config->system->login->attempts->allowed;
       
        $dbNum = $this->getnumLoginAttempts($username,$tablename);
        
        $newNum = $dbNum['num_login_attempts'] + 1;
            if($newNum >= $numAllowed)
            {
                $customerTable = $this->getCustomerMasterTable($username);
                $updateStrCustomerMaster = "customer_master_id='$username'";
                $this->_db->update($customerTable,array('status'=> STATUS_LOCKED),$updateStrCustomerMaster);
            }            
            $this->_db->update($tablename,array('num_login_attempts'=> $newNum),$updateString);                
        return $newNum;
        
    }
    
    
      public function getCustomerStatus($crn){
          
          try {
          
     $customerData = $this->fetchRow($this->select()
                                ->where(" shmart_crn = '".$crn."' ")
        );       
     if(empty($customerData) || !isset($customerData['id'])) {
         return false;
     }
     $tableCustomerMaster = $this->getCustomerMasterTable($customerData['id']);
        $select = $this->select()
                ->setIntegrityCheck(false)
                ->from("customer_master as  cm", array('is_portal_access','shmart_crn','id'))
                ->join($tableCustomerMaster . " as ecm", "cm.id = ecm.customer_master_id", array('status'))
                ->where('cm.id =?', $customerData['id']);
        return $this->fetchRow($select);
          } catch (Exception $e) {
              //print '<pre>';              print_r($e);exit;
          }
         
    }
    
    
    
    public function removenumLoginAttempts($id, $table = DbTable::TABLE_CUSTOMER_MASTER){
      $customerTable = $this->getCustomerMasterTable($id);
      $this->_db->update($table,array('num_login_attempts'=> '0'),"id=".$id);
      $this->_db->update($customerTable,array('status'=> STATUS_ACTIVE),"customer_master_id=".$id);
    }
    
    
    public function editCustomerMaster($param, $id){
       if($id>0){
         $update = $this->_db->update(DbTable::TABLE_CUSTOMER_MASTER,$param,"id=$id");
         return $update;
       } else return '';
    }

    
    
      public function getBankInfo($id){

        $select  = $this->_db->select()        
            ->from(DbTable::TABLE_CUSTOMER_MASTER.' as a',array('a.id'))
            ->join(DbTable::TABLE_BANK.' as b', "a.bank_id = b.id ", array('b.logo as logo_bank','b.unicode as bank_unicode'))
            ->where("a.id=?", $id);
        //echo $select->__toString();exit('here');
        $detailArr = $this->_db->fetchRow($select);
        
        return $detailArr;
        
    } 
 
        
        
    public function getApprovedCustomer()
    {
        return $this->fetchAll($this->select()
                        ->where('is_portal_access=?', STATUS_ACTIVE));

    }
 
    
       
    /**
     * 
     * @param integer $resourceId
     * @access public
     * @return void
     */
   
   public function getCustomerPurseInfo($customerId, $walletCode){
       
       if(empty($customerId)) {
           throw new App_Exception( __CLASS__ .': Invalid Customer info provided');
       }
       if(empty($walletCode)) {
           throw new App_Exception( __CLASS__ . ': Invalid wallet info provided');
       }

       $sql = $this->_db->select()
                    ->from(DBTable::TABLE_RAT_CUSTOMER_PURSE . ' as rcp',array('*','rcp.id as customer_purse_id'))
                    ->join(DbTable::TABLE_PURSE_MASTER . ' as pm',"rcp.purse_master_id = pm.id" )
                    ->where('rcp.rat_customer_id = ?',$customerId)
                    ->where('pm.code = ?',$walletCode);
       return $this->_db->fetchRow($sql);
   }
   
   
   //**********************//
   public function getKotakCustomerPurseInfo($customerId, $walletCode){
       
       if(empty($customerId)) {
           throw new App_Exception( __CLASS__ .': Invalid Customer info provided');
       }
       if(empty($walletCode)) {
           throw new App_Exception( __CLASS__ . ': Invalid wallet info provided');
       }

       $sql = $this->_db->select()
                    ->from(DBTable::TABLE_KOTAK_CUSTOMER_PURSE . ' as kcp',array('*','kcp.id as customer_purse_id'))
                    ->join(DbTable::TABLE_PURSE_MASTER . ' as pm',"kcp.purse_master_id = pm.id" )
                    ->where('kcp.kotak_customer_id = ?',$customerId)
                    ->where('pm.code = ?',$walletCode);
       return $this->_db->fetchRow($sql);
   }
    

}