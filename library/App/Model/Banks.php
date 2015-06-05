<?php
/**
 * Model that manages the products
 *
 * @package Operation_Models
 * @copyright transerv
 */

class Banks extends App_Model
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
    protected $_name = DbTable::TABLE_BANK;
    
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
    
    public function findAll ($page = 1,$paginate = NULL, $force = FALSE){
        
       $details = $this->select()
               ->from(DbTable::TABLE_BANK." as b",array('id','name','ifsc_code','city','branch_name','address','status'))
                ->setIntegrityCheck(false) 
                ->joinleft(DbTable::TABLE_PRODUCTS." as p","b.id=p.bank_id and p.status='".STATUS_ACTIVE."'",array('count(`p`.`bank_id`) as count'))
                ->where("b.status='".STATUS_ACTIVE."'")
                //->where("p.status='active' or p.status is null")
                ->group('b.id')
                 ->order('b.name asc');
                
                 
      return $this->_paginate($details, $page, $paginate);
        
        
    }
    public function checkBankName($name){
       $details = $this->select()
               ->from(DbTable::TABLE_BANK." as b",array('id','name','status'))
               ->where("b.status='".STATUS_ACTIVE."'")
               ->where("b.name='$name'");
             
                
       //echo $details->__toString();exit;
      $nameArr = $this->fetchRow($details);  
          if(empty($nameArr))     
      return  TRUE;
          else
              return FALSE;
    }
    
     public function productAssigned ($id){
        
       $details = $this->select()
               ->from(DbTable::TABLE_BANK." as b",array('id','name','ifsc_code','city','branch_name','address','status'))
                ->setIntegrityCheck(false) 
                ->joinleft(DbTable::TABLE_PRODUCTS." as p","b.id=p.bank_id AND p.status='".STATUS_ACTIVE."'",array('count(`p`.`bank_id`) as count'))
                ->where("b.status='".STATUS_ACTIVE."'")
                ->where("b.id=$id")
                ->group('b.id')
                 ->order('b.name asc');
                
       //echo $details->__toString();exit;
          
                  
      return $this->fetchRow($details);
        
        
    }
    public function getProductByMasterId($resourceId){
        $select = $this->_select();
        $select->from($this->_name);
        $select->where('product_master_id = ?', $resourceId);
        $select->order('name ASC');
        
        $products = $this->fetchAll($select);
       
        return $products;
    }
    
    /**
     * Overrides getAll() in App_Model
     * 
     * @param int $page 
     * @access public
     * @return Zend_Paginator
     */
    public function getAll($page = 1){
        $paginator = $this->fetchAll();
      
        $paginator = Zend_Paginator::factory($paginator);
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage(App_DI_Container::get('ConfigObject')->paginator->items_per_page);
        
        return $paginator;
    }
    
    /**
     * Overrides findById() in App_Model
     * 
     * @param int $userId 
     * @access public
     * @return array
     */
    public function findById($productId,$force = false){
        $products = parent::findById($productId);
        return $products;
    }
    
    /**
     * Overrides deleteById() in App_Model
     * 
     * @param int $privilegeId
     * @access public
     * @return void
     */
    public function deleteById($productId){
        $this->delete($this->_db->quoteInto('id = ?', $productId));
        return TRUE;
    }
    
   
    
    public function getBank()
    {
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_BANK,array('id','name'));
       
        $bankArr =  $this->_db->fetchAll($select);
        $dataArray = array();
        foreach ($bankArr as $id => $val) {
            $dataArray[$val['id']] = $val['name'];
        }
        return $dataArray;
  
    }
    
    /* getAgentRemitProductBank function will return the agent remit product bank details
    * it will expect agentid
    */
    
     public function getBankbyProductId($productId,$programType){
         if($productId=='')
	    throw new Exception(ErrorCodes::ERROR_EDIGITAL_PRODUCT_ID_MISS_MSG, ErrorCodes::ERROR_EDIGITAL_PRODUCT_ID_MISS_CODE);
         
         $select = $this->select();
                    $select->setIntegrityCheck(false);
                    $select->from(DbTable::TABLE_PRODUCTS." as p", array('bank_id'));
                    $select->join(DbTable::TABLE_BANK." as b", "b.id=p.bank_id", array('unicode'));
                    $select->where('p.id= ?', $productId);
                    if($programType) 
                        $select->where('p.program_type= ?', $programType);
        //echo $select;die;

        $bank = $this->fetchRow($select);

        return $bank;
    }
    
 
     public function updateBank($data){
      
       $insert = $this->_db->insert(DbTable::TABLE_LOG_BANK,$data);
      if( $insert > 0)
          return TRUE;
  }
    public function deleteBank($id){
      
      $data = array('status'=> STATUS_DELETED);
      $update = $this->update($data,"id=$id");
      if($update > 0)
          return TRUE;
  }
  
  
  /* getBankInfo() will return the agent remit product bank details
    * it will expect agentid
    */
    
     public function getBankInfo($bankId){
         if($bankId=='')
             throw new Exception('Bank id missing!');
         
         $select =  $this->select();
                    $select->setIntegrityCheck(false);
                    $select->from(DbTable::TABLE_BANK, array('name', 'unicode'));
                    $select->where('id =?',$bankId);
                    
        //echo $select;die;

        $bank = $this->fetchRow($select);

        return $bank;
    }
    
    
    
    /* getMVCProductBanks function will return the MVC program type products bank details
    */
    
     public function getMVCProductBanks(){
         
         $select = $this->select();
                    $select->setIntegrityCheck(false);
                    $select->from(DbTable::TABLE_PRODUCTS." as p", array('bank_id as id'));
//                  $productCondition = "bapc.product_id = p.id AND program_type='".PROGRAM_TYPE_REMIT."'";
                    $select->join(DbTable::TABLE_BANK." as b", "p.bank_id=b.id AND b.status='".STATUS_ACTIVE."'", array('name'));
                    $select->where('p.program_type =?',PROGRAM_TYPE_MVC);
                    $select->where('p.status =?',STATUS_ACTIVE);
                    $select->group('p.bank_id');
        //echo $select;die;

        $bank = $this->fetchAll($select);
        
        $dataArray = array();
        foreach ($bank as $id => $val) {
            $dataArray[$val['id']] = $val['name'];
        }
       
        return $dataArray;

    }
    
    /* getBankLogs() will return the logs details of particular bank on date duration basis
     * @param bank id, duration dates 
     * @return array
     */
    public function getBankLogs($param, $page = 1, $paginate = NULL, $force = FALSE){
        $bankId = isset($param['bank_id'])?$param['bank_id']:0;
        $dateFrom = isset($param['from'])?$param['from']:'';
        $dateTo = isset($param['to'])?$param['to']:'';
        
        if ($bankId<1 || $dateFrom=='' || $dateTo=='') {
            return array();
        }
        
        $select = $this->select();
        $select->from(DbTable::TABLE_LOG_BANK.' as lb');
        $select->joinLeft(DbTable::TABLE_OPERATION_USERS." as ou", "lb.by_ops_id=ou.id", 'concat(ou.firstname," ",ou.lastname) as by_ops_name');
        $select->setIntegrityCheck(false);

        $select->where('lb.bank_id = ?', $bankId);
        $select->where("DATE(lb.date_created) >='".$dateFrom."'"); 
        $select->where("DATE(lb.date_created) <='".$dateTo."'"); 
        //echo $select->__toString();  exit;   
        
        return $this->_paginate($select, $page, $paginate);
    }
    
    /* getRemitProductByBankUnicode function will return the Remit program type products bank details
    */
    
     public function getRemitProductByBankUnicode($params=''){
         
         // $params : unicode of Banks
         $mergeQuery = ''; // Default set blank so fetching all bank records
         if(isset($params) && ($params !='')){
            // Merge Unicode condition for getting single bank record 
            $mergeQuery = " AND b.unicode=".$params;
            $dataArray = array();
         }else{
            $dataArray = array('' => 'Select Bank');
         }
         
         $select = $this->select();
                    $select->setIntegrityCheck(false);
                    $select->from(DbTable::TABLE_PRODUCTS." as p", array('bank_id as id'));
//                  $productCondition = "bapc.product_id = p.id AND program_type='".PROGRAM_TYPE_REMIT."'";
                    $select->join(DbTable::TABLE_BANK." as b", "p.bank_id=b.id AND b.status='".STATUS_ACTIVE."'".$mergeQuery, array('name','unicode'));
                    $select->where('p.program_type =?',PROGRAM_TYPE_REMIT);
                    $select->where('p.status =?',STATUS_ACTIVE);
                    $select->group('p.bank_id');
//        echo $select;die;

        $bank = $this->fetchAll($select);
        
       // $dataArray = array('' => 'Select Bank');
        foreach ($bank as $id => $val) {
            $dataArray[$val['unicode']] = $val['name'];
        }
      
        return $dataArray;

    }
    
/*
 * getRemitProductByBankUnicode gets bank's details by unicode, expexts unicode
 */
    
    
    /* getRemitProductBanks function will return the Remit program type products bank details
    */
    
     public function getRemitProductBanks($program_type=''){
         
      
         $select = $this->select();
                    $select->setIntegrityCheck(false);
                    $select->from(DbTable::TABLE_PRODUCTS." as p", array('bank_id as id'));
//                  $productCondition = "bapc.product_id = p.id AND program_type='".PROGRAM_TYPE_REMIT."'";
                    $select->join(DbTable::TABLE_BANK." as b", "p.bank_id=b.id AND b.status='".STATUS_ACTIVE."'", array('name','unicode'));
                    if(!empty($program_type)){
                        $select->where('p.program_type =?',$program_type);
                    } else {
                        $select->where('p.program_type =?',PROGRAM_TYPE_REMIT);
                    }
                    $select->where('p.status =?',STATUS_ACTIVE);
                    $select->group('p.bank_id');
//        echo $select;die;

        $bank = $this->fetchAll($select);
        
        $dataArray = array('' => 'Select Bank');
        foreach ($bank as $id => $val) {
            $dataArray[$val['unicode']] = $val['name'];
        }
      
        return $dataArray;

    }
    
/*
 * getBankbyUnicode gets bank's details by unicode, expexts unicode
 */
      public function getBankbyUnicode($unicode){
         
         $select = $this->select();
                    $select->from(DbTable::TABLE_BANK,  array('unicode','name','id'));
                    $select->where('unicode =?', $unicode);
//        echo $select;die;

        $bank = $this->fetchRow($select);

        return $bank;
    }
    
    
      /* getRemitProductBanks function will return the Remit program type products bank details
    */
    
     public function getProductsByBankUnicode($unicode){
        $bankUnicodeArr = Util::bankUnicodesArray();
         if (!isset($unicode) || ($unicode == '')){
            $unicode = $bankUnicodeArr['1'];
        }
        $select = $this->_db->select();
                    $select->from(DbTable::TABLE_PRODUCTS." as p", array('id'));
                    $select->join(DbTable::TABLE_BANK." as b", "p.bank_id=b.id AND b.status='".STATUS_ACTIVE."'",array('id as bank_id'));
        $select->where("b.unicode = '".$unicode."'"); 
        $bankArr = $this->_db->fetchAll($select);
        $product_ids = '';
        foreach($bankArr as $bank){
          $product_ids .= $bank['id'].",";
        }
        // remove last ,
        return  substr($product_ids,0,-1);

        }
   
         /* getCustomerConceptBanks function will return the bank list which follow customer concept
    */
    
        /*
         * Get Product list of bank with fiter by program type
         */
        
        public function getProductsByBankUnicodeProgram($unicode,$programType,$programTypeDigi){
        $bankUnicodeArr = Util::bankUnicodesArray();
         if (!isset($unicode) || ($unicode == '')){
            $unicode = $bankUnicodeArr['1'];
        }
        if (!isset($programType) || ($programType == '')){
            $programType = $programType;
        }
        $select = $this->_db->select();
                    $select->from(DbTable::TABLE_PRODUCTS." as p", array('id'));
                    $select->join(DbTable::TABLE_BANK." as b", "p.bank_id=b.id AND b.status='".STATUS_ACTIVE."'",array('id as bank_id'));
        $select->where("b.unicode = '".$unicode."'"); 
        
        if($programType !='' && $programTypeDigi !=''){
            $select->where("p.program_type IN ('".$programType."', '".PROGRAM_TYPE_DIGIWALLET."')");
        } elseif($programType !=''){
            $select->where("p.program_type = '".$programType."'");
        } elseif($programTypeDigi !=''){
            $select->where("p.program_type = '".PROGRAM_TYPE_DIGIWALLET."'");
        }

        $bankArr = $this->_db->fetchAll($select);
        $product_ids = '';
        foreach($bankArr as $bank){
          $product_ids .= $bank['id'].",";
        }
        // remove last ,
        return  substr($product_ids,0,-1);

        }
   /*
    * 
    */
     public function getCustomerConceptBanks(){
         
        $bankRatnakar = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR);
        $bankRatnakarUnicode = $bankRatnakar->bank->unicode;
        $bankKotak = App_DI_Definition_Bank::getInstance(BANK_KOTAK);
        $bankKotakUnicode = $bankKotak->bank->unicode;
        $bankBoi = App_DI_Definition_Bank::getInstance(BANK_BOI);
        $bankBoiUnicode = $bankBoi->bank->unicode;
        $bankArr = array('' => 'Select Bank',$bankRatnakar->bank->unicode => $bankRatnakar->bank->name,$bankKotak->bank->unicode => $bankKotak->bank->name,
            $bankBoi->bank->unicode => $bankBoi->bank->name);
        return $bankArr;

    }
    
    public function getCommProductBanks(){
         
         $select = $this->select();
                    $select->setIntegrityCheck(false);
                    $select->from(DbTable::TABLE_PRODUCTS." as p", array('bank_id as id'));
//                  $productCondition = "bapc.product_id = p.id AND program_type='".PROGRAM_TYPE_REMIT."'";
                    $select->join(DbTable::TABLE_BANK." as b", "p.bank_id=b.id AND b.status='".STATUS_ACTIVE."'", array('name','unicode'));
                    $select->where("p.program_type = '".PROGRAM_TYPE_REMIT."' OR p.program_type = '".PROGRAM_TYPE_MVC."'");
                    $select->where('p.status =?',STATUS_ACTIVE);
                    $select->group('p.bank_id');
        //echo $select;die;

        $bank = $this->fetchAll($select);
        
        $dataArray = array('' => 'Select Bank');
        foreach ($bank as $id => $val) {
            $dataArray[$val['unicode']] = $val['name'];
        }
       
        return $dataArray;

    }
    /*
    * getBankbyID gets bank's details by bankid
    */
      public function getBankbyID($bankID){
         
         $select = $this->select();
                    $select->from(DbTable::TABLE_BANK,array('id','name','ifsc_code','city','branch_name','address','status'));
                    $select->where('id =?', $bankID);
      
        $bank = $this->fetchRow($select);

        return $bank;
    }
    
    /*
     * getBankidByProductid : getting bank id by product id
     */
     public function getBankidByProductid($productid){
       
        $select = $this->_db->select();
                    $select->from(DbTable::TABLE_PRODUCTS." as p", array('id as product_id'));
                    $select->join(DbTable::TABLE_BANK." as b", "p.bank_id=b.id AND b.status='".STATUS_ACTIVE."'",array('id as bank_id'));
        $select->where("p.id = ".$productid); 
        $bank = $this->_db->fetchRow($select);
       
        if(!empty($bank)) {
            return $bank;
          }else{
              return FALSE;
          }

        }

   
        public function getBanksByUnicode($unicodes){
         $unicodes = implode(",", $unicodes);
         $select = $this->select();
                    $select->setIntegrityCheck(false);
                    $select->from(DbTable::TABLE_BANK." as b",  array('name','unicode'));
                    $select->where("b.unicode IN ($unicodes)" );
                    $select->where('b.status =?',STATUS_ACTIVE);

        $bank = $this->fetchAll($select);
        
        $dataArray = array('' => 'Select Bank');
        foreach ($bank as $id => $val) {
            $dataArray[$val['unicode']] = $val['name'];
        }
      
        return $dataArray;

    }
    
        
    public function getRAT_KotakBanks() { 
        $bankRatnakar = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR); 
        $bankKotak = App_DI_Definition_Bank::getInstance(BANK_KOTAK); 
        $bankArr = array(
            '' => 'Select Bank',
            $bankRatnakar->bank->unicode => $bankRatnakar->bank->name,
            $bankKotak->bank->unicode => $bankKotak->bank->name, 
        );
        return $bankArr;  
    }
    
    public function getAllBanks($banks){
        $bankArr = array('' => 'Select Bank');
        foreach ($banks as $bank){
            $bankName = App_DI_Definition_Bank::getInstance($bank);
            $bankArr[$bankName->bank->unicode] = $bankName->bank->name ;
        }
        return $bankArr; 

    }
    
    public function getIFSCBanks(){ 
	$sql = $this->select();
	$sql->setIntegrityCheck(false);
	$sql->from(DbTable::TABLE_BANK_IFSC, array('DISTINCT(bank_name)'));  
	$sql->order('bank_name ASC'); 
        $bank = $this->fetchAll($sql); 
        $dataArray = array('' => 'Select Bank');
        foreach ($bank as $val) {
            $dataArray[$val['bank_name']] = $val['bank_name'];
        } 
        return $dataArray;
    }
}
