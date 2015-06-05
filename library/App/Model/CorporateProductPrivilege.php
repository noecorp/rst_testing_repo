<?php
/**
 * Model that manages the Product Privleges
 *
 * @package Operation_Models
 * @copyright transerv
 */

class CorporateProductPrivilege extends App_Model
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
    protected $_name = DbTable::TABLE_CORPORATE_PRODUCT_PRIVILEGES;
    
    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    //protected $_rowClass = 'App_Table_Flipper';    
 
    /**
     * Define the relationship with another tables
     *
     * @var array
     */
    protected $_referenceMap = array(
        'Flag' => array(
            'columns' => 'flag_id',
            'refTableClass' => 'Flag',
            'refColumns' => 'id'
        ),
        'Privilege' => array(
            'columns' => 'privilege_id',
            'refTableClass' => 'Privilege',
            'refColumns' => 'id'
        ),
    );  
    
    
    
      /**
     * Finds all Product Privileges by Agent id
     * 
     * @param int $agentId 
     * @access public
     * @return void
     */
    public function findByCorporateId($corporateId){
        $curdate = date("Y-m-d");
        $select = $this->_select()
                    ->setIntegrityCheck(FALSE)
                    ->join(DbTable::TABLE_CORPORATE_USER.' as a','a.id = "'.$corporateId.'"')
                    ->join(DbTable::TABLE_BIND_CORPORATE_PRODUCT_COMMISSION.' as ba', "ba.corporate_id = a.id AND '".$curdate ."' >= ba.date_start AND ('".$curdate."' <= ba.date_end OR ba.date_end = '0000-00-00' OR ba.date_end is NULL)")
                    ->join(DbTable::TABLE_PRIVILEGES.' as p', $this->_name.'.privilege_id = p.id', 'p.name as privilege_name')
                    ->join(DbTable::TABLE_FLAGS.' as f', $this->_name.'.flag_id = f.id', 'f.name as flag_name')
                    ->where($this->_name.'.product_id = ba.product_id')
                    ->where('a.enroll_status =?',STATUS_APPROVED);
        return $this->fetchAll($select);
    }
}