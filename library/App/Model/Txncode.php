<?php
/**
 * Manages the Unicode
 *
 * @package Unicode
 * @copyright transerv
 */

class Txncode extends App_Model
{
    /**
     * Column for the primary key
     *
     * @var string
     * @access protected
     */
    protected $_primary = 'txn_code';
    
    /**
     * Holds the table's name
     *
     * @var string
     * @access protected
     */
    protected $_name = DbTable::TABLE_TXNCODE;
    
    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    //protected $_rowClass = 'App_Table_Group';
    
    public $_TXN_TYPE;
    private $_ID;
     
    public function generateTxncode() {
        $lastTxnCode = $this->saveTxncode(); 
        if($lastTxnCode != false){
            if($this->setupTxncode($lastTxnCode)){
                return true;
            } else {
                return false;
            }
        }
        return false;
    }

    public function saveTxncode() {
        $data = array(
            'status' => STATUS_FREE,
            'date_added' => new Zend_Db_Expr('NOW()')
        );
        if($this->insert($data)){
            $txnCode = $this->_db->lastInsertId($this->_name, 'txn_code');
            return $txnCode;
        } else {
            return false;
        }
    }
     
    private function setupTxncode($lastTxnCode) {
        unset($this->_ID);
        $this->_ID = $lastTxnCode; 
        return true;
    } 
    /**
     * 
     * @access public
     * @return array
     */
    /* public function generateTxncode(){
        if($this->setupTxncode() === true) {
            $this->saveTxncode();
            return true;
        } 
        return false;
   } /*
    
    /**
     * setupUnicode
     * Setup Unicode used to generate unique unicode
     * @return boolean
     * @throws Exception
     */
    /*private function setupTxncode()
    {
        unset($this->_ID);
        $this->_ID = mt_rand('10000000','99999999');
        if($this->validateGeneratedTxncode() === false) {
            self::setupTxncode();
        }
        return true;
    }*/
    
    /**
     * validateGeneratedUnicode
     * Validate Generated Unicode into DB, This will help to make it unique
     * @return boolean
     */
    private function validateGeneratedTxncode() {
        $unicodeData = $this->fetchRow($this->select()
                                ->from($this->_name, array('txn_code'))
                                ->where(" txn_code = '".$this->_ID."' ")
        );
        if(!empty($unicodeData)) {
            return false;
        }
        return true;
    }
    
    /*
     * getUnicode
     * Function is used to return newly generated UNICODE
     */
    public function getTxncode() {
        if(isset($this->_ID) && $this->_ID != '') {
            return $this->_ID;
        }
    }
    
    /**
     * saveUnicode
     * Method to save Unicode
     */
    /* private function saveTxncode() {
        $this->insert(array(
                'txn_code'  => $this->_ID,
                'status'   => STATUS_FREE,
                'date_added'  => new Zend_Db_Expr('NOW()'),
             )
          );
    } */

   
    /**
     * setUsedStatus
     * Method to set UNICODE Status as Used
     * @return type
     */
    public function setUsedStatus(){
          $this->update(array(
                'status'   => STATUS_USED,
             ),
                  " txn_code ='".$this->_ID . "'"
          );
    }
}