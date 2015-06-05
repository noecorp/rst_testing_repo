<?php
/**
 * Model that manages the flags (controller names) for defining
 * the Flags in the application
 *
 * @package backoffice_models
 * @copyright company
 */

class Mobile extends App_Model
{

    public static $length = 10;
    

    /**
     * Returns an array with all resources and their associated
     * privileges
     * 
     * @access public
     * @return array
     */
    public static function getCountryCodes(){
       return array(
           '+91' => '+91',
       );
    }
    
    
    public function checkDuplicate($mobile,$tablename='agent')
    {
           
        if(strlen($mobile) <> Mobile::$length || !is_numeric($mobile)) {
            throw new Exception("Invalid mobile number");
        }
        
        $tabName = $this->getTableName($tablename);        
        $checkDate = '';
        
        if($tabName!='') {
            
            $select = $this->_db->select();
            $select->from($tabName);
            //$select->where('mobile_number=?',$mobile);
            
            if($tablename=='rat_remitters'){
                $where = 'mobile="'.$mobile.'"';
            }else{
                $where = 'mobile_number="'.$mobile.'"';
            }
            
            
            if($tablename=='cardholder'){
                $where .= ' AND (enroll_status = "'.ENROLL_APPROVED_STATUS.'" OR (enroll_status="'.STATUS_CLOSED.'" AND close_datetime > DATE_SUB(CURDATE(),INTERVAL '.NUMBER_OF_MONTHS.' MONTH)))';
            }
            
            
            if($tablename=='rat_remitters'){
                $where .= " AND status = '".STATUS_ACTIVE."' AND remitterid IS NOT NULL AND remitterid>0";
            }
            
            $select->where($where);
           
            
           $rs = $this->_db->fetchRow($select);
           
                     
            if(empty($rs)) {
                return true;
            } else {
                
                if($tablename=='rat_remitters'){
                    throw new Exception("Mobile number already exists");
                }else{
             
                    if($rs['close_datetime'] != '0000-00-00 00:00:00'){
                       //Getting close date
                    $closeDate = date("Y-m-d",strtotime($rs['close_datetime']));
                      //Add NUMBER_OF_MONTHS to $closedate
                    $dateMonthAdded = strtotime(date("Y-m-d", strtotime($closeDate)) . "+".NUMBER_OF_MONTHS." month");
                      // date after which the cardhoder can retry 
                    $retryDate = date('d-m-Y', $dateMonthAdded);
                    throw new Exception("Axis Bank Shmart!Pay Prepaid Card with same mobile number has been closed recently. You are requested to try after $retryDate");

                    }
                    else{
                       throw new Exception("Axis Bank Shmart!Pay Prepaid Card with same mobile number exists."); 
                    }
                
                }
            }
            
        }
    }
    
    
    public function getTableName($moduleName){       
        
        $tableNames = array('agent'=>DbTable::TABLE_AGENTS, 
                            'cardholder'=>DbTable::TABLE_CARDHOLDERS,
                            'cardholder_details'=>DbTable::TABLE_CARDHOLDER_DETAILS,
                            'rat_remitters'=>DbTable::TABLE_RATNAKAR_REMITTERS
                           );
        $moduleName = strtolower($moduleName);
        
        return isset($tableNames[$moduleName])?$tableNames[$moduleName]:'';
    }
    
    
    public function checkExist($param,$tablename='cardholder')
    {        
        $mobile = isset($param['mobile_number'])?$param['mobile_number']:'';
        $agentId = isset($param['agent_id'])?$param['agent_id']:'';
       
        if(empty($mobile)) {
            throw new Exception("Input Data not found");
        }
        
        if(strlen($mobile) <> Mobile::$length || !is_numeric($mobile)) {
            throw new Exception("Invalid Mobile Number");
        }
        
        $tabName = $this->getTableName($tablename);        
      
        if($tabName!='') {
            
                    $select = $this->_db->select();
                    $select->from($tabName);
                    $select->where('mobile_number=?',$mobile);
                    
                    if($agentId>0){
                        $select->where('reg_agent_id=?',$agentId);
                    }
                    //echo $select->__toString(); exit;
           $rs = $this->_db->fetchRow($select);
                      
            if(empty($rs)) {
                return false;
            } else {
                return true;
            }
            
        }
    }
    
    /* checkRemitterMobileDuplicate() will cross check for mobile duplicacy of remitter
     * it will expect mobile number of remitter
     */
    /*
    public function checkRemitterMobileDuplicate($mobile)
    {
           
        if(strlen($mobile) <> Mobile::$length || !is_numeric($mobile)) {
            throw new Exception("Invalid mobile number");
        }
            
            $select = $this->_db->select();
            $select->from(DbTable::TABLE_REMITTERS);
            //$where = 'mobile="'.$mobile.'" AND status !="'.STATUS_PENDING.'"';
            $where = 'mobile="'.$mobile.'" AND (status ="'.STATUS_ACTIVE.'" OR status ="'.STATUS_INACTIVE.'")';
            $select->where($where);
            //echo $select; exit;
            $rs = $this->_db->fetchRow($select);
            
            if(empty($rs)) {
                return true;
            } else {
                    throw new Exception("Remitter with same mobile number exists."); 
            }
     }*/
    
	public function checkRemitterMobileDuplicate($mobile)
    {
           
        if(strlen($mobile) <> Mobile::$length || !is_numeric($mobile)) {
            throw new Exception("Invalid mobile number");
        }
            
            $select = $this->_db->select();
            $select->from(DbTable::TABLE_REMITTERS);
            //$where = 'mobile="'.$mobile.'" AND status !="'.STATUS_PENDING.'"';
            $where = 'mobile="'.$mobile.'"';
            $select->where($where);
            //echo $select; exit;
            $rs = $this->_db->fetchRow($select);
            
            if(empty($rs)) {
                return true;
            } else {
                if($rs['status'] == STATUS_ACTIVE){
                    throw new Exception("Remitter with same mobile number exists."); 
                }else{
                    throw new Exception("Remitter is blocked.");  
                }
            }
     }
    
   
     
     /* checkCorpCardholderMobileDuplicate() will cross check for mobile duplicacy of corp cardholder
     * it will expect mobile number of cardholder
     */
    
    public function checkCorpCardholderMobileDuplicate($mobile)
    {
           
        if(strlen($mobile) <> Mobile::$length || !is_numeric($mobile)) {
            throw new Exception("Invalid mobile number");
        }
            
            $select = $this->_db->select();
            $select->from(DbTable::TABLE_RAT_CUSTOMER_MASTER, array('id'));
            //$where = 'mobile="'.$mobile.'" AND status !="'.STATUS_PENDING.'"';
            $where = 'mobile="'.$mobile.'" AND (status ="'.STATUS_ACTIVE.'" OR status ="'.STATUS_INACTIVE.'")';
            $select->where($where);
            $rs = $this->_db->fetchRow($select);
           
            if(empty($rs)) {
                return true;
            } else {
                    throw new Exception("Cardholder with same mobile number exists."); 
            }
     }
     
       
    /* checkKotakRemitterMobileDuplicate() will cross check for mobile duplicacy of remitter
     * it will expect mobile number of remitter
     */
    
    public function checkKotakRemitterMobileDuplicate($mobile)
    {
           
        if(strlen($mobile) <> Mobile::$length || !is_numeric($mobile)) {
            throw new Exception("Invalid mobile number");
        }
            
            $select = $this->_db->select();
            $select->from(DbTable::TABLE_KOTAK_REMITTERS);
            //$where = 'mobile="'.$mobile.'" AND status !="'.STATUS_PENDING.'"';
            $where = "mobile='".$mobile."' AND (status ='".STATUS_ACTIVE."' OR status ='".STATUS_INACTIVE."')";
            $select->where($where);
            $rs = $this->_db->fetchAll($select);
//           echo '<pre>';print_r($rs);exit('hhhh');
            if(empty($rs)) {
                return true;
            } else {
                    throw new Exception("Remitter with same mobile number exists."); 
            }
     }
     
     /**
      *========== Start Code: Ratnakar Remitter's Mobile Duplicate Checker =============**  
      */
      public function checkRatnakarRemitterMobileDuplicate($mobile)
    {
           
        if(!is_numeric($mobile) || strlen($mobile) != self::$length) {
            throw new Exception("Invalid mobile number");
        }
            
            $select = $this->_db->select();
            $select->from(DbTable::TABLE_RATNAKAR_REMITTERS);
            //$where = 'mobile="'.$mobile.'" AND status !="'.STATUS_PENDING.'"';
            $where = "mobile='".$mobile."'";
            
            $where .= " AND status = '".STATUS_ACTIVE."'  OR status ='".STATUS_INACTIVE."' AND remitterid IS NOT NULL AND remitterid>0";
            
            $select->where($where);
            $rs = $this->_db->fetchRow($select);
 //          echo '<pre>';print_r($rs);exit('hhhh');
            if(empty($rs)) {
                return true;
            } else {
                        if($rs['status'] == STATUS_ACTIVE){
                        throw new Exception("Remitter with same mobile number exists."); 
                        }else{
                        throw new Exception("Remitter is blocked.");  
                        } 
            }
     }
     
     public function checkRatnakarRemitterMobileDuplicateNew($mobile,$agentid)
    {

        if(!is_numeric($mobile) || strlen($mobile) != self::$length) {
            throw new Exception("Invalid mobile number");
        }

            $select = $this->_db->select();
            $select->from(DbTable::TABLE_RATNAKAR_REMITTERS);
            //$where = 'mobile="'.$mobile.'" AND status !="'.STATUS_PENDING.'"';
            //$where = "mobile='".$mobile."' AND by_agent_id='".$agentid."'";
            $where = "mobile='".$mobile."'";
           // $where .= " AND status = '".STATUS_ACTIVE."'";
            
	     $select->where($where);
            $rs = $this->_db->fetchRow($select);
            if(empty($rs)) {
                return -1;
            } else {
		/*
		if($rs['status'] == STATUS_ACTIVE){
                        throw new Exception("Remitter with same mobile number exists.");
                        }else if($rs['status'] == STATUS_INACTIVE){
                                return false;
                        } else{
                          throw new Exception("Remitter is blocked.");
                        }
		*/
		/*if( isset($rs['remitterid']) && $rs['remitterid'] != '' ){
			return 0;
		}else{ */
			return $rs['id'];
		//}

            }
     }

     
     /*
      *========== End Code: Ratnakar Remitter's Mobile Duplicate Checker =============** 
      */
 
     public function checkKotakRemitterMobileDuplicate1($mobile)
    {
           
        if(strlen($mobile) <> Mobile::$length || !is_numeric($mobile)) {
            throw new Exception("Invalid mobile number");
        }
            
            $select = $this->_db->select();
            $select->from(DbTable::TABLE_KOTAK_REMITTERS);
            //$where = 'mobile="'.$mobile.'" AND status !="'.STATUS_PENDING.'"';
            $where = "mobile='".$mobile."' AND (status ='".STATUS_ACTIVE."' OR status ='".STATUS_INACTIVE."')";
            $select->where($where);
            $rs = $this->_db->fetchAll($select);
//           echo '<pre>';print_r($rs);
            if(empty($rs)) {
                return true;
            } else {
                return false;
            }
     }
     
      /*
      *========== Start Code: Ratnakar Remitter's Mobile Duplicate 1 Checker =============** 
      */
     
     
/*      public function checkRatnakarRemitterMobileDuplicate1($mobile)
    {
           
        if(strlen($mobile) <> Mobile::$length || !is_numeric($mobile)) {
            throw new Exception("Invalid mobile number");
        }
            
            $select = $this->_db->select();
            $select->from(DbTable::TABLE_RATNAKAR_REMITTERS);
            //$where = 'mobile="'.$mobile.'" AND status !="'.STATUS_PENDING.'"';
            $where = "mobile='".$mobile."' AND (status ='".STATUS_ACTIVE."' OR status ='".STATUS_INACTIVE."')";
            $select->where($where);
            $rs = $this->_db->fetchAll($select);
//           echo '<pre>';print_r($rs);
            if(empty($rs)) {
                return true;
            } else {
                return false;
            }
     }*/
	public function checkRatnakarRemitterMobileDuplicate1($mobile)
    {
           
        if(strlen($mobile) <> Mobile::$length || !is_numeric($mobile)) {
            throw new Exception("Invalid mobile number");
        }
            
            $select = $this->_db->select();
            $select->from(DbTable::TABLE_RATNAKAR_REMITTERS);
            //$where = 'mobile="'.$mobile.'" AND status !="'.STATUS_PENDING.'"';
            $where = "mobile='".$mobile."'";
            $select->where($where);
            $rs = $this->_db->fetchRow($select);
 //          echo '<pre>';print_r($rs);exit('hhhh');
            if(empty($rs)) {
                return true;
            } else {
                        if($rs['status'] == STATUS_ACTIVE){
                        throw new Exception("Remitter with same mobile number exists."); 
                        }else{
                        throw new Exception("Remitter is blocked.");  
                        } 
            }
     }
     /*
      *========== End Code: Ratnakar Remitter's Mobile Duplicate 1 Checker =============** 
      */
     

    public function checkKotakCardholderMobileDuplicate($mobile,$product_id)
    {
           
        if(strlen($mobile) <> Mobile::$length || !is_numeric($mobile)) {
            throw new Exception("Invalid mobile number");
        }
            
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_KOTAK_CORP_CARDHOLDER, array('id'));
        //$where = 'mobile="'.$mobile.'" AND status !="'.STATUS_PENDING.'"';
        $where = 'mobile="'.$mobile.'" AND product_id = "'.$product_id.'" AND status IN("'.STATUS_PENDING.'","'.STATUS_ACTIVE.'","'.STATUS_ECS_PENDING.'")';
        $select->where($where);
        //echo $select; exit;
        $rs = $this->_db->fetchRow($select);
       
        if(empty($rs)) {
            return true;
        } else {
            throw new Exception("Cardholder with same mobile number exists.");
            return false;
        }
     }
     
     /* checkCorpCardholderMobileDuplicate() will cross check for mobile duplicacy of corp cardholder
     * it will expect mobile number of cardholder
     */
    
    public function checkRatCardholderMobileDuplicate($mobile,$productId=0)
    {
           
        if(strlen($mobile) <> Mobile::$length || !is_numeric($mobile)) {
            throw new Exception("Invalid mobile number");
        }
            
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_RAT_CORP_CARDHOLDER, array('id'));
        $where = 'mobile="'.$mobile.'" AND (status ="'.STATUS_ACTIVE.'" OR status ="'.STATUS_INACTIVE.'")';
        if($productId){
            $where = $where." AND product_id=".$productId;
        }
        $select->where($where);
        $rs = $this->_db->fetchRow($select);
       
        if(empty($rs)) {
            return true;
        } else {
                throw new Exception("Cardholder with same mobile number exists."); 
        }
     }     
}
