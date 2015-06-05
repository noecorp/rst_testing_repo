<?php
/**
 * Model that manages the Currency
 *
 * @package Operation_Models
 * @copyright transerv
 */

class FundTransferType extends App_Model
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
    protected $_name = DbTable::TABLE_FUND_TRANSFER_TYPE;
    
    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    //protected $_rowClass = 'App_Table_Privilege';
    
    public function getFundTransferTypeForDropDown()
    {
        $select = $this->_select();
        //$select->from("t_product_master",array('id','name'));
        $select->setIntegrityCheck(false);
        $ftTypes =  $this->fetchAll($select);
        //echo "<pre>";print_r($currency);exit;
        $dataArray = array();
        $dataArray[''] = "Select Fund Transfer Type";
        foreach ($ftTypes as $id => $val) {
            $dataArray[$val['id']] = $val['name'];
        }
        return $dataArray;
  
    }
    
     public function getFundTransferTypes($page = 1, $paginate = NULL){        
            
                   $select =   $this->select()
                    ->setIntegrityCheck(false)
                    ->from(DbTable::TABLE_FUND_TRANSFER_TYPE.' as ftt',array('ftt.*',"DATE_FORMAT(ftt.date_created, '%d-%m-%Y %h:%i:%s') as date_created"))
                    ->joinLeft(DbTable::TABLE_OPERATION_USERS.' as ou',"ftt.by_ops_id=ou.id",array('username as ops_name'))                    
                    ->order('ftt.name ASC');
            // echo $select->__toString();exit;        
        
        return $this->_paginate($select, $page, $paginate);       
    }
    
    
  
}