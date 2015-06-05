<?php

/**
 * Model that manages mcc master & bind purse mccs
 * 
 * @package Operation_Models
 * @copyright transerv
 */
class TidMaster extends App_Model {

    /**
     * Holds the table's name
     *
     * @var string
     * @access protected
     */
    protected $_name = DbTable::TABLE_TID_MASTER;

    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    //protected $_rowClass = 'App_Table_Privilege';

    /* get Mcc Codes for purse */
    public function getPurseTid($purseMasterId = 0)
    {
       if($purseMasterId > 0)
       {
           $select = $this->_db->select()
                ->from(DbTable::TABLE_TID_MASTER . " as tm", array('*'))
                ->joinLeft(DbTable::TABLE_BIND_PURSE_TID . " as bp", "tm.tid = bp.tid_code", array('bp.status as purse_status', 'bp.tid_code'))
                ->where('bp.purse_master_id = ?', $purseMasterId);
                //->where('bp.status = ?', STATUS_ACTIVE);
            $rs = $this->_db->fetchAll($select);      
            return $rs;
       }
       return 0;
    }
   
    public function insertTidMaster($dataArr) {
        $user = Zend_Auth::getInstance()->getIdentity();
       
        if (empty($dataArr))
            throw new Exception('Data missing for add tid');

        $paramChk = array(
            'tid' => $dataArr[0]
        );
        
        $check = $this->checkTidDuplication($paramChk);

        if (!$check) {
              return false;
        }
        else
        {
            $valid = $this->isValid($dataArr);

            if (!$valid) {
                return false;
            } 
        }
        
        $data = array(
            'tid' => $dataArr[0],
            'mid' => $dataArr[1],
            'mcc' => $dataArr[2],
            'me_name' => $dataArr[3],
            'acquire_id' => $dataArr[4],
            'status' => $dataArr[5]
        );

        $this->_db->insert(DbTable::TABLE_TID_MASTER, $data);
        return TRUE;
    }
    
    /*
     * Validates the fields of uploaded file
     */
    public function isValid($param) {
        
        $v = new Validator();
        $tid = isset($param[0]) ? $param[0] : '';
        $mid = isset($param[1]) ? $param[1] : '';
        $mcc = isset($param[2]) ? $param[2] : '';
        $me_name = isset($param[3]) ? $param[3] : '';
        $acquire_id = isset($param[4]) ? $param[4] : '';
        $status = isset($param[5]) ? $param[5] : '';        
        
        if($tid == '' || strlen($tid) < 8 || strlen($tid) > 11 || !(ctype_alnum($tid))){
            return FALSE;
        }

        if($me_name=='' || strlen($me_name) > 30){
            return FALSE;
        }
        
        if($acquire_id != ''){
            if(strlen($acquire_id) != 6 || !(ctype_digit($acquire_id))){
                return FALSE;
            }
        }

        if($status == '' || (strtolower($status) != STATUS_ACTIVE && strtolower($status) != STATUS_INACTIVE)){
            return FALSE;
        }
        
        return TRUE;
    }
    
    public function checkTidDuplication($param) {
        $tid = isset($param['tid']) ? $param['tid'] : '';
        
        $select = $this->_db->select();
        $select->from($this->_name, array('tid'));
        $select->where("tid =? ", $tid);
        //echo $select;exit;
        $rs = $this->_db->fetchRow($select);
        if (empty($rs)) {
            return TRUE;
        } else {
            return FALSE;
        }
        
    }
    
    public function showPendingTidDetails($page = 1, $paginate = NULL, $force = FALSE)
    {
        $user = Zend_Auth::getInstance()->getIdentity();
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_TID_MASTER, array('*'));
        $select->where('tid NOT IN (SELECT tid_code FROM '. DbTable::TABLE_BIND_PURSE_TID .')');

        if($force){
            return $this->_db->fetchAll($select);
        }
        return $this->_paginate($select, $page, $paginate);
    }
    
    public function bindTidToPurse($idArr, $purse_id)
    {
        if (empty($idArr))
            throw new Exception('Data missing for bind tid');

        $masterPurseModel = new MasterPurse();
        $productModel = new Products();

        $pid = $productModel->getProductIDbyConst(PRODUCT_CONST_RAT_CTY);
        $purse = $masterPurseModel->getProductPurseDetails($pid);
        $dataArray = array();
        foreach ($purse as $id => $val) {
            $dataArray[] = $val['id'];
        }

        try {
            $isValid = Util::isValidPurseId($purse_id, $dataArray);
            if(!$isValid){
                throw new Exception('Invalid Purse Id');
            }
            else
            {
                // Foreach selected id value
                foreach ($idArr as $id) {
                    $this->_db->beginTransaction();

                        $paramChk = array(
                            'tid' => $id
                        );
                        $check = $this->checkBindTidDuplication($paramChk);  
                        if ($check) {
                            $data = array(
                                'purse_master_id' => $purse_id,
                                'tid_code' => $id,                                
                                'status' => STATUS_ACTIVE,
                            );

                            $this->_db->insert(DbTable::TABLE_BIND_PURSE_TID, $data);
                        }
                    $this->_db->commit();
                }// END of foreach loop
            }
        } catch (Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_db->rollBack();
            throw new Exception($e->getMessage());
        }
        return TRUE;
    }
    
    public function checkBindTidDuplication($param)
    {
        $tid = isset($param['tid']) ? $param['tid'] : '';
        
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_BIND_PURSE_TID, array('id'));
        $select->where("tid_code =? ", $tid);
        $rs = $this->_db->fetchRow($select);
        if (empty($rs)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    public function changestatus($idArr, $status)
    {
        if (empty($idArr))
            throw new Exception('Data missing for tid');
        
        if (empty($status)){
            throw new Exception('Missing Status');
        }
        
        try {
            // Foreach selected id value
            foreach ($idArr as $id) {
                $data = array(
                    'status' => $status,
                );

                $this->_db->update(DbTable::TABLE_BIND_PURSE_TID, $data, "tid_code='".$id."'");
            }// END of foreach loop
        } catch (Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            throw new Exception($e->getMessage());
        }
        return TRUE;
    }
}