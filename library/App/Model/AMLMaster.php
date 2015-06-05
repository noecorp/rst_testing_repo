<?php

/**
 * Model that manages the products
 *
 * @package Operation_Models
 * @copyright transerv
 */
class AMLMaster extends App_Model {

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
    protected $_name = DbTable::TABLE_AML_MASTER;

    public function insertMasterCRN($dataArr) {
        if(!empty($dataArr) && isset($dataArr['dataid'])) {
	        $user = Zend_Auth::getInstance()->getIdentity();
	        //echo "<pre>";print_r($dataArr);exit;
	        return $this->insert($dataArr);
        } 
        return false;
    }
    
    public function getAll($param, $page = 1, $paginate = NULL){
    	
    		$select = $this->select()
				        ->from($this->_name." AS a" , array('*'))
				        ->setIntegrityCheck(false)
				        ->joinLeft(DbTable::TABLE_OPERATION_USERS ." AS c", "a.by_ops_id = c.id", array('concat(c.firstname," ",c.lastname) as name'));
				if(!empty($param['by_ops_id'])){
					$select->where('a.by_ops_id = ?',$param['by_ops_id']);
				}
				if(!empty($param['from'])){
					$select->where('DATE(a.date_created) >=?',$param['from']); 
				}
				if(!empty($param['to'])){
					$select->where('DATE(a.date_created) <=?',$param['to']);  
				}
				return $this->_paginate($select, $page, $paginate); 
    }
    
    public function getAmlByOps($param, $page = 1, $paginate = NULL){
    	
    		$select = $this->select()
				        ->from($this->_name." AS a" , array('by_ops_id','date_created','COUNT(*) AS TOT'))
				        ->setIntegrityCheck(false)
				        ->joinLeft(DbTable::TABLE_OPERATION_USERS ." AS c", "a.by_ops_id = c.id", array('concat(c.firstname," ",c.lastname) as name'));
				if(!empty($param['by_ops_id'])){
					$select->where('a.by_ops_id = ?',$param['by_ops_id']);
				}
				if(!empty($param['from'])){
					$select->where('DATE(a.date_created) >=?',$param['from']); 
				}
				if(!empty($param['to'])){
					$select->where('DATE(a.date_created) <=?',$param['to']);  
				}
				
				$select->group('DATE_FORMAT(a.date_created, "%Y-%m-%d")');  
				return $this->_paginate($select, $page, $paginate);
		}
    
    public function getTotal($agentId){
    	
				$agentModel = new Agents();
				$agentDetails = $agentModel->findById($agentId);
				
				$select = $this->select()
				        ->from($this->_name." AS a" , array('count(*) as total'))
				        ->where('a.full_name LIKE ?',$agentDetails['name']) 
				        ->Orwhere('a.fake_names LIKE ?','%'.$agentDetails['name'].'%');
				//echo $select; exit;
				return $this->fetchRow($select);
        
    }
    public function getDetails($agentId, $page = 1, $paginate = NULL){
    	
				$agentModel = new Agents();
				$agentDetails = $agentModel->findById($agentId);
				
				$select = $this->select()
				        ->from($this->_name." AS a" , array('*'))
				        ->where('a.full_name LIKE ?',$agentDetails['name']) 
				        ->Orwhere('a.fake_names LIKE ?','%'.$agentDetails['name'].'%');
				return $this->_paginate($select, $page, $paginate);
        
    }
    
		public function getAgentDetails($agentId = 0){
        
       $details = $this->_db->select()
                 ->from(DbTable::TABLE_AGENTS,array('status','id','email','mobile1','agent_code','concat(first_name," ",last_name) as name',"DATE_FORMAT(reg_datetime,'%d-%m-%Y') as reg_datetime",'reg_datetime as datetime'))
                ->where ("enroll_status ='".STATUS_PENDING."'")
                ->where ("status ='".STATUS_UNBLOCKED."'")
                ->where ("id ='".$agentId."'")
                ->where ("last_name <>''")
                ->order('datetime ASC');
       return $this->_db->fetchRow($details);
        
    }
    
    public function getKotakRemitters($agentId = 0){
        $details = $this->_db->select()
                    ->from(DbTable::TABLE_KOTAK_REMITTERS,array('status','id','email','mobile','concat(name," ",last_name) as name',"DATE_FORMAT(date_created,'%d-%m-%Y') as date_created"))
                    ->where ("last_name <>''")
                    ->where ("aml_status ='".STATUS_IS_AML."'")
                    ->order('id DESC');

        $results = $this->_db->fetchAll($details);
        $reportsData = array();
        foreach($results AS $data){
            $data['is_aml'] = 1;
            $reportsData[] = $data;		
        }
        return $reportsData;			       
    }
    
    public function getKotakSingleRemitter($agentId){
       $details = $this->_db->select()
                 ->from(DbTable::TABLE_KOTAK_REMITTERS,array('status','id','email','mobile','name', 'last_name',"DATE_FORMAT(date_created,'%d-%m-%Y') as date_created"))
                 ->where('id = ?',$agentId)
                 ->order('id DESC');
				
       $results = $this->_db->fetchAll($details);
       $reportsData = array();
       foreach($results AS $data){
		       	$select = $this->select()
						        ->from($this->_name." AS a" , array('*'))
						        ->where('a.first_name = "'. $data['name'] .'" AND a.second_name = "'. $data['last_name'].'"')
                                                        ->Orwhere('a.full_name = ?', $data['name'].' '.$data['last_name']) 
                                                        ->Orwhere('a.fake_names LIKE ?','%'.$data['name'].' '.$data['last_name'].'%');
						$row = $this->fetchRow($select);
						$data['is_aml'] = 0;
						if($row['id']){
							$data['is_aml'] = 1;
							$reportsData['remitter'] = $data;		
							$reportsData['aml'][] = $row;		
						}
						
       }
       return $reportsData;
    }
    
    public function getKotakBeneficiaries($agentId = 0){
        $decryptionKey = App_DI_Container::get('DbConfig')->key;
        $email = new Zend_Db_Expr("AES_DECRYPT(`email`,'".$decryptionKey."') as email"); 
        $details = $this->_db->select()
                    ->from(DbTable::TABLE_KOTAK_BENEFICIARIES,array('status','id',$email,'name',"DATE_FORMAT(date_created,'%d-%m-%Y') as date_created","CONCAT(address_line1,' ',address_line2)"))
                    ->where ("aml_status ='".STATUS_IS_AML."'")
                    ->order('id DESC');

        $results = $this->_db->fetchAll($details);
        $reportsData = array();
        foreach($results AS $data){
            $data['is_aml'] = 1;
            $reportsData[] = $data;		
        }
        return $reportsData;
    }
    
    public function getKotakSingleBeneficiary($agentId = 0){
        $decryptionKey = App_DI_Container::get('DbConfig')->key;
        $email = new Zend_Db_Expr("AES_DECRYPT(`email`,'".$decryptionKey."') as email"); 
        $details = $this->_db->select()
                 ->from(DbTable::TABLE_KOTAK_BENEFICIARIES,array('status','id',$email,'name',"DATE_FORMAT(date_created,'%d-%m-%Y') as date_created"))
                 ->where('id = ?',$agentId)
                 ->order('id DESC');
			 $results = $this->_db->fetchAll($details);
       $reportsData = array();
       foreach($results AS $data){
		       	$select = $this->select()
						        ->from($this->_name." AS a" , array('*'))
						        ->where('concat(a.first_name," ",a.second_name) = ?',$data['name']) 
                                                        ->Orwhere('a.full_name = ?',$data['name']) 
                                                        ->Orwhere('a.fake_names LIKE ?','%'.$data['name'].'%');
						$row = $this->fetchRow($select);
						if($row['id']){
							$data['is_aml'] = 1;
							$reportsData['beneficiary'] = $data;		
							$reportsData['aml'][] = $row;		
						}
			 }
       return $reportsData;
    }
    
    public function getKotakCorpCardholders($agentId = 0){
        $details = $this->_db->select()
                        ->from(DbTable::TABLE_KOTAK_CORP_CARDHOLDER,array('status','id','card_number','member_id','mobile','concat(first_name," ",last_name) as name',"DATE_FORMAT(date_created,'%d-%m-%Y') as date_created"))
                       ->where ("first_name <>''")
                       ->where ("last_name <>''")
                       ->where ("aml_status ='".STATUS_IS_AML."'")
                       ->order('id DESC');

              $results = $this->_db->fetchAll($details);
              $reportsData = array();
              foreach($results AS $data){
                   $data['is_aml'] = 1;
                   $reportsData[] = $data;		
              }
              return $reportsData;
    }
    
    public function getKotakSingleCardHolder($agentId = 0){
    	 $details = $this->_db->select()
                 ->from(DbTable::TABLE_KOTAK_CORP_CARDHOLDER,array('status','id','card_number','member_id','mobile','first_name', 'last_name',"DATE_FORMAT(date_created,'%d-%m-%Y') as date_created"))
                 ->where('id = ?',$agentId)
                 ->order('id DESC');
			 $results = $this->_db->fetchAll($details);
       $reportsData = array();
       foreach($results AS $data){
            $select = $this->select()
            ->from($this->_name." AS a" , array('*'))
            ->where('a.first_name = "'. $data['first_name'] .'" AND a.second_name = "'. $data['last_name'].'"')
            ->Orwhere('a.full_name = ?', $data['first_name'].' '.$data['last_name']) 
            ->Orwhere('a.fake_names LIKE ?','%'.$data['first_name'].' '.$data['last_name'].'%');
            $row = $this->fetchRow($select);
            if($row['id']){
                $data['is_aml'] = 1;
                $reportsData['cardholder'] = $data;		
                $reportsData['aml'][] = $row;		
            }
        }
			 
       return $reportsData;
    }
    
		public function getrejectedDetails($page = 1, $paginate = NULL, $force = FALSE){
        
        $details = $this->_db->select()
                 ->from(DbTable::TABLE_AGENTS,array('status','id','email','mobile1','agent_code','concat(first_name," ",last_name) as name',"DATE_FORMAT(reg_datetime,'%d-%m-%Y') as reg_datetime"))
                //->where ("enroll_status ='".STATUS_REJECTED."'")
                ->where ("status ='".STATUS_UNBLOCKED."'")
                ->where ("aml_status ='1'")
                ->order('reg_datetime ASC');
       
       return $this->_paginate($details, $page, $paginate);
    }
    
    public function getKotakDuplicateRemitters(){
        $select = $this->_db->select()
                 ->from(DbTable::TABLE_KOTAK_REMITTERS,array('status','id','dob','mobile','concat(name," ",last_name) as name',"DATE_FORMAT(date_created,'%d-%m-%Y') as date_created",'count(id) as cnt'))
                 ->group('name,last_name having count(id) > 1')
                 ->where('last_name <>""')
                 ->order('id DESC');
				
				$results = $this->_db->fetchAll($select);                 
				return $results;
		}
		
		public function getKotakDuplicateRemitterDetail($id){
        $select = $this->_db->select()
                 ->from(DbTable::TABLE_KOTAK_REMITTERS,array('status','id','ip','dob','mobile','concat(name," ",last_name) as name',"DATE_FORMAT(date_created,'%d-%m-%Y') as date_created"))
								 ->where('concat(name," ",last_name) IN(?)',new Zend_Db_Expr("(SELECT concat(name,' ',last_name) as name FROM ".DbTable::TABLE_KOTAK_REMITTERS." WHERE id = $id)"))
								 ->order('concat(name," ",last_name) ASC')
								 ->order('dob ASC');
				$results = $this->_db->fetchAll($select);                 
				return $results;
		}
		
		public function getKotakDuplicateBeneficiary(){
				$decryptionKey = App_DI_Container::get('DbConfig')->key;
	      $mobile = new Zend_Db_Expr("AES_DECRYPT(`mobile`,'".$decryptionKey."') as mobile"); 
        $select = $this->_db->select()
                 ->from(DbTable::TABLE_KOTAK_BENEFICIARIES,array('status',$mobile,'id','name',"DATE_FORMAT(date_created,'%d-%m-%Y') as date_created",'address_line1','count(id) as cnt'))
                 ->where('status = "active"')
                 ->group('name having count(id) > 1')
                 ->order('id DESC');
               
				$results = $this->_db->fetchAll($select);                 
				return $results;
		}
		
		public function getKotakDuplicateBeneficiaryDetail($id){
				$decryptionKey = App_DI_Container::get('DbConfig')->key;
	      $mobile = new Zend_Db_Expr("AES_DECRYPT(`mobile`,'".$decryptionKey."') as mobile"); 
	      $acno = new Zend_Db_Expr("AES_DECRYPT(`bank_account_number`,'".$decryptionKey."') as acno"); 
        $select = $this->_db->select()
                 ->from(DbTable::TABLE_KOTAK_BENEFICIARIES,array('id','status',$mobile,$acno,'name',"DATE_FORMAT(date_created,'%d-%m-%Y') as date_created"))
								 ->where('name IN(?)',new Zend_Db_Expr("(SELECT name FROM ".DbTable::TABLE_KOTAK_BENEFICIARIES." WHERE id = $id)"))
								 ->order('name ASC');
				
				$results = $this->_db->fetchAll($select);                 
				return $results;
		}
		
		public function getKotakDuplicateCardHolder(){
				$select = $this->_db->select()
                 ->from(DbTable::TABLE_KOTAK_CORP_CARDHOLDER,array('status','id','card_number','member_id','mobile','concat(first_name," ",last_name) as name',"DATE_FORMAT(date_created,'%d-%m-%Y') as date_created",'count(id) as cnt'))
                 ->group('name,last_name having count(id) > 1')
                 ->order('date_created DESC');
				$results = $this->_db->fetchAll($select);                 
				return $results;
		}
		
		public function getKotakDuplicateCardholderDetail($id){
				$select = $this->_db->select()
                 ->from(DbTable::TABLE_KOTAK_CORP_CARDHOLDER,array('status','id','card_number','member_id','mobile','concat(first_name," ",last_name) as name',"DATE_FORMAT(date_created,'%d-%m-%Y') as date_created"))
								 ->where('concat(first_name," ",last_name) IN(?)',new Zend_Db_Expr("(SELECT concat(first_name,' ',last_name) as name FROM ".DbTable::TABLE_KOTAK_CORP_CARDHOLDER." WHERE id = $id)"))
								 ->order('name ASC');
				$results = $this->_db->fetchAll($select);                 
				return $results;
		}
    
    /**
     * Returns a paginator or an array, depending on the value
     * provided for the $paginate field
     * 
     * @param Zend_Db_Select $select 
     * @param int $page
     * @param bool $paginate 
     * @access protected
     * @return mixed
     */
    public function paginateByArray($data, $page, $paginate)
    {
        if (NULL === $paginate) {
            $paginate = $this->_returnPaginators;
        }
       // print_r($data)."===".$page;
        if (empty($data) || $page<0) {
            return $data;
        }
        $paginator = Zend_Paginator::factory($data);
        $paginator->setCurrentPageNumber($page);
        $session = new Zend_Session_Namespace('App.Agent.Controller');
        $itemsPerPage = isset($session->items_per_page)?$session->items_per_page:0;
        
        if($itemsPerPage<1)
           $itemsPerPage = App_DI_Container::get('ConfigObject')->paginator->items_per_page;
        //echo $itemsPerPage.'===';    
        $paginator->setItemCountPerPage($itemsPerPage);
        //$paginator->setItemCountPerPage($numRecords);
        
        return $paginator;
    }
   
    public function getFakenames($data) {
		    $fakeNames = unserialize($data);
		    $fakeNamesData = $this->array_flatten($fakeNames);
		    $fakeData = array();
		    foreach($fakeNamesData as $key=>$val){
		    	$fakeData[] = $val;
		    }
		    $fakeData = implode(",",$fakeData);
				return $fakeData;
		}
	
		public function array_flatten($array) {
		    $res = array();
		    $res1 = array();
		    unset($array['QUALITY']);
		    foreach ($array as $key => $val) {
		        if (is_array($val)) {
		        		$res1[] = $val['ALIAS_NAME'];
		        		$res = array_merge($res, $this->array_flatten($val));
		        } 
		    }
		    return $res1;
		}
                
    public function getRatnakarRemitters($agentId = 0){
             $details = $this->_db->select()
                            ->from(DbTable::TABLE_RATNAKAR_REMITTERS,array('status','id','email','mobile','concat(name," ",last_name) as name',"DATE_FORMAT(date_created,'%d-%m-%Y') as date_created"))
                            ->where ("last_name <>''")
                            ->where ("aml_status ='".STATUS_IS_AML."'")
                            ->order('id DESC');
			        
                            $results = $this->_db->fetchAll($details);
                            $reportsData = array();
                            foreach($results AS $data){
                                $data['is_aml'] = STATUS_IS_AML;
                                $reportsData[] = $data;		
                            }
            return $reportsData;			       
    }
    
    public function getRatnakarSingleRemitter($remitId){
       $details = $this->_db->select()
                 ->from(DbTable::TABLE_RATNAKAR_REMITTERS,array('status','id','email','mobile','name', 'last_name',"DATE_FORMAT(date_created,'%d-%m-%Y') as date_created"))
                ->where('id = ?',$remitId)
                ->where ("aml_status ='".STATUS_IS_AML."'") 
                ->order('id DESC');
				
                $results = $this->_db->fetchAll($details);
                $reportsData = array();
                foreach($results AS $data){
                $select = $this->select()
                        ->from($this->_name." AS a" , array('*'))
                        ->where('a.first_name = "'. $data['name'] .'" AND a.second_name = "'. $data['last_name'].'"')
                        ->Orwhere('a.full_name = ?', $data['name'].' '.$data['last_name']) 
                        ->Orwhere('a.fake_names LIKE ?','%'.$data['name'].' '.$data['last_name'].'%');
                $row = $this->fetchRow($select);
                $data['is_aml'] = STATUS_AML;
                if($row['id']){
                    $data['is_aml'] = STATUS_IS_AML;
                    $reportsData['remitter'] = $data;		
                    $reportsData['aml'][] = $row;		
                }
						
       }
       return $reportsData;
    }
    
    public function getRatBeneficiaries($agentId = 0){
        $decryptionKey = App_DI_Container::get('DbConfig')->key;
        $email = new Zend_Db_Expr("AES_DECRYPT(`email`,'".$decryptionKey."') as email"); 
        $details = $this->_db->select()
                    ->from(DbTable::TABLE_RATNAKAR_BENEFICIARIES,array('status','id',$email,'name',"DATE_FORMAT(date_created,'%d-%m-%Y') as date_created","CONCAT(address_line1,' ',address_line2)"))
                    ->where ("aml_status ='".STATUS_IS_AML."'")
                    ->order('id DESC');

        $results = $this->_db->fetchAll($details);
        $reportsData = array();
        foreach($results AS $data){
            $data['is_aml'] = STATUS_IS_AML;
            $reportsData[] = $data;		
        }
        return $reportsData;
   }
   
   public function getRatnakarSingleBeneficiary($agentId = 0){
        $decryptionKey = App_DI_Container::get('DbConfig')->key;
        $email = new Zend_Db_Expr("AES_DECRYPT(`email`,'".$decryptionKey."') as email"); 
        $mobile = new Zend_Db_Expr("AES_DECRYPT(`mobile`,'".$decryptionKey."') as mobile");
        $details = $this->_db->select()
                 ->from(DbTable::TABLE_RATNAKAR_BENEFICIARIES,array('status','id',$email,'name',"DATE_FORMAT(date_created,'%d-%m-%Y') as date_created", $mobile))
                 ->where('id = ?',$agentId)
                 ->order('id DESC');

                $results = $this->_db->fetchAll($details);
                
        $reportsData = array();
        foreach($results AS $data){
        $select = $this->select()
                ->from($this->_name." AS a" , array('*'))
                ->where('concat(a.first_name," ",a.second_name) = ?',$data['name']) 
                ->Orwhere('a.full_name = ?',$data['name']) 
                ->Orwhere('a.fake_names LIKE ?','%'.$data['name'].'%');
            $row = $this->fetchRow($select);
            if($row['id']){
                    $data['is_aml'] = STATUS_IS_AML;
                    $reportsData['beneficiary'] = $data;		
                    $reportsData['aml'][] = $row;		
            }
        }
       return $reportsData;
    }
    
    public function getRatnakarCorpCardholders($agentId = 0){
        $details = $this->_db->select()
                    ->from(DbTable::TABLE_RAT_CORP_CARDHOLDER,array('status','id','card_number','medi_assist_id','mobile','concat(first_name," ",last_name) as name','email',"DATE_FORMAT(date_created,'%d-%m-%Y') as date_created"))
                    ->where ("first_name <>''")
                    ->where ("last_name <>''")
                    ->where ("aml_status ='".STATUS_IS_AML."'")
                    ->order('id DESC');

        $results = $this->_db->fetchAll($details);
        $reportsData = array();
        foreach($results AS $data){
            $data['is_aml'] = STATUS_IS_AML;
            $reportsData[] = $data;		
        }
        return $reportsData;
    }
    
    public function getRatnakarSingleCardHolder($ratId = 0){
    	 $details = $this->_db->select()
                 ->from(DbTable::TABLE_RAT_CORP_CARDHOLDER,array('status','id','card_number','medi_assist_id','mobile','first_name', 'last_name','email',"DATE_FORMAT(date_created,'%d-%m-%Y') as date_created"))
                 ->where('id = ?',$ratId)
                 ->order('id DESC');
                $results = $this->_db->fetchAll($details);
                
       $reportsData = array();
       foreach($results AS $data){
            $select = $this->select()
                    ->from($this->_name." AS a" , array('*'))
                    ->where('a.first_name = "'. $data['first_name'] .'" AND a.second_name = "'. $data['last_name'].'"')
                    ->Orwhere('a.full_name = ?', $data['first_name'].' '.$data['last_name']) 
                    ->Orwhere('a.fake_names LIKE ?','%'.$data['first_name'].' '.$data['last_name'].'%');
                    $row = $this->fetchRow($select);
                    if($row['id']){
                            $data['is_aml'] = STATUS_IS_AML;
                            $reportsData['cardholder'] = $data;		
                            $reportsData['aml'][] = $row;		
                    }
            }
       return $reportsData;
    }
    
    public function getBoiRemitters($remitId = 0){
        $details = $this->_db->select()
                       ->from(DbTable::TABLE_REMITTERS,array('status','id','email','mobile','name',"DATE_FORMAT(date_created,'%d-%m-%Y') as date_created"))
                       ->where ("aml_status ='".STATUS_IS_AML."'");
        
        if(!empty($remitId))
        {
            $details = $details->where('id = ?',$remitId);
        }
        
        $details = $details->order('id DESC');

        $results = $this->_db->fetchAll($details);
        $reportsData = array();
        foreach($results AS $data){
            $select = $this->select()
                    ->from($this->_name." AS a" , array('*'))
                    ->where('a.full_name LIKE ?',$data['name']) 
                    ->Orwhere('a.fake_names LIKE ?','%'.$data['name'].'%');
            $row = $this->fetchRow($select);
            $data['is_aml'] = STATUS_AML;
            if($row['id']){
                    $data['is_aml'] = STATUS_IS_AML;
                    if(!empty($remitId))
                    {
                        $reportsData['remitter'] = $data;		
                        $reportsData['aml'][] = $row;
                    }
                    else
                    {                        
                        $reportsData[] = $data;
                    }
            }
        }
        return $reportsData;
    }
    
    public function getBoiBeneficiaries($beneficiaryId = 0){
        $decryptionKey = App_DI_Container::get('DbConfig')->key;
        $email = new Zend_Db_Expr("AES_DECRYPT(`email`,'".$decryptionKey."') as email"); 
        $details = $this->_db->select()
                    ->from(DbTable::TABLE_BENEFICIARIES,array('status','id',$email,'name',"DATE_FORMAT(date_created,'%d-%m-%Y') as date_created","CONCAT(address_line1,' ',address_line2)"))
                    ->where ("aml_status ='".STATUS_IS_AML."'");
        
        if(!empty($beneficiaryId))
        {
            $details = $details->where('id = ?',$beneficiaryId);
        }        
        
        $details = $details->order('id DESC');

        $results = $this->_db->fetchAll($details);
        $reportsData = array();
        foreach($results AS $data){
          $select = $this->select()
                   ->from($this->_name." AS a" , array('*'))
                   ->where('a.full_name LIKE ?',$data['name']) 
                   ->Orwhere('a.fake_names LIKE ?','%'.$data['name'].'%');

            $row = $this->fetchRow($select);
            $data['is_aml'] = STATUS_AML;
            if($row['id']){
                    $data['is_aml'] = STATUS_IS_AML;
                    if(!empty($beneficiaryId))
                    {
                        $reportsData['beneficiary'] = $data;		
                        $reportsData['aml'][] = $row;
                    }
                    else
                    {
                        $reportsData[] = $data;
                    }
            }
        }
        return $reportsData;
   }
   
   public function getBoiCorpCardholders($cardholderId = 0){
        $details = $this->_db->select()
                    ->from(DbTable::TABLE_BOI_CORP_CARDHOLDER,array('status','id','card_number','member_id','mobile','concat(first_name," ",last_name) as name','email',"DATE_FORMAT(date_created,'%d-%m-%Y') as date_created"))
                    ->where ("first_name <>''")
                    ->where ("last_name <>''")
                    ->where ("aml_status ='".STATUS_IS_AML."'");
        
        if(!empty($cardholderId))
        {
            $details = $details->where('id = ?', $cardholderId);
        }
        
        $details = $details->order('id DESC');
        
        $results = $this->_db->fetchAll($details);
        $reportsData = array();
        foreach($results AS $data){
            $select = $this->select()
                    ->from($this->_name." AS a" , array('*'))
                    ->where('a.full_name LIKE ?',$data['name']) 
                    ->Orwhere('a.fake_names LIKE ?','%'.$data['name'].'%');
            $row = $this->fetchRow($select);
            $data['is_aml'] = STATUS_AML;
            if($row['id']){
                $data['is_aml'] = STATUS_IS_AML;
                if(!empty($cardholderId))
                {
                    $reportsData['cardholder'] = $data;		
                    $reportsData['aml'][] = $row;
                }
                else
                {
                    $reportsData[] = $data;
                }
            }
        }
        return $reportsData;
    }
}