<?php
/**
 * Model that manages the products
 *
 * @package Operation_Models
 * @copyright transerv
 */

class Agentfeeitem extends App_Model
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
    protected $_name = DbTable::TABLE_FEE_ITEMS;
    
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
        $select->from(DbTable::TABLE_TRANSACTION_TYPE,array('typecode','name'))
                ->where("status = 'active'")
                ->order('name');
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
                ->from(DbTable::TABLE_FEE_ITEMS, array("id", "fee_id", "typecode_name", "typecode", "txn_flat", "txn_pcnt",
                    "txn_min", "txn_max", 
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
    
   
    
    public function findFeeitemsbyMasterfee($id, $page = 1, $paginate = NULL, $force = FALSE)
    {
                $details = $this->select()
                ->from(DbTable::TABLE_FEE_ITEMS." as fi", array("fi.*", 
                    "DATE_FORMAT(fi.date_start, '%d-%m-%Y') as date_start_formatted",
                    "DATE_FORMAT(fi.date_end, '%d-%m-%Y') as date_end_formatted"))
                ->setIntegrityCheck(false)
                //->joinLeft('t_fee as f',"fi.fee_id = f.id ",array('f.name as fee_name'))
                ->where("fee_id=?",$id)
                ->where("(date_end = '0000-00-00' OR date_end >= date_start)")
                ->order(array("typecode_name ASC", "date_start ASC"));
               // ->where("fi.status = 'active'");

        return $this->_paginate($details, $page, $paginate);
    }
     
     public function getMasterFeeDetails($masterId)
    {
        $select = $this->_db->select();
        $select->from("t_fee")
                ->where("id=".$masterId)
                ->limit(1);
        return $this->_db->fetchRow($select);
     }
     
     public function updateFeeitem($val)
     {
         $data = array(
             'status' => 'inactive',
            'date_end' => date('Y-m-d', strtotime('-1 day', strtotime($val['date_start']))) );
         $this->update($data, "id = ".$val['id']);
     }
     
    public function getTypecodeDetails($typecode)
    {
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_TRANSACTION_TYPE)
                ->where("typecode='".$typecode."'")
                ->limit(1);
      
        return $this->_db->fetchRow($select);
     }
     
    public function chkDuplicateTypecode($row)
    {
        $select = $this->_db->select()
                ->from(DbTable::TABLE_FEE_ITEMS)
                ->where("fee_id=".$row['fee_id'])
                ->where("typecode = '".$row['typecode']."'");
                //->where("date_start > '".$row['date_start']."' AND (date_end != '0000-00-00' AND date_end < '".$row['date_start']."') || (date_end = '0000-00-00')");
                //->where("'".$row['date_start']."' BETWEEN date_start AND date_end AND date_end != '0000-00-00'");
        //echo $select->__toString();
        $typecodeArr = $this->_db->fetchAll($select);
        if (empty($typecodeArr))
            return TRUE;
        else
            return FALSE;
     }
     
     public function checkDateDuplicacy($param)
    {
        $select = $this->select()
                ->where("fee_id=".$param['fee_id'])
                ->where("typecode = '".$param['typecode']."'")
                //->where("date_start > '".$row['date_start']."' AND (date_end != '0000-00-00' AND date_end < '".$row['date_start']."') || (date_end = '0000-00-00')");
                ->where("'".$param['date_start']."' BETWEEN date_start AND date_end");
        $dateArr = $this->fetchAll($select);
        if ($dateArr->count() == 0)
            return TRUE;
        else
            return FALSE;
     }
}