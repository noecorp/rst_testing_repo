<?php

class ForgotPassword extends BaseUser
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
    protected $_name = DbTable::TABLE_LOG_FORGOT_PASSWORD;
    
    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    public function updateActiveRecords(){
         $updateLogArr = array('date_modified' => new Zend_Db_Expr('NOW()'),'status'=> STATUS_INACTIVE);
        
         $update = $this->update($updateLogArr, "status='".STATUS_ACTIVE."'");
        
        return $update;
       
    }
    
    
     
    
    
}