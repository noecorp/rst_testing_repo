<?php
/**
 * Manages the Unicode
 *
 * @package Unicode
 * @copyright transerv
 */

class AfnNumber extends App_Model
{
    /**
     * Column for the primary key
     *
     * @var string
     * @access protected
     */
    protected $_primary = 'afn_no';
    
    /**
     * Holds the table's name
     *
     * @var string
     * @access protected
     */
    protected $_name = DbTable::TABLE_AFN_NUMBER;
    
    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    //protected $_rowClass = 'App_Table_Group';
    
    public $_TXN_TYPE;
    private $_ID;
    private $_REF_NUM;
    
    /**
     * 
     * @access public
     * @return array
     */
    public function generateTxncode($initial='',$length=''){
        if($this->setupTxncode($initial,$length) === true) {
            $this->saveTxncode();
            return true;
        } 
        return false;
   }
    
    /**
     * setupUnicode
     * Setup Unicode used to generate unique unicode
     * @return boolean
     * @throws Exception
     */
    private function setupTxncode($initial='',$length='')
    {
        unset($this->_ID);
        if(empty($initial)) {
            $this->_ID = mt_rand('1000000000','9999999999');            
        } else {
            $min = '10000';
            $max = '99999';
            $r = mt_rand($min, $max);
            $min = '1000';
            $max = '9999';
            $r2 = mt_rand($min, $max);
            //$min = $initial.'0000000000';
            //$max = $initial.'9999999999';
            $this->_ID = $initial.$r . $r2;
            //echo substr($min,0,10) . ' : ' . substr($max,0,10) . '<br />';
            //$this->_ID = (float) mt_getrandmax(substr($min,0,10),substr($max,0,10));
            //echo $this->_ID.'==<br />';
            //echo $initial .$this->genrate_random(10);
        }
        if($this->validateGeneratedTxncode() === false) {
            self::setupTxncode($initial='',$length='');
        }
        return true;
    }
    
    /**
     * validateGeneratedUnicode
     * Validate Generated Unicode into DB, This will help to make it unique
     * @return boolean
     */
    private function validateGeneratedTxncode() {
        $unicodeData = $this->fetchRow($this->select()
                                ->where(" afn_no = '".$this->_ID."' ")
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
    private function saveTxncode() {
        $this->insert(array(
                'afn_no'  => $this->_ID,
                'status'   => STATUS_FREE,
                'date_added'  => new Zend_Db_Expr('NOW()'),
             )
          );
    }

   
    /**
     * setUsedStatus
     * Method to set UNICODE Status as Used
     * @return type
     */
    public function setUsedStatus(){
          $this->update(array(
                'status'   => STATUS_USED,
             ),
                  " afn_no ='".$this->_ID . "'"
          );
    }
    
     public function generateNSDCRefNum($length=''){
        if($this->setupNSDCRefNum($length) === TRUE) {
            $this->saveNSDCRefNum();
            return TRUE;
        } 
        return FALSE;
   }
     private function setupNSDCRefNum($length='')
    {
        unset($this->_REF_NUM);
       
            $min = '10000';
            $max = '99999';
            $r = mt_rand($min, $max);
            $min = '1000';
            $max = '9999';
            $r2 = mt_rand($min, $max);
            $this->_REF_NUM = '7'.$r . $r2;
           
        if($this->validateGeneratedNSDCRefNum() === FALSE || $this->validateFirstDigit() === FALSE) {
            self::setupNSDCRefNum($length='');
        }
        return TRUE;
    }
    private function saveNSDCRefNum() {
        $this->insert(array(
                'afn_no'  => $this->_REF_NUM,
                'status'   => STATUS_FREE,
                'date_added'  => new Zend_Db_Expr('NOW()'),
             )
          );
    }

     private function validateGeneratedNSDCRefNum() {
        $unicodeData = $this->fetchRow($this->select()
                                ->where(" afn_no = '".$this->_REF_NUM."' ")
        );
        if(!empty($unicodeData)) {
            return FALSE;
        }
        return TRUE;
    }
    
     private function validateFirstDigit() {
        $firstDigit = substr($this->_REF_NUM, 0,1);
        
        if($firstDigit != '7') {
            return FALSE;
        }
        return TRUE;
    }
     public function getNSDCRefNum() {
        if(isset($this->_REF_NUM) && $this->_REF_NUM != '') {
            return $this->_REF_NUM;
        }
    }
    
     public function setNSDCRefNum($refNum = '') {
        if(!isset($this->_REF_NUM) || $this->_REF_NUM == '') {
            $this->_REF_NUM = $refNum;
        }
    }
 
    
     public function setNSDCRefNumUsedStatus(){
          $this->update(array(
                'status'   => STATUS_USED,
             ),
                  " afn_no ='".$this->_REF_NUM . "'"
          );
    }
}