<?php
/**
 * Model that manages the products
 *
 * @package Operation_Models
 * @copyright transerv
 */

class Agentfee extends App_Model
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
   // protected $_name = 't_fee_table_info';
    protected $_name = 't_fee_info';
    
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
        
    );
    
     
     public function getTypecode()
    {
        $select = $this->select();
        $select->from(DbTable::TABLE_TRANSACTION_TYPE,array('typecode','name'));
        $select->setIntegrityCheck(false);
        $typecodeArr =  $this->fetchAll($select);
        $dataArray = array();
        foreach ($typecodeArr as $id => $val) {
            $dataArray[$val['typecode']] = $val['name'];
        }
        return $dataArray;
  
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
    public function findById($productId, $force = FALSE){
        //$products = parent::findById($productId);
        
        $select = $this->select()
                ->from("t_fee_info", array("id", "fee_id", "currency", "limit_min", "limit_max",
                    "limit_first_load", "load_limit_min", "load_limit_max", 
                    "DATE_FORMAT(date_start, '%d-%m-%Y') as date_start", 
                    "DATE_FORMAT(date_end, '%d-%m-%Y') as date_end", "status") )
                ->where("id = ? ", $productId);
       $products = $this->fetchRow($select);
       
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
    
    public function findAllbyMasterfee($id, $page = 1, $paginate = NULL, $force = FALSE)
    {
                /*$select = $this->select()
                    ->where("fee_id=?",$id);
                        //->fetchAll();
                 */    
                
                $details = $this->select()
                ->from("t_fee_info as fi")
                ->setIntegrityCheck(false)
                ->joinLeft('t_fee as f',"fi.fee_id = f.id ",array('f.name as fee_name'))
                ->where("fee_id=?",$id);

        return $this->_paginate($details, $page, $paginate);
    }
    
    public function findFeeinfobyMasterfee($id, $page = 1, $paginate = NULL, $force = FALSE)
    {
                $select = $this->select()
                ->from("t_fee_info as fi", array("fi.*", 
                    "DATE_FORMAT(fi.date_start, '%d-%m-%Y') as date_start_formatted",
                    "DATE_FORMAT(fi.date_end, '%d-%m-%Y') as date_end_formatted"))
                ->setIntegrityCheck(false)
                //->joinLeft('t_fee as f',"fi.fee_id = f.id ",array('f.name as fee_name'))
                ->where("fee_id=?",$id)
                ->where("(date_end = '0000-00-00' OR date_end >= date_start)")
                ->order("date_start");
//                $select = $this->select()
//                ->from("t_fee_info as fi", array("fi.*", 
//                    "DATE_FORMAT(fi.date_start, '%d-%m-%Y') as date_start_formatted",
//                    "DATE_FORMAT(fi.date_end, '%d-%m-%Y') as date_end_formatted"))
//                ->setIntegrityCheck(false)
//                ->joinLeft('t_fee as f',"fi.fee_id = f.id ",array('f.name as fee_name'))
//                ->where("fee_id=?",$id)
//                ->where("(date_end = '0000-00-00' OR date_end >= date_start)")
//                ->order("date_start");
               // ->where("fi.status = 'active'")
               // ->limit(1);
               // echo $select->__toString();exit;
        return $this->_paginate($select, $page, $paginate);
    }
     
     public function getMasterFeeDetails($masterId)
    {
        $select = $this->_db->select();
        $select->from("t_fee")
                ->where("id=".$masterId)
                ->limit(1);
      
        return $this->_db->fetchRow($select);
     }
     
     public function updateFeeinfo($val)
     {
         $data = array(
             'status' => 'inactive',
            'date_end' => date('Y-m-d', strtotime('-1 day', strtotime($val['date_start']))) );
         $this->update($data, "id = ".$val['id']);
     }
     
     
     
     public function getAgentFeeDetails($param){
         if($param['agent_id']=='' || $param['product_id']==''){
             throw new Exception('Insufficient Agent Data');
         }
         
          $select = $this->select()
                ->from("t_agent_products as ap")
                ->setIntegrityCheck(false)
                ->joinLeft('t_fee as f',"ap.fee_id = f.id ")
                ->joinLeft('t_fee_info as limits',"f.id = limits.fee_id ")
                ->where("ap.agent_id=?",$param['agent_id'])
                ->where("f.product_id=?",$param['product_id'])
                ->limit(1);  
         
          return $this->_db->fetchRow($select);
     }
     
     public function checkDateDuplicacy($param){
         $select = $this->select()
                 ->where("fee_id = ".$param['fee_id'])
                 ->where("'".$param['date_start']."' BETWEEN date_start AND date_end");
        $dateArr = $this->fetchAll($select);
        if ($dateArr->count() == 0)
            return TRUE;
        else
            return FALSE;
     }
     
     
     
}