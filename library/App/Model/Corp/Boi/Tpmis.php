<?php

/**
 * Model that manages the products
 *
 * @package Operation_Models
 * @copyright transerv
 */
class Corp_Boi_Tpmis extends Corp_Boi {

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
    protected $_name = DbTable::TABLE_REPORT_TPMIS;

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
   
    
    
    public function SaveTPMisGenericDetails($param) {
        
        $user = Zend_Auth::getInstance()->getIdentity();
        $product = isset($param['product_id']) && $param['product_id'] > 0 ? $param['product_id'] : '';
        $tp_mobile = isset($param['tp_mobile']) ? $param['tp_mobile'] : '';
        $agent_mobile = isset($param['agent_mobile']) ? $param['agent_mobile'] : '';
        $tp_code = isset($param['tp_code']) ? $param['tp_code'] : '';
        $agent_code = isset($param['agent_code']) ? $param['agent_code'] : '';
        $wallet_load_from = isset($param['wallet_load_from']) ? $param['wallet_load_from'] : '';
        $wallet_load_to = isset($param['wallet_load_to']) ? $param['wallet_load_to'] : '';
        //$status = isset($param['status']) ? $param['status'] : '';
        $today = date("Y-m-d");
        try{
        $select = $this->select();
        
        if ($tp_mobile != '') {
            $select->where("tp_mobile =? ", $tp_mobile );
        }
        if ($agent_mobile != '') {
            $select->where("agent_mobile =? ", $agent_mobile );
        }
        if ($tp_code != '') {
            $select->where("tp_code =? ", $tp_code );
        }
        
        if ($agent_code != '') {
            $select->where("agent_code =? ", $agent_code );
        }
        if ($wallet_load_from != '' && $wallet_load_to != ''){
            $select->where("wallet_load_from >=  '" . $wallet_load_from . "'");
            $select->where("wallet_load_to <= '" . $wallet_load_to . "'");
           
        }
        $select->where("DATE(date_request) = ?", $today);
        
        //$select->where("status =? ", STATUS_PROCESSED ); 
//        echo $select;
        $result = $this->fetchAll($select);
        $result = $result->toArray();
        $valstatus = '';
        if(!empty($result)){
        
            foreach($result as $val){
                if($val['status'] == 'processed'){
                    return array('status' => 'processed',
                        'rs' => $result);
                } else {
                    if($val['status'] == 'started' || $val['status'] == 'pending'){
                        return array('status' => 'in_process',
                        'rs' => array());
                    }
                    else if($val['status'] == 'failed'){
                        return array('status' => 'failed',
                        'rs' => array());
                    }
                }
            }
        }
              //echo '<pre>';print_r();exit;        
              //echo '<pre>';print_r($result);exit;        
        if(empty($result) ){

            $this->save($param);
            return array('status' => 'submitted',
                        'rs' => array());
        }
        if($valstatus == 'failed'){
              
            $this->save($param);
            return array('status' => 'submitted',
                        'rs' => array());
        }
            
        
        
        }catch (Exception $e) {
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                       
                }
        
    }
    
    public function getGeneratedFileDetails($page = 1){
         $select = $this->select()
                ->setIntegrityCheck(false)
                ->from(DbTable::TABLE_REPORT_TPMIS .' as tpmis', array('tpmis.*'))
                ->joinLeft(DbTable::TABLE_OPERATION_USERS .' as ops',"tpmis.by_ops_id = ops.id", array( 'concat(ops.firstname," ",ops.lastname) as ops_name'))
                ->where("tpmis.status =?", STATUS_PROCESSED )
//                ->where("tpmis.file_name != ''")
                ->order("id desc");

         return $this->_paginate($select, $page, TRUE);

     
         
    }
    
    
}   

