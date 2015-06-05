<?php
/**
 * Model that manages the flags (controller names) for defining
 * the Flags in the application
 *
 * @package backoffice_models
 * @copyright company
 */

class Email extends App_Model
{

    public static $minLength = 5;
    public static $maxLength = 50;        
     
    
    public function checkDuplicate($email, $tablename='cardholder')
    {
           
        if(strlen($email) < Email::$minLength || strlen($email) > Email::$maxLength || $this->invalidEmail($email)) {
            throw new Exception("Invalid email address");
        }
        
        $tabName = $this->getTableName($tablename);        
      
        if($tabName!='') {
            
            $select = $this->_db->select();
                    $select->from($tabName);
                    $select->where('email=?',$email);
                    if($tablename=='cardholder'){
                        $where = " enroll_status = '".STATUS_APPROVED."'";
                        $select->where($where);
                    }
                    
                    if($tablename=='rat_remitters'){
                        $where = " status = '".STATUS_ACTIVE."'";
                        $select->where($where);
                    }
                        
                        $select->limit("1");
           $rs = $this->_db->fetchAll($select);
             
            if(empty($rs)) {               
                return true;
            } else {
                //echo "<pre>";print_r($rs);exit;
                throw new Exception("Email already exists");
            }
            
        }
    }
    
    
    private function getTableName($moduleName){       
        
        $tableNames = array('agent'=>DbTable::TABLE_AGENTS, 
                            'cardholder'=>DbTable::TABLE_CARDHOLDERS,
                            'rat_remitters'=>DbTable::TABLE_RATNAKAR_REMITTERS
                           );
        $moduleName = strtolower($moduleName);
        
        return isset($tableNames[$moduleName])?$tableNames[$moduleName]:'';
    }
    
    private function invalidEmail($email) {
        if (preg_match('|^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$|i', $email)) {
             return false;
        } else return true;               
    }
   
    /*updateEmailVerification()
     * that function will make entry in t_email_verification for any user type
     * will accept email, cardholder_id, agent_id, ops_id, remkars, verification_code, activation_status,datetime_send,datetime_verified
     */
    public function updateEmailVerification($param){
        if(empty($param))
            throw new Exception('No email verification data found');
        
        $email = isset($param['email'])?$param['email']:'';
        if($email=='')
            throw new Exception('No email found');
        
        
        $chResp  = $this->_db->insert(DbTable::TABLE_EMAIL_VERIFICATION, $param);
        return $this->_db->lastInsertId(DbTable::TABLE_EMAIL_VERIFICATION, 'id');
    }
    
    
    /* checkRemitterEmailDuplicate
     * 
     */
     public function checkRemitterEmailDuplicate($email)
    {
           
        if(strlen($email) < Email::$minLength || strlen($email) > Email::$maxLength || $this->invalidEmail($email)) {
            throw new Exception("Invalid email address");
        }
            $where = '(status ="'.STATUS_ACTIVE.'" OR status ="'.STATUS_INACTIVE.'")';
            $select = $this->_db->select();
                      $select->from(DbTable::TABLE_REMITTERS);
                      $select->where('email=?',$email);
                      $select->where($where);
           //echo $select; exit;
            $rs = $this->_db->fetchRow($select);
             
            if(empty($rs)) {               
                return true;
            } else {
                //echo "<pre>";print_r($rs);exit;
                throw new Exception("Email already exists");
            }
            
    }
    
     /* checkCorpCardholderEmailDuplicate() will check email of corp cardholder for duplicacy
     *  as params:- email
     */
     public function checkCorpCardholderEmailDuplicate($email)
     {
           
        if(strlen($email) < Email::$minLength || strlen($email) > Email::$maxLength || $this->invalidEmail($email)) {
            throw new Exception("Invalid email address");
        }
            $where = '(status ="'.STATUS_ACTIVE.'" OR status ="'.STATUS_INACTIVE.'")';
            $select = $this->_db->select();
                      $select->from(DbTable::TABLE_RAT_CUSTOMER_MASTER, array('id'));
                      $select->where('lower(email)=?', strtolower($email));
                      $select->where($where);
            //echo $select; exit;
            $rs = $this->_db->fetchRow($select);
              
            if(empty($rs)) {               
                return true;
            } else {
                //echo "<pre>--";print_r($rs);exit;
                throw new Exception("Email already existed");
            }
            
     }
     
       /* checkKotakRemitterEmailDuplicate
     * 
     */
     public function checkKotakRemitterEmailDuplicate($email)
    {
           
        if(strlen($email) < Email::$minLength || strlen($email) > Email::$maxLength || $this->invalidEmail($email)) {
            throw new Exception("Invalid email address");
        }
            $where = '(status ="'.STATUS_ACTIVE.'" OR status ="'.STATUS_INACTIVE.'")';
            $select = $this->_db->select();
                      $select->from(DbTable::TABLE_KOTAK_REMITTERS);
                      $select->where('email=?',$email);
                      $select->where($where);
           //echo $select; exit;
            $rs = $this->_db->fetchRow($select);
             
            if(!$rs) {               
                return true;
            } else {
                //echo "<pre>";print_r($rs);exit;
                throw new Exception("Email already exists");
            }
            
    }
    
    public function checkRatCardholderEmailDuplicate($email,$productId=0)
    {
           
            $select = $this->_db->select();
            $select->from(DbTable::TABLE_RAT_CORP_CARDHOLDER, array('id'));
            //$where = 'mobile="'.$mobile.'" AND status !="'.STATUS_PENDING.'"';
            $where = 'email="'.$email.'" AND (status ="'.STATUS_ACTIVE.'" OR status ="'.STATUS_INACTIVE.'")';
            if($productId){
                $where = $where." AND product_id=".$productId;
            }
            $select->where($where);
            //echo $select; exit;
            $rs = $this->_db->fetchRow($select);
           
            if(empty($rs)) {
                return true;
            } else {
                    throw new Exception("Cardholder with same email exists."); 
            }
     }
}