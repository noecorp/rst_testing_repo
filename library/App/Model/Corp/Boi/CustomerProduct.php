<?php
/**
 * Model that manages the products
 *
 * @package Operation_Models
 * @copyright transerv
 */

class Corp_Boi_CustomerProduct extends Corp_Boi
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
    protected $_name = DbTable::TABLE_BOI_CUSTOMER_PRODUCT;
    
    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    //protected $_rowClass = 'App_Table_Privilege';
    
   
     public function saveCustProduct($params)
     {
         $this->_db->insert(DbTable::TABLE_BOI_CUSTOMER_PRODUCT, $params);
     }
     
     public function updateCustProduct($params, $where)
     {
         $this->_db->update(DbTable::TABLE_BOI_CUSTOMER_PRODUCT, $params, $where);
     }
    
}