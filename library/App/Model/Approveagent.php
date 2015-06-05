<?php
/**
 * Model that manages the products
 *
 * @package Operation_Models
 * @copyright transerv
 */

class Approveagent extends App_Model
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
    protected $_name = DbTable::TABLE_AGENTS;
    
    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    //protected $_rowClass = 'App_Table_Privilege';
    
    /**
     * Define the relationship with another tables
     *
     * @var array
     */
    protected $_referenceMap = array(
        
    );
    
     
    
    
    /**
     * Overrides getAll() in App_Model
     * 
     * @param int $page 
     * @access public
     * @return Zend_Paginator
     */
    public function getAll($page = 1){
        $paginator = $this->fetchAll();
      
        $paginator = Zend_Paginator::factory($paginator);
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage(App_DI_Container::get('ConfigObject')->paginator->items_per_page);
        
        return $paginator;
    }
    
    public function getAgentCode($id){
        $agentcode = $this->select('agent_code')              
                ->where("id=$id");
        $code = $this->fetchRow($agentcode);
        return $code['agent_code'];
                
    }
    
    
     public function getDetails ($page = 1, $paginate = NULL, $force = FALSE){
        
       $details = $this->select()
                 ->from(DbTable::TABLE_AGENTS,array('status','id','email','mobile1','agent_code','concat(first_name," ",last_name) as name',"DATE_FORMAT(reg_datetime,'%d-%m-%Y') as reg_datetime",'reg_datetime as datetime'))
                ->where ("enroll_status ='".STATUS_PENDING."'")
                ->where ("status ='".STATUS_UNBLOCKED."'")
                ->order('datetime ASC');
       //echo $details->__toString();
                
       
       return $this->_paginate($details, $page, $paginate);
        
        
    }
    
    public function getrejectedDetails ($page = 1, $paginate = NULL, $force = FALSE){
        
       $details = $this->select()
                 ->from(DbTable::TABLE_AGENTS,array('status','id','email','mobile1','agent_code','concat(first_name," ",last_name) as name',"DATE_FORMAT(reg_datetime,'%d-%m-%Y') as reg_datetime"))
                ->where ("enroll_status ='".STATUS_REJECTED."'")
                ->where ("status ='".STATUS_UNBLOCKED."'")
                ->order('reg_datetime ASC');
       
                
       
       return $this->_paginate($details, $page, $paginate);
        
        
    }
    

    
    public function getagentproductDetails ($page = 1, $id){
        $paginate = NULL;
        $force = FALSE;
       $details = $this->getagentproductSql($id);
     //echo $details->__toString();
     //exit;
       
       return $this->_paginate($details, $page, $paginate);
       
        
        
    }
      private function getagentproductSql($id = 0){
         $details = $this->select()
                ->from(DbTable::TABLE_BIND_AGENT_PRODUCT_COMMISSION." as bapc",array('bapc.*',"DATE_FORMAT(bapc.date_end, '%d-%m-%Y') as date_end_formatted","DATE_FORMAT(bapc.date_start, '%d-%m-%Y') as date_start_formatted"))
                ->setIntegrityCheck(false)
                ->joinLeft(DbTable::TABLE_AGENTS." as a", "bapc.agent_id=a.id",array('a.id as a_id'))
                ->joinLeft(DbTable::TABLE_PLAN_COMMISSION." as pc", "bapc.plan_commission_id=pc.id",array('pc.id as c_id','pc.name as commission_name'))
                ->joinLeft(DbTable::TABLE_PLAN_FEE." as pf", "bapc.plan_fee_id=pf.id",array('pf.id as f_id','pf.name as fee_name'))
                ->joinLeft(DbTable::TABLE_PRODUCT_LIMIT." as pl", "bapc.product_limit_id= pl.id",array('pl.name as product_limit_name','pl.product_limit_code','pl.product_id','pl.id as plid'))
                ->joinLeft(DbTable::TABLE_PRODUCTS." as p", "bapc.product_id = p.id ",array('p.name as product_name','p.id as pid'))
                //->joinLeft("t_bank as b", "p.bank_id = b.id ",array('b.name as bank_name'))
                ->where("(bapc.date_end = '0000-00-00' OR bapc.date_end >= bapc.date_start)")
                ->where ("a.enroll_status ='".STATUS_APPROVED."'")
                ->where ("a.id = $id")
                ->where ("bapc.status = '".STATUS_ACTIVE."'")
                ->order(array('p.name','bapc.date_start'));
        return $details;
        
    }
    public function getagentproductDetailsAsArray ($id){
       
        $curdate = date("Y-m-d");
        $details = $this->getagentproductSql($id);;
     
       //echo $details->__toString();
       
       return $this->fetchAll($details);
       
        
        
    }
    
    public function getagentDetails ($id){
        
       $details = $this->select()
                ->from(DbTable::TABLE_AGENTS." as a", array('a.email','a.first_name','a.last_name'))
                ->where ("a.id = $id");
     
       return $this->_db->fetchRow($details);
        
        
    }
    
    
    /**
     * Overrides findById() in App_Model
     * 
     * @param int $userId 
     * @access public
     * @return array
     */
    /*public function findById($Id, $force = FALSE){
        if (!is_numeric($Id)) {
            return array();
        }
        
        $select = $this->_db->select();
        $select->from("t_agent_products");
        $select->setIntegrityCheck(false);
        $select->where('id = ?', $Id);
       
       
        return $this->fetchRow($select);
    }*/
    
    /**
     * Overrides deleteById() in App_Model
     * 
     * @param int $privilegeId
     * @access public
     * @return void
     */
    public function deleteById($agentId){
        $agn_info = array('status' => STATUS_BLOCKED);
        $update = $this->update($agn_info,"id=$agentId");
        if ($update)
        return TRUE;
    }
    
     public function approveById($agentId,$data){
        
         
         //Generate Password and update password on db
        $password = Util::generateRandomPassword();
        
        $db_password = BaseUser::hashPassword($password, 'agent');
        
        $agent_code = $this->getAgentCode($agentId);
        
        
        
         // generate Verification code for email verification
         $verification_code = Util::hashVerification($agent_code);
        
        $dataarr = array(
                'enroll_status'=>STATUS_APPROVED,
                'password'=>$db_password,
                'approval_ops_id'=>$data['by_ops_id'],
                'approval_datetime'=>new Zend_Db_Expr('NOW()')
         );
        if(isset($data['user_type'])) {
            $dataarr['user_type'] = $data['user_type'];
            unset($data['user_type']);
        }
        $this->update($dataarr,"id = ". $agentId);
        
        
         
        //Alert method for sending password in email and SMS
         $agentuserModel = new AgentUser();
         $agentModel = new Agents();
         $param = $agentModel->findById($agentId);
         $agent_details = $agentModel->findagentDetailsById($agentId);
         $detailsArr = array('id' =>$param['id'],'first_name'=>$param['first_name'],'last_name'=>$param['last_name'],'name'=>$param['name'],
             'email'=>$param['email'],'auth_email'=>$param['auth_email'],'password'=>$password,'agent_code'=>$agent_code);
         
         
          $activation_id = $this->sendVerificationCode($agentId,$verification_code,$detailsArr);
             
              if($activation_id < 0)
               {
                  
                   $verification_code = Util::hashVerification($agent_code);
                   $activation_id = $this->sendVerificationCode($agentId,$verification_code,$detailsArr);
               }
        if( $agent_details['auth_email'] != ''){
         $auth_verification_code = Util::hashVerification($agent_details['id']);
         $auth_activation_id = $this->sendAuthVerificationCode($agent_details['id'],$auth_verification_code,$detailsArr);
             
              if($auth_activation_id < 0)
               {
                  
                   $auth_verification_code = Util::hashVerification($agent_details['id']);
                  $auth_activation_id = $this->sendAuthVerificationCode($agent_details['id'],$auth_verification_code,$detailsArr);
           }
         
         
        }
           
        
        $insert = $this->_db->insert("t_change_status_log",$data);
        if($insert >0)
        return TRUE;
    }
    
     public function rejectById($agentId,$data){
        
        $dataarr = array('enroll_status'=>STATUS_REJECTED);
       
        $this->update($dataarr,"id = ". $agentId);
        
        
       
        $insert = $this->_db->insert("t_change_status_log",$data);
        if($insert >0)
        return TRUE;
    }
    
     public function sendVerificationCode($id,$verification_code,$detailsArr){
       
        $check = $this->_db->select()
               ->from(DbTable::TABLE_EMAIL_VERIFICATION,array('id'))
                ->where('verification_code =?',$verification_code);
                $activation = $this->_db->fetchRow($check);
                
                if (empty($activation)){
                
        $activationArr = array ('agent_id'=>$id,
             'verification_code'=>$verification_code,'email'=>$detailsArr['email'],
             'datetime_send'=>new Zend_Db_Expr('NOW()'),
             'activation_status'=>STATUS_PENDING,'remarks'=>'Email verification sent');
        
           
                 $activation_id = $this->_db->insert(DbTable::TABLE_EMAIL_VERIFICATION,$activationArr);
            if($activation_id){
                 $act_id = $this->_db->lastInsertId(DbTable::TABLE_EMAIL_VERIFICATION, 'id');
            }
            else
            {
                throw new Exception("Invalid data for insert. Please try again");
                 exit;
            }
        
           $m = new App\Messaging\MVC\Axis\Agent();
             try {
             
               
            
               $m->agentEmailVerification(array(
                 'name'   => $detailsArr['name'],
                 'first_name'   => $detailsArr['first_name'],
                 'last_name'   => $detailsArr['last_name'],
                 'email'        => $detailsArr['email'],
                 'verification_code' => $verification_code,
                 'agent_code'   => $this->getAgentCode($id),
                 'password'   => $detailsArr['password'],
                 'id' => $id), 'operation');
             }
             catch (Exception $e ) {  
                                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                                $errMsg = $e->getMessage();
                                $this->_helper->FlashMessenger(
                                    array(
                                        'msg-error' => $errMsg,
                                    )
                                );
                    }
                    
                    
                    return $act_id;
                }
                    else {
                        return FALSE;
                    }
        
    }
    
 public function getBank()
    {
        $select = $this->select();
        $select->from("t_bank",array('id','name'));
        $select->distinct(TRUE);
        $select->where("status =  '".STATUS_ACTIVE."'");
        $select->setIntegrityCheck(false);
        $bankArr =  $this->fetchAll($select);
        $dataArray = array();
        $dataArray = array('' => 'Select Bank Name');
        foreach ($bankArr as $id => $val) {
            $dataArray[$val['id']] = $val['name'];
        }
        return $dataArray;
  
    }
    
     public function getBankByProduct($pid)
    {
        $select = $this->_db->select()
                  ->from(DbTable::TABLE_BANK." as b",array('b.id','b.name'))
                  ->joinleft(DbTable::TABLE_PRODUCTS.' as p','p.bank_id = b.id',array('p.name as product_name','p.id as p_id'))
                  ->where('p.id='.$pid);
       
        $bankArr =  $this->_db->fetchAll($select);
        $dataArray = array();
       
        foreach ($bankArr as $id => $val) {
            $dataArray[$val['id']] = $val['name'];
        }
        return $dataArray;
  
    }
    
    
     public function getCommissionPlan()
    {
        $select = $this->select();
        $select->from(DbTable::TABLE_PLAN_COMMISSION,array('id','name'));
        $select->setIntegrityCheck(false);
        $bankArr =  $this->fetchAll($select);
        $dataArray = array();
        $dataArray = array('' => 'Select Commission Plan');
        foreach ($bankArr as $id => $val) {
            $dataArray[$val['id']] = $val['name'];
        }
        return $dataArray;
  
    }
    
     public function getproductByBankId($bankId)
    {
        $select = $this->select();
        $select->from(DbTable::TABLE_PRODUCTS,array('id','name','ecs_product_code'));
        $select->setIntegrityCheck(false);
        $select->where("bank_id=".$bankId);
        $select->where("status='".STATUS_ACTIVE."'");
        $masterproductArr =  $this->fetchAll($select);
        $dataArray = array();
        foreach ($masterproductArr as $id => $val) {
            $dataArray[$val['id']] = $val['ecs_product_code'].' ('.$val['name'].')';
        }
        
        return $dataArray;
  
    }
    
     public function getlimitByproductId($productId)
    {
        $select = $this->select();
        $select->from(DbTable::TABLE_PRODUCT_LIMIT,array('id','product_limit_code'));
        $select->setIntegrityCheck(false);
        $select->where("product_id=".$productId);
        $select->where("status='".STATUS_ACTIVE."'");
        $masterproductArr =  $this->fetchAll($select);
        $dataArray = array();
        foreach ($masterproductArr as $id => $val) {
            $dataArray[$val['id']] = $val['product_limit_code'];
        }
        
        return $dataArray;
  
    }
    
    
     
    
    public function getFeeBybankId($resourceId){
      $select = $this->select()
                ->from("t_products as p")
                ->setIntegrityCheck(false)
                ->joinLeft("t_bank as b", "b.id=p.bank_id")
                ->join("t_fee as f", " f.product_id = p.id", array('f.id','f.name'))
                ->where('p.bank_id= ?', $resourceId)
                ->order('p.id ASC');
       
        
        
        $fee = $this->fetchAll($select);
        $dataArray = array();
        foreach ($fee as $id => $val) {
            $dataArray[$val['id']] = $val['name'];
        }
        
        return $dataArray;
    }
    
    
     public function getBankbyFeeId($resourceId){
      $select = $this->select()
                ->from("t_products as p", array('p.bank_id','p.id'))
                ->setIntegrityCheck(false)
                ->joinLeft("t_fee as f", "f.product_id=p.id", array('f.id','f.product_id'))
                ->joinleft("t_bank as b", " p.bank_id = b.id", array('b.id','b.name'))
                ->where('f.id= ?', $resourceId);
                
   
       $fee = $this->fetchRow($select);
       return $fee;
        
    }
    
     public function getBankbyAgentId($resourceId){
      $select = $this->select()
                ->from("t_agent_products as ap", array('ap.fee_id'))
                ->setIntegrityCheck(false)
                ->joinLeft("t_fee as f", "f.id=ap.fee_id", array('f.id','f.product_id'))
                ->join("t_products as p", " f.product_id = p.id", array('p.bank_id'))
                ->where('ap.agent_id= ?', $resourceId)
                ->order('p.id ASC');
              
        $fee = $this->fetchRow($select);
        return $fee;
        
    }
    
 
    public function checkmasterProduct($id,$product_id){
       
        
        $selectid = $this->_db->Select()
                   ->from("t_products")
                   ->where("id=".$product_id);           
         $rownumid = $this->_db->fetchRow($selectid);
         
         
         
          $select = $this->_db->Select()
                   ->from("t_products")
                   ->where("id=".$id);             
         $rownum = $this->_db->fetchRow($select);
         
         if ($rownum['product_master_id'] == $rownumid['product_master_id'])
             $bank = $this->checkBank($rownum['product_master_id'],$rownumid['product_master_id']);
                 if($bank)
                     return TRUE;
                 else 
                     return FALSE;
         
            
    }
    
    public function check_Bank($id,$product_id){
        
        $selectid = $this->_db->Select()
                   ->from("t_product_master")
                   ->where("id=".$product_id);           
         $rownumid = $this->_db->fetchRow($selectid);
         
         
         
          $select = $this->_db->Select()
                   ->from("t_product_master")
                   ->where("id=".$id);             
         $rownum = $this->_db->fetchRow($select);
         
         if ($rownum['bank_id'] == $rownumid['bank_id'])
             return TRUE;
         else 
             return FALSE;
    }
    public function checkagentProduct(array $data){
        
         $select = $this->_db->Select()
                   ->from("t_agent_products")
                   ->where("agent_id=".$data['agent_id'])                 
                   ->where("product_id=".$data['product_id'])
                   ->where("fee_id=".$data['fee_id']); 
               
        $rownum = $this->_db->fetchRow($select);
     
        if(empty($rownum))
           return 'new_record';
        else
            return 'duplicate';
            
    }
    
    public function checkagentfee(array $data){
        
         $select = $this->_db->Select()
                   ->from("t_agent_products")
                   ->where("agent_id=".$data['agent_id'])                 
                   ->where("product_id=".$data['product_id']);
                   
               
        $rownum = $this->_db->fetchRow($select);
     
        if(!empty($rownum))
           return 'product_fee_assigned';
        else
            return 'product_fee_not_assigned';
            
    }
    
    public function agentProduct(array $data){
               
        $res = $this->_db->insert("t_bind_agent_product_commission",$data);
        return $res;
        
        
        
        
    }
    
    public function checkBank($id,$fee_id){
      
        $checkfeeid = $this->getBankbyFeeId($fee_id);
         
        
        $checkagentid = $this->getBankbyAgentId($id);
        
        
         if($checkfeeid['bank_id'] == $checkagentid['bank_id'])
             return 'same_bank';
         else
             return 'diff_bank';
    }
    
  
       public function updagentProduct($id , $startDate){
           
           $data = array(
            'date_end' => date('Y-m-d', strtotime('-1 day', strtotime($startDate))) );
           
        $update = $this->_db->update(DbTable::TABLE_BIND_AGENT_PRODUCT_COMMISSION,$data,"id=$id") ;
        
       
        return $update;
       }
  
   
     public function findById($id, $force = FALSE){
        if (!is_numeric($id)) {
            return array();
        }
        
        $select = $this->_db->Select()
                   ->from(DbTable::TABLE_BIND_AGENT_PRODUCT_COMMISSION)
                   ->where("id=".$id);
        
        
        
        return $this->_db->fetchRow($select);
        
        
    }
    
    public function findByAgentId($id, $force = FALSE){
        if (!is_numeric($id)) {
            return array();
        }
        
        $select = $this->_db->Select()
                   ->from("t_agents")
                   ->where("id=".$id);
        
        
        
        return $this->_db->fetchRow($select);
        
        
    }
    
    public function findAgent($id){
        if (!is_numeric($id)) {
            return array();
        }
        
        $select = $this->_db->Select()
                   ->from(DbTable::TABLE_AGENTS." as a",array('a.*','concat(a.first_name," ",a.last_name) as name'))
                   ->joinLeft(DbTable::TABLE_AGENT_DETAILS ." as ad","ad.agent_id = a.id",array('branch_id'))   
                ->where("a.id=".$id);
        
        //echo $select->__toString();
        
        return $this->_db->fetchRow($select);
        
        
    }
    
 
    
   
     public function checkLastdetails($agentId){
         $select = $this->_db->select()
                 ->from(DbTable::TABLE_BIND_AGENT_PRODUCT_COMMISSION)
                  ->where("date_end = '0000-00-00'")
                  ->where("agent_id = ".$agentId);
        
        $groupArr = $this->_db->fetchRow($select);
        if (!empty($groupArr))
            return $groupArr;
        else
            return FALSE;
     }
     
     
     public function checkProductdateRange($agentId,$pid,$startDate){
         $select = $this->_db->select()
                 ->from(DbTable::TABLE_BIND_AGENT_PRODUCT_COMMISSION)
                  ->where("date_end != '0000-00-00'")
                  ->where("agent_id = ".$agentId)
                  ->where("date_end > date_start")
                  ->where("product_id = ".$pid);
        //echo $select->__toString();
        $groupArr = $this->_db->fetchAll($select);
        if (!empty($groupArr))
            return $groupArr;
        else
            return FALSE;
     }
     public function findProductById($id){
         $select = $this->_db->select()
                 ->from(DbTable::TABLE_BIND_AGENT_PRODUCT_COMMISSION)
                 ->where("agent_id =$id");
                  
        $groupArr = $this->_db->fetchRow($select);
        if (!empty($groupArr))
            return $groupArr;
        else
            return FALSE;
     }
     public function getProductById($id){
         $select = $this->_db->select()
                 ->from(DbTable::TABLE_BIND_AGENT_PRODUCT_COMMISSION.' as b',array('b.id as bid','b.product_id'))
                 ->joinleft(DbTable::TABLE_PRODUCTS.' as p','b.product_id=p.id',array('p.id as id','p.name as name'))
                 ->where("b.id =$id");
          
           
        $bankArr =  $this->_db->fetchRow($select);
       
        return $bankArr;
     }
     public function checkInRange($start_date, $end_date, $date_from_user)
       {
            // Convert to timestamp
            $start_ts = new DateTime($start_date);
            $end_ts = new DateTime($end_date);
            $user_ts = new DateTime($date_from_user);
            
            // Check that user date is between start & end
            if(($user_ts >= $start_ts) && ($user_ts <= $end_ts))
            return TRUE;
            else
                return FALSE;
     }
     
     public function sendAuthVerificationCode($id,$verification_code,$detailsArr){
       
        $check = $this->_db->select()
               ->from(DbTable::TABLE_EMAIL_VERIFICATION,array('id'))
                ->where('verification_code =?',$verification_code);
                $activation = $this->_db->fetchRow($check);
                
                if (empty($activation)){
                
        $activationArr = array ('agent_detail_id'=>$id,
             'verification_code'=>$verification_code,'email'=>$detailsArr['auth_email'],
             'datetime_send'=>new Zend_Db_Expr('NOW()'),
             'activation_status'=>STATUS_PENDING,'remarks'=>'Auth Email verification sent');
        
           
                 $activation_id = $this->_db->insert(DbTable::TABLE_EMAIL_VERIFICATION,$activationArr);
            if($activation_id){
                 $act_id = $this->_db->lastInsertId(DbTable::TABLE_EMAIL_VERIFICATION, 'id');
            }
            else
            {
                throw new Exception("Invalid data for insert. Please try again");
                 exit;
            }
        
           $m = new App\Messaging\MVC\Axis\Agent();
             try {
             
               
            
               $m->agentAuthEmailVerification(array(
                 'name'   => $detailsArr['name'],
                 'first_name'   => $detailsArr['first_name'],
                 'last_name'   => $detailsArr['last_name'],
                 'email'        => $detailsArr['auth_email'],
                 'verification_code' => $verification_code,
                 'agent_code'   => $this->getAgentCode($id),
//                 'password'   => $detailsArr['password'],
                 'id' => $id), 'operation');
             }
             catch (Exception $e ) {  
                                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                                $errMsg = $e->getMessage();
                                $this->_helper->FlashMessenger(
                                    array(
                                        'msg-error' => $errMsg,
                                    )
                                );
                    }
                    
                    
                    return $act_id;
                }
                    else {
                        return FALSE;
                    }
        
    }
      
}