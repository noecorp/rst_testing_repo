<?php
/**
 * Model for HIC Ratnakar Product
 *
 * @author Vikram Singh
 * @package HIC Ratnakar
 * @copyright transerv
 */

abstract class Corp_Ratnakar extends Corp
{
    
    Const PRODUCT_NAME = 'Medi Assist';
    private $_txncode = '';
    
    public function mediAssistIdProofType($typeId){
          
        switch($typeId){
           case '01':
              $str = 'Passport';
              break;
           case '02':
               $str = 'PAN card';
              break;
           case '03':
              $str = 'Aadhar card';
              break;
           case '04':
               $str = 'Driving license';
              break;
           case '05':
             $str = 'Government approved ID card';
              break;
       }
        return $str;
    }
    
     public function mediAssistAddressProofType($typeId){

        switch($typeId){
           case '01':
              $str = 'Passport';
              break;
           case '02':
               $str = 'Bank account statement';
              break;
           case '03':
              $str = 'Electricity bill';
              break;
           case '04':
               $str = 'Ration card';
              break;
           case '05':
             $str = 'Government approved Address Proof';
              break;
       }
        return $str;
    }
    
    public function getPurseInfoByCode($code)
    {
        $sql = $this->_db->select()
                ->from(DbTable::TABLE_PURSE_MASTER)
                ->where("code =?",$code)
                ->where("status= ?",STATUS_ACTIVE);
        $purseInfo = $this->_db->fetchRow($sql);
        if(!empty($purseInfo)) {
            return $purseInfo;
        }
        return array();
    }
    
    public function setTxncode($msg)
    {
        $this->_txncode = $msg;
    }    
    
    public function getTxncode()
    {
        return $this->_txncode;
    }
 
}