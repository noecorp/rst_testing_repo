<?php
/**
 * CRN CLASS handles things related to crn
 * @package Core
 * @copyright transerv
 */

class CRN extends App_Model
{

    /**
     * Returns an array with all MVC Types
     * 
     * 
     * @access public static
     * @return array
     */
    /* checkCorpCRNDuplicate() will check corp crn for duplication
     * 
     */
    public function checkCorpCRNDuplicate($crn){
        if(!is_numeric($crn) || !$crn > 0) {
            throw new Exception("Invalid CRN");
        }
            $select = $this->_db->select();
            $select->from(DbTable::TABLE_CUSTOMER_MASTER, array('id'));
            $where = 'shmart_crn="'.$crn.'" AND (status ="'.STATUS_ACTIVE.'" OR status ="'.STATUS_INACTIVE.'")';
            $select->where($where);
            //echo $select; exit;
            $rs = $this->_db->fetchRow($select);
            
            if(empty($rs)) {
                return true;
            } else {
                    throw new Exception("Technical problem exists, cannot perform that action."); 
            }
            
    }
    
    
   
}