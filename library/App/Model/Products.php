<?php
/**
 * Model that manages the products
 *
 * @package Operation_Models
 * @copyright transerv
 */

class Products extends App_Model
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
    protected $_name = DbTable::TABLE_PRODUCTS;
    
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
    protected $_referenceMap = array(
        't_product_master' => array(
            'columns' => 'product_master_id',
            'refTableClass' => 't_product_master',
            'refColumns' => 'id'
        ),
    );
    
     
    /**
     * Retrieves all the products attached to
     * the specified master product
     * 
     * @param integer $resourceId
     * @access public
     * @return void
     */
    
    public function findAllProducts ($page = 1,$paginate = NULL, $force = FALSE, $bid = 0){
        
        $details = $this->select()
                ->from(DbTable::TABLE_PRODUCTS." as p")
                ->setIntegrityCheck(false)
                ->joinleft(DbTable::TABLE_BANK.' as b',"p.bank_id = b.id ",array('b.name as bank_name'))
                ->joinleft(DbTable::TABLE_PRODUCT_LIMIT." as l"," l.product_id = p.id AND l.status='".STATUS_ACTIVE."'",array('count(l.id) as count','l.status'));
                
         if($bid > 0) {
             $details->where("b.id=$bid");
         }
         
         //$details->where("l.status='active' OR l.status is null");
         $details->where("p.status='".STATUS_ACTIVE."'")
                  ->group('p.id')
            //    ->having("l.status='active'")
                ->order('b.name')
                ->order('p.name');
         
       // echo $details->__toString();      exit;
       return $this->_paginate($details, $page, $paginate);
    }
    
  
     public function assgignedLimit($id){
        
        $details = $this->select()
                ->from(DbTable::TABLE_PRODUCTS." as p")
                ->setIntegrityCheck(false)
                ->joinleft(DbTable::TABLE_BANK.' as b',"p.bank_id = b.id ",array('b.name as bank_name'))
                ->joinleft(DbTable::TABLE_PRODUCT_LIMIT." as l",' l.product_id = p.id',array('count(l.id) as count'));
                
         
         $details->where("p.id=$id");
         $details->where("p.status='".STATUS_ACTIVE."'")
                ->group('p.id')
                ->order('b.name')
                ->order('p.name');
         
       // echo $details->__toString();      exit;
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
    
     /**
     * Retrive all master products for dropdown
     * 
     * @param int $userId 
     * @access public
     * @return array
     */
    public function getProducts()
    {
        $select = $this->_select();
        $select->from(DbTable::TABLE_PRODUCTS,array('id','name','ecs_product_code'));
        $select->setIntegrityCheck(false);
        $productArr =  $this->fetchAll($select);
        
        $dataArray = array();
        $dataArray = array('' => 'Select Product name');
        foreach ($productArr as $id => $val) {
            $dataArray[$val['id']] = $val['ecs_product_code'].' ('.$val['name'].')';
        }
        return $dataArray;
  
    }
    
    public function getBank()
    {
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_BANK,array('id','name'));
        $select->where("status='".STATUS_ACTIVE."'");
       
        $bankArr =  $this->_db->fetchAll($select);
        $dataArray = array();
        $dataArray = array('' => 'Select Bank ');
        foreach ($bankArr as $id => $val) {
            $dataArray[$val['id']] = $val['name'];
        }
        return $dataArray;
  
    }
    

    
     public function getAgentProducts($agentId, $programType=PROGRAM_TYPE_MVC,$productUnicode = '' ){
         
         
         $curdate = date("Y-m-d");
         $joinCondition = "bapc.product_id = p.id AND p.program_type= '".$programType."'";
         if($productUnicode != '') {
            $joinCondition .= " AND p.unicode = $productUnicode";
         }
        
         $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(DbTable::TABLE_BIND_AGENT_PRODUCT_COMMISSION." as bapc", array('product_id'))
                    //->joinLeft("t_fee as f", "ap.fee_id = f.id", array("product_id"))
                    ->join(DbTable::TABLE_PRODUCTS." as p", $joinCondition, array('product_name' => 'name'))
                    ->where('bapc.agent_id =?',$agentId)
                    ->where("'".$curdate."' >= bapc.date_start AND ('".$curdate."' <= bapc.date_end OR bapc.date_end = '0000-00-00')")
                    ->order("p.name ASC");
//        echo $select->__toString();//die;

        $products = $this->fetchAll($select);

        $dataArray = array();
        foreach ($products as $id => $val) {
            $dataArray[$val['product_id']] = $val['product_name'];
        }

        return $dataArray;
    }
    
   
    
    public function getCardholderProducts($param){
        if($param['cardholder_id']=='')
            return false;
        
        $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(DbTable::TABLE_CARDHOLDERS_PRODUCT)                    
                    ->where('cardholder_id =?',$param['cardholder_id'])
                    ->where('status=?',$param['status']);
        
        //echo $select->__toString();die;
        
        return $this->fetchAll($select);   
        
    }
    

    /* agentProductLimit()
    * that funciton will return the agent product limit details
    * it will accept $param array with agent_id and product_id
    */ 
     public function agentProductLimit($param){
         if($param['agent_id']<1 || $param['product_id']<1)
             throw new Exception('No sufficient data received');
         
       $curdate = date('Y-m-d');
       $select = $this->select() 
               ->setIntegrityCheck(false)
               ->from(DbTable::TABLE_BIND_AGENT_PRODUCT_COMMISSION.' as bapc', array('product_limit_id'))  
               ->joinLeft(DbTable::TABLE_PRODUCT_LIMIT." as pl", "bapc.product_limit_id = pl.id", array('limit_out_first_load'))                   
               ->where('bapc.agent_id = ?', $param['agent_id'])
               ->where('bapc.product_id = ?', $param['product_id'])
               //->where('bapc.date_start <= ?', new Zend_Db_Expr('CURDATE()'))
               //->where('bapc.date_end = ?', '0000-00-00')
               //->where("'".$cuDate."' BETWEEN bapc.date_start AND bapc.date_end")
               ->where("'".$curdate."' >= date_start AND ('".$curdate."' <= date_end OR date_end = '0000-00-00')");
               //->where('bapc.status = ?', 'active');               
               //echo $select->__toString(); exit;
              
        return $this->fetchRow($select);
      
  }
    
  public function getLastProduct(){
      $select = $this->select()
               ->from($this)
               ->order('id desc')
               ->limit('1');
      //echo $select->__toString();      exit;
     return $productCodeArr = $this->fetchRow($select);
  }
  
public function getBindProducts(){
         
         $select = $this->_db->select()
                  ->from(DbTable::TABLE_BIND_AGENT_PRODUCT_COMMISSION, array('product_id'))
                  ->group('product_id');
        //echo $select->__toString();die;

        $products = $this->_db->fetchAll($select);
        $dataArray = array();
        foreach ($products as $val) {
            $dataArray[] = $val['product_id'];
        }
        return $dataArray;

    }
    
    public function getBindProductsLimits(){
         
         $select = $this->_db->select()
                  ->from(DbTable::TABLE_BIND_AGENT_PRODUCT_COMMISSION, array('product_limit_id'))
                  ->group('product_limit_id');
        //echo $select->__toString();exit;

        $products = $this->_db->fetchAll($select);
        $dataArray = array();
        foreach ($products as $val) {
            $dataArray[] = $val['product_limit_id'];
        }
        return $dataArray;

    }
    public function updateProduct($data){
      $insert = $this->_db->insert(DbTable::TABLE_LOG_PRODUCTS,$data);
      if( $insert > 0)
          return TRUE;
  }
    public function deleteProduct($id){
      
      $data = array('status'=> STATUS_DELETED,'ip'=>Util::getIP());
      $update = $this->update($data,"id=$id");
      if($update > 0)
          return TRUE;
  }
  
  
   /* getBankProducts function will return the agent remit product details
    * it will expect agentid
    */
    
     public function getBankProducts($bankId, $programType=''){
         if($bankId=='')
             throw new Exception('Bank id missing!');
         
         $select = $this->select();
         $select->setIntegrityCheck(false);
         $select->from(DbTable::TABLE_PRODUCTS, array('id', 'name'));
         $select->where('bank_id =?',$bankId);
         $select->where('status =?',STATUS_ACTIVE);
         if($programType!='')
             $select->where('program_type =?',$programType);
        //echo $select;die;

        $products = $this->_db->fetchAll($select);
        $dataArray = array();
        foreach ($products as $val) {
            $dataArray[$val['id']] = $val['name'];
        }
        return $dataArray;
    }
    
   /* getProgramProducts function will return products based on bank & program type
    */
    
     public function getBankProgramProducts($bankId, $programType){
                  
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(DbTable::TABLE_PRODUCTS, array('id', 'name'));
        if($bankId != 0){
        $select->where('bank_id =?',$bankId);
        }
        $select->where('program_type =?',$programType);

        if($programType == PROGRAM_TYPE_DIGIWALLET) {
            $select->where('const =?',PRODUCT_CONST_RAT_SMP);
        }

        $select->where('status =?',STATUS_ACTIVE);

        $products = $this->_db->fetchAll($select);
        $dataArray = array();
        foreach ($products as $val) {
            $dataArray[$val['id']] = $val['name'];
        }
        return $dataArray;
    }
    /* getProductInfo() will return the product details
    * it will expect product ID
    */
    
     public function getProductInfo($productId){
         if($productId=='')
             throw new Exception(ErrorCodes::ERROR_EDIGITAL_PRODUCT_ID_MISS_MSG, ErrorCodes::ERROR_EDIGITAL_PRODUCT_ID_MISS_CODE);
         
         $select =  $this->select();
                    $select->setIntegrityCheck(false);
                    $select->from(DbTable::TABLE_PRODUCTS, array('id','name', 'unicode','bank_id', 'program_type','ecs_product_code','const', 'static_otp'));
                    $select->where('id =?',$productId);
        
        return $this->fetchRow($select);
   }
    
    
    
    /** getProductLogs() will return the logs details of particular agent on date duration basis
     * @param product id, duration dates 
     * @return array
     */
    public function getProductLogs($param, $page = 1, $paginate = NULL, $force = FALSE){
        $productId = isset($param['product_id'])?$param['product_id']:0;
        $dateFrom = isset($param['from'])?$param['from']:'';
        $dateTo = isset($param['to'])?$param['to']:'';
        
        if ($productId<1 || $dateFrom=='' || $dateTo=='') {
            return array();
        }
        
        $select = $this->select();
        $select->from(DbTable::TABLE_LOG_PRODUCTS.' as lp');
        $select->joinLeft(DbTable::TABLE_BANK.' as bk','lp.bank_id=bk.id', array('bk.name as bank_name'));
        $select->joinLeft(DbTable::TABLE_OPERATION_USERS." as ou", "lp.by_ops_id=ou.id", 'concat(ou.firstname," ",ou.lastname) as by_ops_name');
        $select->setIntegrityCheck(false);

        $select->where('lp.product_id = ?', $productId);
        $select->where("DATE(lp.date_created) >='".$dateFrom."'"); 
        $select->where("DATE(lp.date_created) <='".$dateTo."'"); 
        //echo $select->__toString();  exit;   
        //$abc = $this->fetchAll($select);
        
        return $this->_paginate($select, $page, $paginate);
    }
    
       /* getProductInfoByUnicode() will return the product details
    * it will expect product Unicode
    */
    
     public function getProductInfoByUnicode($unicode){
         if($unicode == '')
             throw new Exception('Unicode missing');
         
         $select =  $this->select();
                    $select->from(DbTable::TABLE_PRODUCTS, array('id','name', 'unicode','bank_id', 'program_type','ecs_product_code'));
                    $select->where('unicode =?',$unicode);
                    $select->where("status='".STATUS_ACTIVE."'");
                    $select->limit(1);
        //echo $select;die;

        $product = $this->fetchRow($select);

        return $product;
	}
    /* getBankProductsByUnicode function will return the products associated with the bank
    * it will expect Bank unicode
    */
    
     public function getBankProductsByUnicode($unicode){
        
         $select = $this->_db->select();
         
         $select->from(DbTable::TABLE_PRODUCTS ." as p" ,array('id'));
         $select->joinLeft(DbTable::TABLE_BANK ." as b","p.bank_id = b.id",array());
         $select->where("b.unicode = '".$unicode."'");
         $select->where('p.status =?',STATUS_ACTIVE);
         $products = $this->_db->fetchAll($select);
       
        $dataArray = '';
        foreach ($products as $val) {
            $dataArray.= $val['id'].',';
        }
        return substr($dataArray,0,-1);
    }
    
    
    /**
     * getAgentProductsInfo
     * Get Agent Product info if $subAgentId is passed it will return product which is not assigned to sub agent
     * @param type $agentId
     * @param type $subAgentId
     * @return type
     */
     public function getAgentProductsInfo($agentId, $subAgentId = ''){
         
         if(!empty($subAgentId)) {
             $select = 'SELECT `bapc`.`product_id`, `p`.`name` AS `product_name` 
                                FROM `t_bind_agent_product_commission` AS `bapc` 
                                INNER JOIN `t_products` AS `p` ON bapc.product_id = p.id 
                                WHERE (bapc.agent_id ="'.$agentId.'") AND bapc.product_id NOT IN (SELECT product_id from t_bind_agent_product_commission where agent_id = "'.$subAgentId.'")';
             return $this->_db->fetchAll($select);
         } else {
         $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(DbTable::TABLE_BIND_AGENT_PRODUCT_COMMISSION." as bapc", array('product_id'))
                    ->join(DbTable::TABLE_PRODUCTS." as p", "bapc.product_id = p.id", array('product_name' => 'name'))

                    ->where('bapc.agent_id =?',$agentId);
         
        return $this->fetchAll($select);
         }
    }
    public function getBankProductsByUnicodeDD($unicode, $flg = '', $program_type = ''){
        
         $select = $this->_db->select();
         
         $select->from(DbTable::TABLE_PRODUCTS ." as p" ,array('id','name'));
         $select->joinLeft(DbTable::TABLE_BANK ." as b","p.bank_id = b.id",array());
         $select->where("b.unicode = '".$unicode."'");
         $select->where('p.status =?',STATUS_ACTIVE);
         if($flg == 'common') {
             $select->where("p.flag_common = ?",FLAG_YES);
         }
         if(!empty($program_type))
         {
             $select->where("p.program_type IN(?)", $program_type);
         }
         return $this->_db->fetchAll($select);
    }
    
    /*
     * Getting Product list of relevent Bank as well Program Type and flag_common is YES
     */
     public function getBankProductsProgramByUnicodeDD($unicode,$programType, $flg = ''){
        
         $select = $this->_db->select();
         
         $select->from(DbTable::TABLE_PRODUCTS ." as p" ,array('id','name','unicode'));
         $select->joinLeft(DbTable::TABLE_BANK ." as b","p.bank_id = b.id",array());
         $select->where("b.unicode = '".$unicode."'");
         $select->where('p.status =?',STATUS_ACTIVE);
          if($programType!=''){
             $select->where('program_type =?',$programType);
            }
         if($flg == 'common') {
            // $select->where("p.flag_common = ?",FLAG_YES);
         }
         return $this->_db->fetchAll($select);
    }
    
    public function getProductDD($product_id){
        $products = $this->findById($product_id);
        
        $products = Util::toArray($products);
        $dataArray = array();
        $dataArray[$products['id']] = $products['name'];
        return $dataArray;
    }
    
    public function isActiveProduct($productId) {
        $sql = $this->select()
                ->from($this->_name, array('id','bank_id','name','description','currency','ecs_product_code','program_type','unicode','const'))
                ->where('id=?',$productId)
                ->where('status=?',STATUS_ACTIVE);
        return $this->fetchRow($sql);
    }
    
    
    public function isActiveProductByConst($productId,$const) {
        $sql = $this->select()
                ->from($this->_name, array('id','bank_id','name','description','currency','ecs_product_code','program_type','unicode','const'))
                ->where('id=?',$productId)
                ->where('const=?',$const)
                ->where('status=?',STATUS_ACTIVE);
        return $this->fetchRow($sql);
    }
    
    public function getProductList($bankId,$programType = PROGRAM_TYPE_CORP,$filter_products = FALSE){

        $select = $this->_db->select();
        $select->from(DbTable::TABLE_PRODUCTS,array('id','name'));
        $select->where("bank_id =?", $bankId);
        $select->where("program_type =?", $programType);
        $select->where('status =?',STATUS_ACTIVE);
        $select->order("name ASC");
       
        
        if($filter_products == 'kotak_gpr')
         {
             $str = "'" . PRODUCT_CONST_KOTAK_SEMICLOSE_GPR . "', '" . PRODUCT_CONST_KOTAK_OPENLOOP_GPR . "'";
             $select->where("const IN ($str)");
         }
        else {
             $str = "'" . PRODUCT_CONST_KOTAK_AMULWB . "', '" . PRODUCT_CONST_KOTAK_AMULGUJ . "'";
             $select->where("const IN ($str)");
        }
        
        $productArr =  $this->_db->fetchAll($select);
        $dataArray = array('' => 'Select Product');
        foreach ($productArr as $id => $val) {
            $dataArray[$val['id']] = $val['name'];
        }
        return $dataArray;
        
    }
    
    /**
     * getCorporateProductsInfo
     * Get Agent Product info if $subAgentId is passed it will return product which is not assigned to sub agent
     * @param type $agentId
     * @param type $subAgentId
     * @return type
     */
     public function getCorporateProductsInfo($coporateId, $subCorporateId = ''){
         
         if(!empty($subCorporateId)) {
             $select = "SELECT `bapc`.`product_id`, `p`.`name` AS `product_name` 
                                FROM `corporate_bind_product_commission` AS `bapc` 
                                INNER JOIN `t_products` AS `p` ON bapc.product_id = p.id AND p.const IN ('RAT_CNERGYIS','KOTAK_OPENLOOP_GPR','KOTAK_SEMICLOSE_GPR','RAT_SURYODAY') WHERE (bapc.corporate_id = '".$coporateId."') AND bapc.product_id NOT IN (SELECT product_id from corporate_bind_product_commission where corporate_id = '".$subCorporateId."')";
             return $this->_db->fetchAll($select);
         } else {
         $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(DbTable::TABLE_BIND_CORPORATE_PRODUCT_COMMISSION." as bapc", array('product_id'))
                    ->join(DbTable::TABLE_PRODUCTS." as p", "bapc.product_id = p.id AND p.const IN ('RAT_CNERGYIS','KOTAK_OPENLOOP_GPR','KOTAK_SEMICLOSE_GPR','RAT_SURYODAY')", array('product_name' => 'name'))
                    ->where('bapc.corporate_id =?',$coporateId);
         
        return $this->fetchAll($select);
         }
    }
    
    
     public function getProductInfoByConstDD($constArr = array()){
         $conststr = "'".implode("' , '", $constArr)."'";
         $select =  $this->select();
                    $select->from(DbTable::TABLE_PRODUCTS, array('id','name', 'unicode','bank_id', 'program_type','ecs_product_code'));
                    $select->where("const IN ($conststr)");
                    $select->where("status='".STATUS_ACTIVE."'");
                   
        
        $productArr =  $this->_db->fetchAll($select);
        $dataArray = array('' => 'Select Product');
        foreach ($productArr as $id => $val) {
            $dataArray[$val['id']] = $val['name'];
        }
        return $dataArray;
        
     }
    
      public function getCorporateBankProgramProducts($bankId, $programType){
         $dataArray = array();
         if($bankId != ''){
         $select = $this->select();
         $select->setIntegrityCheck(false);
         $select->from(DbTable::TABLE_PRODUCTS, array('id', 'name'));
         $select->where('bank_id =?',$bankId);
//         $select->where("const IN ('RAT_CNERGYIS','KOTAK_OPENLOOP_GPR','KOTAK_SEMICLOSE_GPR','RAT_SURYODAY')");
         $select->where('program_type =?',$programType);
         $select->where('status =?',STATUS_ACTIVE);

        $products = $this->_db->fetchAll($select);
        
        foreach ($products as $val) {
            $dataArray[$val['id']] = $val['name'];
          }
         }
        return $dataArray;
    }

    /* getProgramProducts function will return products based on bank & program type
    */
    
     public function getRatProgramProducts($bankId, $programType, $pType=''){
                  
         $select = $this->select();
         $select->setIntegrityCheck(false);
         $select->from(DbTable::TABLE_PRODUCTS, array('id', 'name'));
         
         $select->where('bank_id =?',$bankId);
//         $select->where("const IN ('RAT_CNERGYIS','RATNAKAR_MEDIASSIST','RAT_GENERIC_GPR','RAT_SURYODAY')");
        
         if(!empty($pType) && $pType == 'all') {
             $select->where("program_type IN ('".PROGRAM_TYPE_CORP."','".PROGRAM_TYPE_DIGIWALLET."','".PROGRAM_TYPE_MVC."')");
         } else {
             $select->where('program_type =?',$programType);
         }
         $select->where('status =?',STATUS_ACTIVE);

        $products = $this->_db->fetchAll($select);
        $dataArray = array();
        foreach ($products as $val) {
            $dataArray[$val['id']] = $val['name'];
        }
        return $dataArray;
    }
    
     public function getProductIDbyConst($const){
         if($const=='')
            // throw new Exception('Constant missing');
             throw new Exception(ErrorCodes::ERROR_INVALID_PRODUCT_CONST_FAILED_RESPONSE_MSG, ErrorCodes::ERROR_INVALID_PRODUCT_CONST_FAILED_RESPONSE_CODE); 
         
         $select =  $this->select();
                    $select->from(DbTable::TABLE_PRODUCTS, array('id'));
                    $select->where('const =?',$const);
        
        $productid = $this->fetchRow($select);
        return $productid['id'];
    }
  
    
     public function getProductIDbyConstArr($constarr = array(),$return_arr = FALSE){
         
        $productidarr = array(); 
        $conststr = "'".implode("' , '", $constarr)."'";
        $select =  $this->select();
                   $select->from(DbTable::TABLE_PRODUCTS, array('id'));
                   $select->where("const IN ($conststr)");
        $productidlist = $this->fetchAll($select);
        $i = 0;
        foreach ($productidlist as $val) {
            $productidarr[$i] = $val['id'];
            $i++;
        }
          
        $productidstr = implode(",", $productidarr);
        
        if($return_arr){
            return $productidarr;
        }else{
            return $productidstr;
        }
        
        
     }
  
    
    public function getProgramProducts($bankId, $programType){
                  
         $select = $this->select();
         $select->setIntegrityCheck(false);
         $select->from(DbTable::TABLE_PRODUCTS, array('id', 'name'));
         
         $select->where('bank_id =?',$bankId);
        
         $select->where('program_type =?',$programType);
         $select->where('status =?',STATUS_ACTIVE);

        $products = $this->_db->fetchAll($select);
        $dataArray = array();
        foreach ($products as $val) {
            $dataArray[$val['id']] = $val['name'];
        }
        return $dataArray;
    }
    
    /*
     * Getting Product list of relevent Bank as well Program Type
     */
     public function getListProductsByprogram($unicode,$programType){ 
         $select = $this->_db->select(); 
         $select->from(DbTable::TABLE_PRODUCTS ." as p" ,array('id','name','unicode'));
         $select->joinLeft(DbTable::TABLE_BANK ." as b","p.bank_id = b.id",array());
         $select->where("b.unicode = '".$unicode."'");
         $select->where('p.status =?',STATUS_ACTIVE);
	if($programType!=''){
	    $select->where('program_type IN (?)',$programType);
	}
	return $this->_db->fetchAll($select);
    }
    
    public function getProductDetailbyConst($const){
         if($const=='')
             throw new Exception('Constant missing');
         
         $select =  $this->select();
                    $select->from(DbTable::TABLE_PRODUCTS, array('id','unicode','const','name'));
                    $select->where('const =?',$const);
        //echo $select;die;

        $productinfo = $this->fetchRow($select);
        if(!empty($productinfo)){
        return $productinfo;
        }else{
            return false;
        }
    }
   
  }
