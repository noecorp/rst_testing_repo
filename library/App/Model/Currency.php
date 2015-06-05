<?php
/**
 * Model that manages the Currency
 *
 * @package Operation_Models
 * @copyright transerv
 */

class Currency extends App_Model
{
    /**
     * Column for the primary key
     *
     * @var string
     * @access protected
     */
    protected $_primary = 'currency';
    
    /**
     * Holds the table's name
     *
     * @var string
     * @access protected
     */
    protected $_name = DbTable::TABLE_CURRENCY;
    
    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    //protected $_rowClass = 'App_Table_Privilege';
    
    public function getAllCurrencyForDropDown()
    {
        $select = $this->_select();
        $select->setIntegrityCheck(false);
        $currency =  $this->fetchAll($select);
        $dataArray = array();
        $dataArray[''] = "Select Currency";
        foreach ($currency as $id => $val) {
            $dataArray[$val['currency']] = $val['currency_name'].' ('.$val['currency'].')';
        }
        return $dataArray;
  
    }
    
  
}