<?php
/**
 * Model that manages the products
 *
 * @package Operation_Models
 * @copyright transerv
 */

class BindCorporateProductCommission extends App_Model
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
    protected $_name = DbTable::TABLE_BIND_CORPORATE_PRODUCT_COMMISSION;
    
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
    protected $_referenceMap = array();   
     
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
                ->from($this)
                ->order('name Asc');
                
     // echo $details->__toString();
       //  exit;       
                
       
       return $this->_paginate($details, $page, $paginate);
        
        
    }
    
    /*
     * agent product limit details
     */
     public function getCorporateProductLimitDetails($agentId, $productId)
     {
         $curdate = new Zend_Db_Expr('NOW()');
         $select = $this->_db->select()
                ->from(DbTable::TABLE_BIND_CORPORATE_PRODUCT_COMMISSION." as a")
                ->joinLeft(DbTable::TABLE_PRODUCT_LIMIT.' as b',"b.id = a.product_limit_id")
                ->where("a.corporate_id = ?", $agentId)
                ->where("a.product_id = ?",$productId)
                ->where("$curdate >= a.date_start AND ($curdate <= a.date_end OR a.date_end = '0000-00-00' OR a.date_end is NULL)");
         //echo $select->__toString();exit;
          return $this->_db->fetchRow($select);
     }
    
     /**
     * Overrides findById() in App_Model
     *  public function findById($productId,$force = false){
        $products = parent::findById($productId);
        return $products;
    }
     * @param int $userId 
     * @access public
     * @return array
     */
    /*public function findById($productId,$force = false){
        $products = parent::findById($productId);
        return $products;
        
    }*/
    
    public function findById($id, $force = FALSE){
        if (!is_numeric($id)) {
            return array();
        }
        
        $select = $this->_db->Select()
                   ->from(DbTable::TABLE_BIND_CORPORATE_PRODUCT_COMMISSION)
                   ->where("id=".$id);
        
        
        return $this->_db->fetchRow($select);
        
        
    }
    
    
    
    
   /*
      * Overrides getAll() in App_Model
  }
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
    
    
     
      public function checkDateDuplicacy($param){
          
          $date = Util::returnDateFormatted($param['date_start'], "d-m-Y", "Y-m-d", "-");
         $select = $this->select()
                  ->where("date_start = '$date'")
                  ->where("corporate_id = ".$param['corporate_id']);
        
        $dateArr = $this->fetchRow($select);
        if (empty($dateArr))
            return TRUE;
        else
            return FALSE;
     }
     
      public function getAgentName($agentId){
          
          
         $select = $this->_db->select()
                 ->from(DbTable::TABLE_AGENTS.' as a')
                 ->where("id = ".$agentId);
        
        $detailArr = $this->_db->fetchRow($select);
        return $detailArr;
     }
     
     public function getActiveAgentproductlimit($id){
       
         $select = $this->_db->select()    
                ->from(DbTable::TABLE_BIND_CORPORATE_PRODUCT_COMMISSION.' as a',array('a.*',"DATE_FORMAT(a.date_end, '%d-%m-%Y') as date_end_formatted",
                    "DATE_FORMAT(a.date_start, '%d-%m-%Y') as date_start_formatted"))
                 //->setIntegrityCheck(false)
                 ->joinLeft(DbTable::TABLE_PRODUCTS.' as b', "b.id=a.product_id",array('b.name as product_name'))
                 ->joinLeft(DbTable::TABLE_PRODUCT_LIMIT.' as c', "c.id=a.product_limit_id",array('c.name as product_limit_name'))
                 ->joinLeft(DbTable::TABLE_PLAN_COMMISSION.' as d', "d.id=a.plan_commission_id",array('d.name as plan_comm_name'))
                 ->joinLeft(DbTable::TABLE_PLAN_FEE.' as f', "f.id=a.plan_fee_id",array('f.name as plan_fee_name'))
                 //->where("bal.date_end = '0000-00-00'")
                 ->where('a.id= ?', $id);
         //echo $select->__toString();exit;
         return $this->_db->fetchRow($select);
        
    }
    
    public function deleteBindAgentProductCommission($id, $prevId)
     { 
        if(isset($prevId)) 
        {
             $dataPrev = array(
                'date_end' => '0000-00-00' );
             $upd = $this->_db->update(DbTable::TABLE_BIND_CORPORATE_PRODUCT_COMMISSION, $dataPrev, "id = $prevId");
           if($upd)
           {
                $data = array('status' => 'inactive', 'date_end' => new Zend_Db_Expr('NOW()'));
                $this->_db->update(DbTable::TABLE_BIND_CORPORATE_PRODUCT_COMMISSION,$data, "id = '$id'");
                return true;
           }
        }
         
         return false;
     }
     
     public function chkDuplicateCorporateProduct($agentId, $productId)
    {
        $select = $this->_db->select()
                ->from(DbTable::TABLE_BIND_CORPORATE_PRODUCT_COMMISSION)
                ->where("corporate_id=".$agentId)
                ->where("product_id = '".$productId."'");
                //->where("date_start > '".$row['date_start']."' AND (date_end != '0000-00-00' AND date_end < '".$row['date_start']."') || (date_end = '0000-00-00')");
                //->where("'".$row['date_start']."' BETWEEN date_start AND date_end AND date_end != '0000-00-00'");
        //echo $select->__toString();exit;
        $productArr = $this->_db->fetchAll($select);
        if (empty($productArr))
            return TRUE;
        else
            return FALSE;
     }
     
     public function corporateProduct(array $data){
               
        $res = $this->_db->insert(DbTable::TABLE_BIND_CORPORATE_PRODUCT_COMMISSION,$data);
        return $res;
        
        
        
        
    }
    
    public function findProductById($id){
         $select = $this->_db->select()
                 ->from(DbTable::TABLE_BIND_CORPORATE_PRODUCT_COMMISSION)
                 ->where("corporate_id =$id");
                  
        $groupArr = $this->_db->fetchRow($select);
        if (!empty($groupArr))
            return $groupArr;
        else
            return FALSE;
     }
     
     public function checkLastdetails($agentId, $productId)
    {
         $select = $this->_db->select()
                  ->from(DbTable::TABLE_BIND_CORPORATE_PRODUCT_COMMISSION)
                  ->where("corporate_id = ".$agentId)
                  ->where("product_id = ".$productId)
                  ->where("date_end = '0000-00-00'");
        
        $bindArr = $this->_db->fetchRow($select);
        if (!empty($bindArr))
            return $bindArr;
        else
            return FALSE;
     }
     
     public function updagentProduct($id , $startDate){
           
           $data = array(
             //'status' => 'inactive',
            'date_end' => date('Y-m-d', strtotime('-1 day', strtotime($startDate))) );
           
        $update = $this->_db->update(DbTable::TABLE_BIND_CORPORATE_PRODUCT_COMMISSION,$data,"id=$id") ;
        
       
        return $update;
       }
       
     public function getProductById($id){
         $select = $this->_db->select()
                 ->from(DbTable::TABLE_BIND_CORPORATE_PRODUCT_COMMISSION.' as b',array('b.id as bid','b.product_id'))
                 ->joinleft(DbTable::TABLE_PRODUCTS.' as p','b.product_id=p.id',array('p.id as id','p.name as name', 'p.program_type'))
                 ->where("b.id =$id");
          
        $bankArr =  $this->_db->fetchRow($select);
       
        return $bankArr;
     }
     
     public function getCorporateBinding($agentId, $date )
    {
         $select = $this->_db->select()
                 ->from(DbTable::TABLE_BIND_CORPORATE_PRODUCT_COMMISSION.' as b',array('b.corporate_id','b.product_id', 'b.plan_commission_id' ))
                 ->joinleft(DbTable::TABLE_PRODUCTS.' as p','b.product_id=p.id',array('p.id as product_id', 'p.name as product_name', 'p.ecs_product_code as product_code','program_type'))
                 ->joinleft(DbTable::TABLE_BANK.' as bk','p.bank_id=bk.id',array('bk.name as bank_name', 'bk.id as bank_id', 'bk.unicode as bank_unicode'))
                 ->joinleft(DbTable::TABLE_PLAN_COMMISSION.' as pc','b.plan_commission_id=pc.id',array('pc.name as plan_commission_name'))
                 ->joinleft(DbTable::TABLE_COMMISSION_ITEMS.' as ci','ci.plan_commission_id=b.plan_commission_id AND ci.status = "'.STATUS_ACTIVE.'"',array('ci.typecode as typecode', 'ci.txn_flat', 'ci.txn_pcnt', 'ci.txn_min', 'ci.txn_max'));
                       
         if($agentId > 0){
                    $select->where("b.corporate_id = $agentId");
                }
                $select->where("'".$date."' >= b.date_start AND ('".$date."' <= b.date_end OR b.date_end = '0000-00-00')");
//        echo $select;   
        $commArr =  $this->_db->fetchAll($select);
       
        return $commArr;
     }
     
     
       public function getAgentFeeBinding($agentId, $date)
    {
         $select = $this->_db->select()
                 ->from(DbTable::TABLE_BIND_CORPORATE_PRODUCT_COMMISSION.' as b',array('b.corporate_id','b.product_id', 'b.plan_fee_id' ))
                 ->joinleft(DbTable::TABLE_PRODUCTS.' as p','b.product_id=p.id',array('p.name as product_name', 'p.ecs_product_code as product_code'))
                 ->joinleft(DbTable::TABLE_BANK.' as bk','p.bank_id=bk.id',array('bk.name as bank_name'))
                 ->join(DbTable::TABLE_PLAN_FEE.' as pf','b.plan_fee_id=pf.id',array('pf.name as plan_fee_name'))
                 ->join(DbTable::TABLE_FEE_ITEMS.' as fi','fi.plan_fee_id=b.plan_fee_id AND fi.status = "'.STATUS_ACTIVE.'"',array('fi.typecode as typecode', 'fi.txn_flat as txn_flat', 'fi.txn_pcnt as txn_pcnt', 'fi.txn_min as txn_min', 'fi.txn_max as txn_max'));
                       
         if($agentId > 0){
                    $select->where("b.corporate_id = $agentId");
                }
                $select->where("'".$date."' >= b.date_start AND ('".$date."' <= b.date_end OR b.date_end = '0000-00-00')");
       // echo "<br/><br/>".$select->__toString();
           
        $feeArr =  $this->_db->fetchAll($select);
       
        return $feeArr;
     }
     
     public function checkAgentCurrentProduct($param)
    {        
        $mobile = isset($param['mobile_number'])?$param['mobile_number']:'';
        $agentId = isset($param['corporate_id'])?$param['corporate_id']:'';
        $productId = isset($param['product_id'])?$param['product_id']:'';
        $cuDate = NEW Zend_Db_Expr('NOW()');
        
        if(empty($mobile) || $agentId<1 || $productId<1) {
            throw new Exception("Input Data not found");
        }
        
        if(strlen($mobile) <> Mobile::$length || !is_numeric($mobile)) {
            throw new Exception("Invalid Mobile Number");
        }
      
            //$select = $this->select()
            $select = $this->_db->select();
            $select->from(DbTable::TABLE_BIND_CORPORATE_PRODUCT_COMMISSION.' as bapc',array('bapc.id'));
            ///$select->joinLeft("t_bind_agent_product_commission as bapc", "ch.id = bapc.corporate_id AND bapc.product_id=$productId AND ".$cuDate." >= bapc.date_start AND (".$cuDate." <= bapc.date_end OR bapc.date_end = '0000-00-00')", array('bapc.id as bapc_id'));                    
            $where = "bapc.corporate_id = '".$agentId."' AND bapc.product_id = '".$productId."'"; 
            $where .= " AND bapc.date_start <= ".$cuDate." AND (bapc.date_end >=".$cuDate." OR bapc.date_end = '0000-00-00')";
            $select->where($where);
            
           //echo $select->__toString(); exit;
           $rs = $this->_db->fetchRow($select);
                      
            if(empty($rs)) {
                return false;
            } else {
                return true;
            }
            
    }
    
    
    
    /*
     * checkAgentProductForCurrentDate() will check agent product allowed with him for current date
     */
     public function checkAgentProductForCurrentDate($agentId, $productId)
     {
         $curdate = new Zend_Db_Expr('NOW()');
         $select = $this->_db->select()
                ->from(DbTable::TABLE_BIND_CORPORATE_PRODUCT_COMMISSION." as a")
                ->where("a.corporate_id = ?", $agentId)
                ->where("a.product_id = ?",$productId)
                ->where("$curdate >= a.date_start AND ($curdate <= a.date_end OR a.date_end = '0000-00-00' OR a.date_end is NULL)");
         //echo $select->__toString();exit;
          $resp = $this->_db->fetchRow($select);
          
          if(empty($resp))
              return false;
          else 
              return true;
     }
     
     
     
     /*
     * getAgentProductForCurrentDate() will find n return agent product allowed with him for current date
     */
     public function getCorporateProductForCurrentDate($agentId)
     {
         $curdate = new Zend_Db_Expr('NOW()');
         $select = $this->_db->select()
                ->from(DbTable::TABLE_BIND_CORPORATE_PRODUCT_COMMISSION." as a", array('product_id'))
                ->where("a.corporate_id = ?", $agentId)
                ->where("$curdate >= a.date_start AND ($curdate <= a.date_end OR a.date_end = '0000-00-00' OR a.date_end is NULL)");
         //echo $select->__toString();exit;
          $resp = $this->_db->fetchRow($select);
          
          return $resp;
     }
     
     
      /*
     * getAgentProductForCurrentDate() will find n return agent product allowed with him for current date
     */
     public function getCorporateProductAndBank($corporateId)
     {
         $curdate = date('Y-m-d');
         
         $select = $this->_db->select();
         $select->from(DbTable::TABLE_BIND_CORPORATE_PRODUCT_COMMISSION." as bind", array('bind.product_id'));
         $select->joinLeft(DbTable::TABLE_PRODUCTS.' as p', "bind.product_id = p.id", array('p.bank_id'));
         $select->joinLeft(DbTable::TABLE_BANK.' as b', "p.bank_id= b.id", array('b.name as bank_name','b.id as bank_id'));
         $select->where("bind.corporate_id = ?", $corporateId);
         $select->where("'$curdate' >= bind.date_start AND ('$curdate' <= bind.date_end OR bind.date_end = '0000-00-00' OR bind.date_end is NULL)");
         $resp = $this->_db->fetchRow($select);
        
          return $resp;
     }

     
    /**
     * getAgentProduct
     * Get Agent Product irrespect its active or not,
     * Used in Partner portal when Super agent assign Product to sub agent
     * @param type $agentId
     * @param type $productId
     * @return type
     */  
     public function getCorporateProduct($agentId, $productId)
    {
        $select = $this->_db->select()
                ->from(DbTable::TABLE_BIND_CORPORATE_PRODUCT_COMMISSION)
                ->where("corporate_id=".$agentId)
                ->where("product_id = '".$productId."'");
                //->where("date_start > '".$row['date_start']."' AND (date_end != '0000-00-00' AND date_end < '".$row['date_start']."') || (date_end = '0000-00-00')");
                //->where("'".$row['date_start']."' BETWEEN date_start AND date_end AND date_end != '0000-00-00'");
        //echo $select->__toString();exit;
        return $this->_db->fetchRow($select);
    }
       
}