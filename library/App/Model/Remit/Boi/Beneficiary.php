<?php

class Remit_Boi_Beneficiary extends Remit_Boi
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
    protected $_name = DbTable::TABLE_BENEFICIARIES;
    
    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
//    protected $_rowClass = 'App_Table_Beneficiaries';
    
    

   
          
    
    public function addbeneficiary($param){
        
        $accountNumber = isset($param['bank_account_number'])?trim($param['bank_account_number']):'';
        $email = isset($param['email'])?trim($param['email']):'';
        $mobile = isset($param['mobile'])?trim($param['mobile']):'';
        $branchAddress = isset($param['branch_address'])?trim($param['branch_address']):'';
        $encryptionKey = App_DI_Container::get('DbConfig')->key;
        //exit;
        if($accountNumber!='')
            $param['bank_account_number'] = new Zend_Db_Expr("AES_ENCRYPT('".$accountNumber."','".$encryptionKey."')");
        if($email!='')
            $param['email'] = new Zend_Db_Expr("AES_ENCRYPT('".$email."','".$encryptionKey."')");
        if($mobile!='')
            $param['mobile'] = new Zend_Db_Expr("AES_ENCRYPT('".$mobile."','".$encryptionKey."')");
        if($branchAddress!='')
            $param['branch_address'] = new Zend_Db_Expr("AES_ENCRYPT('".$branchAddress."','".$encryptionKey."')");
        
       
      try{
          $resp = $this->insert($param); // adding remitter to t_beneficiaries   
        return TRUE;           
          }
        catch (Exception $e ) {
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                throw new Exception($e->getMessage()); exit;
            }  
    }          
      
    
    
    /**
     * Overrides findById() in App_Model
     * 
     * @param int $userId 
     * @access public
     * @return array
     */
    public function getBeneficiaryDetails($id){
         $decryptionKey = App_DI_Container::get('DbConfig')->key;
         $bankAccountNumber = new Zend_Db_Expr("AES_DECRYPT(`b`.`bank_account_number`,'".$decryptionKey."') as bank_account_number");
         $branchAddress = new Zend_Db_Expr("AES_DECRYPT(`b`.`branch_address`,'".$decryptionKey."') as branch_address");
         $mobile = new Zend_Db_Expr("AES_DECRYPT(`b`.`mobile`,'".$decryptionKey."') as mobile");
         $email = new Zend_Db_Expr("AES_DECRYPT(`b`.`email`,'".$decryptionKey."') as email");
         
         $details = $this->select()
                ->from(DbTable::TABLE_BENEFICIARIES." as b", array('id', 'remitter_id', 'name', 'nick_name', 'ifsc_code', $bankAccountNumber, 'bank_name', 'branch_name', 'branch_city', $branchAddress, 'bank_account_type', 'address_line1', 'address_line2', $mobile, $email, 'by_agent_id', 'by_ops_id', 'date_created', 'date_modified', 'status'))
                ->setIntegrityCheck(false)
                ->joinleft(DbTable::TABLE_REMITTERS.' as r',"b.remitter_id = r.id ",array('r.name as remitter_name', 'r.mobile as remitter_mobile', 'r.email as remitter_email','r.product_id'))
                ->joinleft(DbTable::TABLE_BANK_IFSC.' as bi','bi.ifsc_code= b.ifsc_code',array('bank_name'))
                ->where("b.id=$id");
         //echo $details; exit;
         
         return $this->fetchRow($details);
    }
    
    public function updateBeneficiaryDetails($data,$id){
        $update = $this->update($data,"id = $id");
        if($update)
                 return TRUE;
             else 
                 return FALSE;
    }
    
     public function updateAmlBoiBeneficiary(){
        $details = $this->select()
                       ->from($this->_name, array('id', 'name'))
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
                                   $this->_db->update($this->_name, array('aml_status' => STATUS_IS_AML), 'id='.$data['id']);	
                               } else {
                                   $this->_db->update($this->_name, array('aml_status' => STATUS_AML_UPDATE), 'id='.$data['id']);	
                               }
                          }
        return $reportsData;
    }
}