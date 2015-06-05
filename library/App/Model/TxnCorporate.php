<?php

class TxnCorporate extends App_Model
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
    protected $_name = DbTable::TABLE_CORPORATE_TXN;
    
    private $_msg;
    
    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    //protected $_rowClass = 'App_Table_CardholderUser';
    
      
    public function getTxnCorporateDaily($corpId, $curDate){
        $select = $this->_db->select()       
                ->from($this->_name, array('count(*) as count', 'sum(amount) as total'))              
                ->where('mode=?',TXN_MODE_DR) 
                ->where("txn_type in ('".TXNTYPE_CARD_RELOAD."', "
                        . "'".TXNTYPE_KOTAK_CORP_CORPORATE_LOAD."' , "
                        . "'".TXNTYPE_RAT_CORP_CORPORATE_LOAD."' )")
                ->where('corporate_id=?',$corpId)
                ->where("txn_status = '".FLAG_SUCCESS."' OR txn_status = '".FLAG_PENDING."'")
                ->where("DATE(date_created) = '".$curDate."'")
                ->group("corporate_id");
        //echo $select->__toString();
        $row = $this->_db->fetchRow($select);      
        return $row;
    }    
    
    public function getTxnCorporateDuration($corpId, $startDate, $endDate){
        $select = $this->_db->select()       
                ->from($this->_name, array('count(*) as count', 'sum(amount) as total'))              
                ->where('mode=?',TXN_MODE_DR) 
                ->where("txn_type in ('".TXNTYPE_CARD_RELOAD."',  "
                        . "'".TXNTYPE_KOTAK_CORP_CORPORATE_LOAD."' , "
                        . "'".TXNTYPE_RAT_CORP_CORPORATE_LOAD."' )")
                ->where('corporate_id=?',$corpId)
                ->where("txn_status = '".FLAG_SUCCESS."' OR txn_status = '".FLAG_PENDING."'")
                ->where("DATE(date_created) BETWEEN '".$startDate."' AND '".$endDate."'")
                ->group("corporate_id");
        //echo $select->__toString();
        $row = $this->_db->fetchRow($select);      
        return $row;
    }    
    
    
   
}