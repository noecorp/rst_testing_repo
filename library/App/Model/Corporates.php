<?php
/**
 * Model that manages the products
 *
 * @package Operation_Models
 * @copyright transerv
 */

class Corporates extends App_Model
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
    protected $_name = DbTable::TABLE_CORPORATE_USER;
    
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
        DbTable::TABLE_AGENT_DETAILS => array(
            'columns' => 'corporate_user_id',
            'refTableClass' => DbTable::TABLE_CORPORATE_USER,
            'refColumns' => 'id'
        ),
    );
    
    /**
     * Finds a privilege based on its name and the id of the
     * resource it belongs to
     * 
     * @param string $name 
     * @param int $resourceId 
     * @access public
     * @return void
     */
    public function findByNameAndFlagId($name, $resourceId){
        $select = $this->_select();
        $select->from($this->_name);
        $select->where('name = ?', $name);
        $select->where('flag_id = ?', $resourceId);
        
        $privilege = $this->fetchRow($select);
        
        $privilege->flag = $privilege->findParentRow('Flag');
        $privilege->flagName = $privilege->flag->name;
        
        return $privilege;
    }
    
    /**
     * Retrieves all the privileges attached to
     * the specified resource
     * 
     * @param mixed $resourceId 
     * @access public
     * @return void
     */
    public function getProductByMasterId($resourceId){
        $select = $this->_select();
        $select->from($this->_name);
        $select->where('flag_id = ?', $resourceId);
        $select->order('name ASC');
        
        $privileges = $this->fetchAll($select);
        
        foreach($privileges as $privilege){
            $privilege->flag = $privilege->findParentRow('Flag');
            $privilege->flagName = $privilege->flag->name;
        }
        
        return $privileges;
    }
    
    /**
     * Overrides getAll() in App_Model
     * 
     * @param int $page 
     * @access public
     * @return Zend_Paginator
     */
    public function getAll($page = 1){
        $paginator = $this->fetchAll();
        //$privileges = array();
      
        $paginator = Zend_Paginator::factory($paginator);
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage(App_DI_Container::get('ConfigObject')->paginator->items_per_page);
        
        return $paginator;
    }
    
    
    public function getDetails ($param, $page = 1, $paginate = NULL, $force = FALSE){
       //$param = array('searchCriteria'=>'mobile1','keyword'=>'9810780690');
       $columnName = $param['searchCriteria'];
       $keyword = $param['keyword'];
      
       if($columnName == 'estab_city'){
            $whereString = "ag.$columnName LIKE '%$keyword%'";
       }
       else{
            $whereString = "a.$columnName LIKE '%$keyword%'";
       }
       $details = $this->select()
                ->from(DbTable::TABLE_AGENTS." as a",array('id','concat(a.first_name," ",a.last_name) as name','email','agent_code','mobile1','enroll_status','status','parent_agent_id'
                    ))
                ->setIntegrityCheck(false)
                ->joinLeft(DbTable::TABLE_AGENT_DETAILS." as ag", "ag.agent_id=a.id AND ag.status='".STATUS_ACTIVE."'",array('ag.afn','if(ag.by_ops_id = 0, "Self", "By Ops") as registration_type','ag.res_state','ag.res_district','ag.res_city','ag.res_taluka','ag.res_pincode'))
                ->joinLeft(DbTable::TABLE_AGENT_BALANCE." as ab", "ab.agent_id=a.id", array("amount"))
                ->where("a.enroll_status='".STATUS_APPROVED."'")
                ->where($whereString)
                ->order('ag.date_created DESC');
       //echo $details->__toString();exit;
       return $this->_paginate($details, $page, $paginate);
        
        
    }
    
    
    public function getStatus($agentId){
        $select = $this->select() 
                ->from(DbTable::TABLE_AGENTS,array('id','status','enroll_status'));
                $select->where('id = ?', $agentId);
            
        return $this->fetchRow($select);
    }
    
  
    public function agentDoclist($agentId){
        
          if (!is_numeric($agentId)) {
            return array();
        }
        
        $select = $this->select() 
                ->from(DbTable::TABLE_AGENTS.' as a')
                 ->setIntegrityCheck(false)
                ->join(DbTable::TABLE_DOCS." as d", "a.id=d.doc_agent_id",array('d.doc_agent_id','d.doc_type','d.file_name','d.status'));
               $select->where('d.doc_agent_id = ?', $agentId);
               $select->where("d.status = '".STATUS_ACTIVE."' ");
            
        return $this->fetchAll($select);
    }
    
    public function savedetails(array $param, array $agn_info){
        
        $mobNum = isset($param['mobile_number']) ? $param['mobile_number'] : '';
               
        $select = $this->select()
              
                ->where('mobile1 <>?',$mobNum)
                ->where('email =?',$param['email']);
                $agntData = $this->fetchAll($select);
               
        
       
        
        if(!empty($agntData) && $agntData->count() >0) {
            return 'email_mobile_dup';
        }        
         
         
         $id = $this->insert($param);
         $agn_info['agent_id']=$id;
         
         
       
         if($id>0){ 
             // adding agent details to t_agent_details
             
            
             
              $this->_db->insert(DbTable::TABLE_AGENT_DETAILS,$agn_info);         
         }
         return 'sucess';      
        
        
    }
    public function inactiveAgentDetails($inactiveArr,$detail_id){
      
       $update = $this->_db->update (DbTable::TABLE_AGENT_DETAILS,$inactiveArr,"id = $detail_id"); 
       if($update)
       return true;
    }
    public function updatedetails(array $param, array $agn_info,$id){
        $agentuser = new AgentUser();
        $approveagentModel = new Approveagent();
//        $Corpflag = $param['Corpflag'];
//         if($Corpflag == TRUE && $agn_info['auth_email'] ==''){
//             return 'auth_email_req';
//         }
       // Check Phone if updated 
       if($this->agentPhone($param['mobile1'],$id) != 'agent_phone'){
                
        $checkphone =  $agentuser->checkPhone($param['mobile1']);
        
        if($checkphone=='phone_dup')
            return 'mobile_dup';
          }
        // Check Auth email     
//         $authemail = $this->agentAuthEmail($agn_info['auth_email'],$id);    
//        
//           if( $authemail  != 'agent_auth_email'){
//         
//           
//         
//         // new auth email entered for Agent do new Verification link needs to be sent
//             // to Authorize email
//             //get new verification code 
//              $agent_details = $this->findagentDetailsById($id);
//             
//             
//             
//            
//             $detailsArr = array('first_name'=>$param['first_name'],
//             'last_name'=>$param['last_name'],'auth_email'=>$agn_info['auth_email'],'email'=>$param['email']);
//             
//            
//             
//         $auth_verification_code = Util::hashVerification($agent_details['id']);
//         $auth_activation_id = $approveagentModel->sendAuthVerificationCode($agent_details['id'],$auth_verification_code,$detailsArr);
//        
//               $agentUpdate = array ('first_name'=>$param['first_name'],
//             'last_name'=>$param['last_name'],'email'=>$param['email']);
//            
//            
//             }
         
       if($this->agentEmail($param['email'],$id) != 'agent_email'){
                    
         $emailcheck = $this->emailDuplication($param['email']);
         if(!empty($emailcheck))
             return 'email_dup'; 
         else
         {
             // new email entered for Agent do new Verification link needs to be sent
             // to Authorize email
             //get new verification code 
             $agentArr = $this->findById($id);
             
             $verification_code = Util::hashVerification($agentArr['agent_code']);
             
             //New password
             $password = Util::generateRandomPassword();
             $db_password = BaseUser::hashPassword($password , 'agent');
            
             
            
             $detailsArr = array('first_name'=>$param['first_name'],
             'last_name'=>$param['last_name'],'email'=>$param['email'],'password'=>$password);
             
             
             $activation_id = $agentuser->sendVerificationCode($id,$verification_code,$detailsArr);
         
                
             
              $agentUpdate = array ('first_name'=>$param['first_name'],
             'last_name'=>$param['last_name'],'email'=>$param['email'],'email_verification_status' =>'pending','password'=>$db_password);
                         
         }
        }
        if(isset($agentUpdate) && !empty($agentUpdate)) {
         $update_agent = $this->update($agentUpdate,"id=$id");
        }
        else{
             $update_agent = $this->update($param,"id=$id");
        }
               $update = $this->_db->insert (DbTable::TABLE_AGENT_DETAILS,$agn_info);
        if ($update_agent || $update)
            $update_sucess = 1;
               
        return $update_sucess;
        
    }
    
    public function emailDuplication($email){
        $where = " email = '".$email."' AND (enroll_status = '".STATUS_APPROVED."' OR enroll_status = '".STATUS_PENDING."')";
        $select = $this->select() 
                ->from(DbTable::TABLE_AGENTS,array('id'))
                ->where($where);
        return $this->fetchRow($select);        
    }
    
    public function agentEmail($email,$id){
        
        $select = $this->select('id')      
                ->where("email ='$email'")
                ->where("id =$id");
         $chk =$this->fetchRow($select);
     
         if(!empty($chk))
                return 'agent_email';
        else 
            return 'not_agent_email';
        
    }
    
    
    public function agentPhone($phone,$id){
        
       
            
        $selectphone = $this->select()
                ->where('mobile1 =?',$phone)
                ->where('id =?',$id);
                $agntphData = $this->fetchRow($selectphone);
                
              
        if(!empty($agntphData)) {
             //App_Logger::log("Phone number already registered", Zend_Log:: INFO);
             return 'agent_phone';
        }      
        else return 'not_agent_phone';
            
        
        
    }
    /**
     * Overrides findById() in App_Model
     * 
     * @param int $userId 
     * @access public
     * @return array
     */
    public function findById($corporateId, $force = FALSE){
        if (!is_numeric($corporateId)) {
            return array();
        }
        
        $select = $this->select()    
                ->from(DbTable::TABLE_CORPORATE_USER.' as a',array('a.id','concat(a.first_name," ",a.last_name) as name','a.first_name','a.last_name','a.email','a.status',
                'a.corporate_code','a.mobile','a.enroll_status','*'))
                ->setIntegrityCheck(false)
                ->joinLeft(DbTable::TABLE_CORPORATE_USER_DETAILS." as ag", "ag.corporate_user_id=a.id AND ag.status = '".STATUS_ACTIVE."'",array('ag.id as row_id','ag.afn','ag.profile_photo',
                'ag.father_first_name','ag.title','ag.mobile2','ag.father_middle_name','ag.father_last_name','ag.auth_email',
                'ag.spouse_first_name','ag.spouse_middle_name','ag.spouse_last_name',
                'ag.mother_maiden_name','ag.estab_name','ag.home','ag.office','ag.shop',
                'ag.education_level','ag.matric_school_name','ag.intermediate_school_name',
                'ag.graduation_degree','ag.graduation_college','ag.p_graduation_degree',
                'ag.p_graduation_college','ag.other_degree','ag.other_college','DATE_FORMAT(ag.date_of_birth, "%d-%m-%Y") as date_of_birth',
                'ag.fund_account_type','ag.gender','ag.Identification_type','ag.Identification_number',
                'ag.pan_number','ag.flat_no','ag.estab_name','ag.estab_address1','ag.estab_address2','ag.estab_city',
                'ag.estab_taluka','ag.estab_district','ag.estab_state','ag.estab_country',
                'ag.estab_pincode','ag.res_type','ag.res_address1','ag.res_address2','ag.res_city',
                'ag.res_taluka','ag.res_district','ag.res_state','ag.res_country','ag.res_pincode',
                'ag.bank_name','ag.bank_account_number','ag.team_manager_approval','ag.bank_id',
                'ag.bank_location','ag.bank_city','ag.bank_ifsc_code','ag.branch_id','ag.bank_area',
                'ag.bank_branch_id','ag.operation_head_approval','ag.bank_approval','ag.amount_bal',
                'ag.closure_request','DATE_FORMAT(ag.closure_date, "%d-%m-%Y") as closure_date','ag.occupation','ag.id_proof1','ag.id_proof2',
                'ag.address_proof','ag.address_proof_number','ag.address_proof_type','DATE_FORMAT(ag.passport_expiry, "%d-%m-%Y") as passport_expiry','ag.annual_income','ag.computer_literacy','ag.political_linkage','if(ag.by_ops_id = 0, "Self", "By Ops") as registration_type','DATE_FORMAT(ag.date_created, "%d-%m-%Y") as date_created'));
               $select->where('a.id = ?', $corporateId);
               //$select->where("ag.status = '".STATUS_ACTIVE."' ");
       //echo $select->__toString();  
        return $this->fetchRow($select);
    }
    
    
    /**
     * Overrides findById() in App_Model 
     * Duplicate function of findById()
     * 
     * @param int $userId 
     * @access public
     * @return array
     */
    public function findAgentByAgentId($agentId){
        if (!is_numeric($agentId)) {
            return array();
        }
        
        $select = $this->select()    
                ->from(DbTable::TABLE_AGENTS.' as a',array('a.id','concat(a.first_name," ",a.last_name) as name','a.first_name','a.middle_name','a.last_name','a.email','a.status',
                'a.corporate_code','a.mobile','a.enroll_status','*'))
                ->setIntegrityCheck(false)
                ->joinLeft(DbTable::TABLE_AGENT_DETAILS." as ag", "ag.agent_id=a.id AND ag.status = '".STATUS_ACTIVE."'",array('ag.id as row_id','ag.afn','ag.profile_photo',
                'ag.father_first_name','ag.title','ag.father_middle_name','ag.father_last_name','ag.auth_email',
                'ag.spouse_first_name','ag.spouse_middle_name','ag.spouse_last_name',
                'ag.mother_maiden_name','ag.estab_name','ag.home','ag.office','ag.shop',
                'ag.education_level','ag.matric_school_name','ag.intermediate_school_name',
                'ag.graduation_degree','ag.graduation_college','ag.p_graduation_degree',
                'ag.p_graduation_college','ag.other_degree','ag.other_college','DATE_FORMAT(ag.date_of_birth, "%d-%m-%Y") as date_of_birth',
                'ag.fund_account_type','ag.gender','ag.Identification_type','ag.Identification_number',
                'ag.pan_number','ag.flat_no','ag.estab_name','ag.estab_address1','ag.estab_address2','ag.estab_city',
                'ag.estab_taluka','ag.estab_district','ag.estab_state','ag.estab_country',
                'ag.estab_pincode','ag.res_type','ag.res_address1','ag.res_address2','ag.res_city',
                'ag.res_taluka','ag.res_district','ag.res_state','ag.res_country','ag.res_pincode',
                'ag.bank_name','ag.bank_account_number','ag.team_manager_approval','ag.bank_id',
                'ag.bank_location','ag.bank_city','ag.bank_ifsc_code','ag.branch_id','ag.bank_area',
                'ag.bank_branch_id','ag.operation_head_approval','ag.bank_approval','ag.amount_bal',
                'ag.closure_request','DATE_FORMAT(ag.closure_date, "%d-%m-%Y") as closure_date','ag.occupation','ag.id_proof1','ag.id_proof2',
                'ag.address_proof','ag.address_proof_number','ag.address_proof_type','DATE_FORMAT(ag.passport_expiry, "%d-%m-%Y") as passport_expiry','ag.annual_income','ag.computer_literacy','ag.political_linkage','if(ag.by_ops_id = 0, "Self", "By Ops") as registration_type','DATE_FORMAT(ag.date_created, "%d-%m-%Y") as date_created'));
               $select->where('a.id = ?', $corporateId);
        return $this->fetchRow($select);
    }
    
     public function findagentDetailsById($agentId, $force = FALSE){
        if (!is_numeric($agentId)) {
            return array();
        }
        
        $select = $this->_db->select()    
                ->from(DbTable::TABLE_AGENT_DETAILS)
               ->where('agent_id = ?', $agentId)
               ->where("status = '".STATUS_ACTIVE."' ");
         
        return $this->_db->fetchRow($select);
    }
    
    /**
     * Overrides deleteById() in App_Model
     * 
     * @param int $privilegeId
     * @access public
     * @return void
     */
    public function blockByAgentId($agentId,$data){
      
        $agn_info = array('status' => STATUS_BLOCKED);
        $objLogStatus = new LogStatus();
        $update = $this->update($agn_info,"id=$agentId");
        $reslog =  $objLogStatus->log($data);
        //$insert = $this->_db->insert(DbTable::TABLE_CHANGE_STATUS_LOG,$data);
       
        if ($update && $insert >0)
        return TRUE;
    }
    
    public function unblockByAgentId($agentId,$data){
      
        $agn_info = array('status' => STATUS_UNBLOCKED);
        $update = $this->update($agn_info,"id=$agentId");
        $objLogStatus = new LogStatus();
        $reslog =  $objLogStatus->log($data);
        //$insert = $this->_db->insert(DbTable::TABLE_CHANGE_STATUS_LOG,$data);
       
        if ($update && $insert >0)
        return TRUE;
    }
    
     public function unlockByAgentId($agentId,$data){
      
        $agn_info = array('status' => STATUS_UNBLOCKED,'num_login_attempts' => '0');
        $update = $this->update($agn_info,"id=$agentId");
        $insert = $this->_db->insert(DbTable::TABLE_CHANGE_STATUS_LOG,$data);
       
        if ($update && $insert >0)
        return TRUE;
    }
    
    public function agupdateedudetails(array $data,$id){
      $update = $this->_db->insert(DbTable::TABLE_AGENT_DETAILS,$data);
             if($update){
             return TRUE;  
             }
            else {
                return False;
      }
    }
     public function agupdatedetails(array $data,$id){
        
       
                    $update = $this->_db->insert(DbTable::TABLE_AGENT_DETAILS,$data);
                    if($update)
                    return 'updated';   
          
           
     }
    
      public function adressupdatedetails(array $data,$id){
         
         
             $update = $this->_db->insert(DbTable::TABLE_AGENT_DETAILS,$data);
             if($update)
             return 'updated';
             


     }
     public function bankupdatedetails(array $data,$id){
         
         
             $update = $this->_db->insert(DbTable::TABLE_AGENT_DETAILS,$data);
             if($update)
             return 'updated';
             


     }
    public function panDuplication($pan){
        
        
        $select = $this->_db->select('id')
                ->from(DbTable::TABLE_AGENT_DETAILS)
                ->where("pan_number ='$pan'");
               
        return $this->_db->fetchRow($select);
        
    }
    
     public function idDuplication($id_no){
         
        $select = $this->_db->select()
                ->from(DbTable::TABLE_AGENT_DETAILS,array('id'))
                ->where("Identification_number ='$id_no'");
               
        return $this->_db->fetchRow($select);
        
    }
   public function addDuplication($add_no){
         
        $select = $this->_db->select()
                ->from(DbTable::TABLE_AGENT_DETAILS,array('id'))
                ->where("address_proof_number ='$add_no'");
               
        return $this->_db->fetchRow($select);
        
    }
    
     public function agentPan($pan,$id){
        
        if(!empty($pan)){
            
        $select = $this->_db->select()
                ->from(DbTable::TABLE_AGENT_DETAILS,array('id'))
                ->where("pan_number ='$pan'")
                 ->where("agent_id ='$id'");
           
        return $this->_db->fetchRow($select);
       }
    }
    
     public function agentidNo($id_no,$id){
         
        $select = $this->_db->select('id')
                ->from(DbTable::TABLE_AGENT_DETAILS,array('id'))
                ->where("Identification_number ='$id_no'")
                 ->where("agent_id ='$id'");
               
        return $this->_db->fetchRow($select);
        
    }
   public function agentaddressNo($add_no,$id){
         
        $select = $this->_db->select()
                ->from(DbTable::TABLE_AGENT_DETAILS,array('id'))
                ->where("address_proof_number ='$add_no'")
                 ->where("agent_id ='$id'");
               
        return $this->_db->fetchRow($select);
        
    }
       
    public function getAgentLimit()
    {        
        return '10000';

    }
    
    
    
     public function getAgentBalance($agentId){
        if (!is_numeric($agentId)) {
            return array();
        }
        
        $select = $this->select();
        
                $select->from(DbTable::TABLE_AGENT_BALANCE." as a",array('a.*',"DATE_FORMAT(date_modified,'%d-%m-%Y') as date_mod"))
                ->setIntegrityCheck(false)
                ->where('agent_id = ?', $agentId);       
      // echo $select->__toString();
        return $this->fetchRow($select);
    }
 
    
    
    /** getAgentLogs() will return the agent details logs of particular agent on date duration basis
     * @param agent id, duration dates 
     * @return array
     */
    public function getAgentLogs($param, $page = 1, $paginate = NULL, $force = FALSE){
        $agentId = isset($param['agent_id'])?$param['agent_id']:0;
        $dateFrom = isset($param['from'])?$param['from']:'';
        $dateTo = isset($param['to'])?$param['to']:'';
        
        if ($agentId<1 || $dateFrom=='' || $dateTo=='') {
            return array();
        }
        
        $select = $this->select();
        $select->from(DbTable::TABLE_AGENT_DETAILS.' as ag',array('ag.email', 'ag.title', 'ag.first_name', 'ag.middle_name', 'ag.last_name', 'ag.ip', 'ag.mobile1', 'ag.mobile2', 
                        'ag.mother_maiden_name', 'ag.estab_name', 'ag.estab_address1', 'ag.estab_address2', 'ag.estab_city', 
                        'ag.estab_taluka', 'ag.estab_district', 'ag.estab_state', 'ag.estab_country', 'ag.estab_pincode',
                        'ag.home', 'ag.office', 'ag.shop', 
                        'ag.education_level', 'ag.date_of_birth', 'ag.gender', 'ag.Identification_type', 'ag.Identification_number', 
                        'ag.pan_number', 'ag.flat_no',  'ag.res_type', 'ag.res_address1', 'ag.res_address2', 'ag.res_city', 'ag.res_taluka', 'ag.res_district', 'ag.res_state', 
                        'ag.res_country', 'ag.res_pincode', 
                        'ag.bank_name', 'ag.bank_account_number',  'ag.bank_location', 'ag.bank_city', 'ag.bank_ifsc_code', 'ag.branch_id', 
                        'ag.bank_area', 'ag.occupation', 'ag.address_proof', 'ag.address_proof_number', 'ag.date_created', 'ag.status'));
        $select->joinLeft(DbTable::TABLE_OPERATION_USERS." as ou", "ag.by_ops_id=ou.id",array('ou.username as by_ops_name'));
        $select->setIntegrityCheck(false);

        $select->where('ag.agent_id = ?', $agentId);
        $select->where("DATE(ag.date_created) >='".$dateFrom."'"); 
        $select->where("DATE(ag.date_created) <='".$dateTo."'"); 
        //echo $select->__toString();  exit;   
        //$abc = $this->fetchAll($select);
        
        return $this->_paginate($select, $page, $paginate);
    }
    
    
    public function updateAuthEmail($param, $id){
      $update_agent =  $this->update($param,"id=$id");  
    }
    
     public function authemailDuplication($email){
        $where = " email = '".$email."' OR auth_email ='".$email."' AND (status = '".STATUS_ACTIVE."')";
        $select = $this->_db->select() 
                ->from(DbTable::TABLE_AGENT_DETAILS,array('id'))
                ->where($where);
              
        return $this->_db->fetchRow($select);        
    }
    public function agupdateauthemail(array $data,$id){
                    $authEmailDup = $this->authemailDuplication($data['auth_email']);
                     if(!empty($authEmailDup) ) {
                         throw new Exception ('Application Form Number Exists!');
                         exit;
                         } 
                    $update = $this->_db->insert(DbTable::TABLE_AGENT_DETAILS,$data);
                    if($update)
                    return 'updated';   
          
           
     }
     public function agentAuthEmail($email,$id){
        $select = $this->_db->select()      
                ->from(DbTable::TABLE_AGENT_DETAILS,array('id'))
                ->where("auth_email ='$email'")
                ->where("agent_id =$id")
                ->where("status =?",STATUS_ACTIVE);
         $chk =$this->_db->fetchRow($select);
        if(!empty($chk))
                return 'agent_auth_email';
        else 
            return 'not_agent_auth_email';
        
    }
    
     public function getAgentAuthEmailVerifySatus($id){
        $select  = $this->_db->select()
                ->from(DbTable::TABLE_AGENT_DETAILS,array('auth_email_verification_status', 'auth_email_verification_id','status', 'id','auth_email'))
                ->where("agent_id ='$id'")
                ->where("status = '".STATUS_ACTIVE."'");
        $result = $this->_db->fetchRow($select);
        
        return $result;
     }
     
     /**
      * to fetch agent binding with product & bank
      * @param type $agentId
      * @param type $date
      * @return type
      */
     public function getAgentBinding($agentId, $date )
    {
         $select = $this->_db->select()
                 ->from(DbTable::TABLE_BIND_AGENT_PRODUCT_COMMISSION.' as b',array('b.agent_id','b.product_id' ))
                 ->joinleft(DbTable::TABLE_PRODUCTS.' as p','b.product_id=p.id',array('p.id as product_id', 'p.name as product_name', 'p.ecs_product_code as product_code','program_type'))
                 ->joinleft(DbTable::TABLE_BANK.' as bk','p.bank_id=bk.id',array('bk.name as bank_name', 'bk.id as bank_id', 'bk.unicode as bank_unicode'));
                       
         if($agentId > 0){
                    $select->where("b.agent_id = $agentId");
                }
        $select->where("'".$date."' >= b.date_start AND ('".$date."' <= b.date_end OR b.date_end = '0000-00-00')");
        $bindArr =  $this->_db->fetchAll($select);
       
        return $bindArr;
     }
     
     
    /**
     * getSubAgentList
     * Get Subagent list based on SuperAgent
     * @param type $superAgentId
     * @param type $page
     * @param type $paginate
     * @return type
     */
    public function getSubCorporateList($headCorporateId='',$typeId ='',  $page = 1, $paginate = NULL){ 
        $select =   $this->select()
                    ->from($this->_name . " as ag")
                    ->setIntegrityCheck(false)
                    ->joinLeft(DbTable::TABLE_CORPORATE_BALANCE . " as ab", "ag.id = ab.corporate_id",array('amount'))
                    ->joinLeft(DbTable::TABLE_BIND_OBJECT_RELATION . " as or", "or.to_object_id = ag.id",array());
                    //->joinLeft(DbTable::TABLE_BIND_OBJECT_RELATION_TYPES . " as obt", "obt.id = ob.object_relation_type_id");
        $select->where('ag.enroll_status ="'.STATUS_PENDING.'" OR ag.enroll_status = "'.STATUS_APPROVED.'"');
        $select->where('or.from_object_id=?',$headCorporateId);
        $select->where('or.object_relation_type_id=?',$typeId);
        //$select->where('obt.label=?',$label);
        $select->order('ag.id DESC');
        //echo $select; exit;
        return $this->_paginate($select, $page, $paginate);       
    }
         
    
   public function getBCList($param ,$flgAll = FALSE)
    {
        $objectRelation = new ObjectRelations();
        $typeId = $objectRelation->getRelationTypeId(DISTRIBUTOR_TO_AGENT);         
        $select  = $this->_db->select();        
        $select->from(DbTable::TABLE_AGENTS.' as ag',array('ag.id', 'concat(ag.first_name," ",ag.last_name) as agent_name'));
        if($param['user_type'] == DISTRIBUTOR_AGENT){
          
        $select->joinLeft(DbTable::TABLE_BIND_OBJECT_RELATION . " as or", "or.to_object_id = ag.id",array());
        $select->where('or.from_object_id=?',$param['user_id']);
        $select->where('or.object_relation_type_id=?',$typeId);    
        } 
        else if($param['user_type'] == NORMAL_AGENT || $param['user_type'] == SUB_AGENT){
            $select->where('id =?',$param['user_id']);
        }
        if(isset($param['status'])){
            $select->where("ag.status IN ('". $param['status']."')");
        }
        if(isset($param['enroll_status'])){
            $select->where("ag.enroll_status=?", $param['enroll_status']);
             }
        
        $select->order('ag.first_name');
        $agents = $this->_db->fetchAll($select);     
        $dataArray = array();
        if($flgAll == TRUE && $param['user_type'] != SUB_AGENT){
        $dataArray['all'] = 'All';
        }
        foreach ($agents as $id => $val) {
            $dataArray[$val['id']] = $val['agent_name'];
        }
        return $dataArray;     
        
    }
    
    public function getBCListUnderDistributor($param)
    {
        $select = $this->sqlBCListUnderDistributor($param);
        return $this->fetchAll($select);    
        
    }
    
    private function sqlBCListUnderDistributor($param)
    {
        $objectRelation = new ObjectRelations();
        $typeId = $objectRelation->getRelationTypeId(DISTRIBUTOR_TO_AGENT);         
        $select  = $this->select();  
        $select->setIntegrityCheck(false);
        $select->from(DbTable::TABLE_AGENTS.' as ag',array('ag.id', 'concat(ag.first_name," ",ag.middle_name," ",ag.last_name) as agent_name','*'));
        $select->joinLeft(DbTable::TABLE_AGENT_DETAILS . " as ad", "ad.agent_id = ag.id AND ad.status='". STATUS_ACTIVE ."'",array('branch_id'));
        $select->joinLeft(DbTable::TABLE_BIND_OBJECT_RELATION . " as or", "or.to_object_id = ag.id",array());
        $select->where('or.from_object_id=?',$param['agent_id']);
        $select->where('or.object_relation_type_id=?',$typeId);    
       
        if(isset($param['status'])){
            $select->where("ag.status IN ('". $param['status']."')");
        }
        if(isset($param['enroll_status'])){
            $select->where("ag.enroll_status=?", $param['enroll_status']);
             }
        
        $select->order('ag.institution_name');
        return $select;
        
    }
    
    public function getBCListUnderDist($param)
    {
        $select = $this->sqlBCListUnderDistributor($param);
        $agentsArr =  $this->fetchAll($select);  
        $agent_ids = '';
        foreach ($agentsArr as $agents) {
            $agent_ids .= $agents['id'] . "','";
        }
        // remove last ,
        return substr($agent_ids, 0, -3);
        
    }
    
     public function countBCListUnderDistributor($param)
    {
        $objectRelation = new ObjectRelations();
        $typeId = $objectRelation->getRelationTypeId(DISTRIBUTOR_TO_AGENT);         
        $select  = $this->select();  
        $select->setIntegrityCheck(false);
        $select->from(DbTable::TABLE_AGENTS.' as ag',array('count(ag.id) as count','*'));
        $select->joinLeft(DbTable::TABLE_AGENT_DETAILS . " as ad", "ad.agent_id = ag.id AND ad.status = '".STATUS_ACTIVE."'",array('branch_id'));
        $select->joinLeft(DbTable::TABLE_BIND_OBJECT_RELATION . " as or", "or.to_object_id = ag.id",array());
        $select->where('or.from_object_id=?',$param['agent_id']);
        $select->where('or.object_relation_type_id=?',$typeId);    
       
        if(isset($param['status'])){
            $select->where("ag.status IN ('". $param['status']."')");
        }
        if(isset($param['enroll_status'])){
            $select->where("ag.enroll_status=?", $param['enroll_status']);
             }
        
        $select->order('ag.institution_name');
        return $this->fetchRow($select);    
        
    }
    
     public function getBCListing($param)
    {
        $objectRelation = new ObjectRelations();
        $typeId = $objectRelation->getRelationTypeId(DISTRIBUTOR_TO_AGENT);         
        $select  = $this->_db->select();        
        $select->from(DbTable::TABLE_AGENTS.' as ag',array('ag.id', 'concat(ag.first_name," ",ag.last_name) as agent_name'));
        if($param['user_type'] == DISTRIBUTOR_AGENT){
          
        $select->joinLeft(DbTable::TABLE_BIND_OBJECT_RELATION . " as or", "or.to_object_id = ag.id",array());
        $select->where('or.from_object_id=?',$param['user_id']);
        $select->where('or.object_relation_type_id=?',$typeId);    
        } 
        else if($param['user_type'] == NORMAL_AGENT || $param['user_type'] == SUB_AGENT){
            $select->where('id =?',$param['user_id']);
        }
        if(isset($param['status'])){
            $select->where("ag.status IN ('". $param['status']."')");
        }
        if(isset($param['enroll_status'])){
            $select->where("ag.enroll_status=?", $param['enroll_status']);
             }
        
        $select->order('ag.first_name');
        $agents = $this->_db->fetchAll($select);     
        $dataArray = '';
        foreach ($agents as $val) {
            $dataArray.= "'".$val['id']."',";
        }
       
        return substr($dataArray,0,-1);
        
    }
    
    public function findByAgentId($agentId){
        if (!is_numeric($agentId)) {
            return array();
        }
        
        $select = $this->_db->select()    
                ->from(DbTable::TABLE_AGENT_DETAILS.' as a',array('*'));
               $select->where('a.agent_id = ?', $agentId);
               $select->where('a.status = ?', STATUS_ACTIVE);
        return $this->_db->fetchRow($select);
    }
    
    public function findByCorporateCode($corporateCode){
        $select = $this->_db->select()
                ->from(DbTable::TABLE_CORPORATE_USER.' as a',array('*'));
                $select->where('a.corporate_code = ?', $corporateCode);
                $select->where('a.enroll_status = ?', STATUS_APPROVED);
                $select->where('a.status = ?', STATUS_UNBLOCKED);
            return $this->_db->fetchRow($select); 
    }
}