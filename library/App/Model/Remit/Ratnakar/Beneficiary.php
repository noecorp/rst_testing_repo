<?php
/*
 * ratnakar beneficiary model
 */
class Remit_Ratnakar_Beneficiary extends Remit_Ratnakar
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
    protected $_name = DbTable::TABLE_RATNAKAR_BENEFICIARIES;
    
    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
//    protected $_rowClass = 'App_Table_Beneficiaries';
    
    

  	public function checkbeneficiary($data){
	            $accountNumber = isset($data['bank_account_number'])?trim($data['bank_account_number']):'';
	            $encryptionKey = App_DI_Container::get('DbConfig')->key;
	       
	        if($accountNumber!='')
	            $param['bank_account_number'] = new Zend_Db_Expr("AES_ENCRYPT('".$accountNumber."','".$encryptionKey."')");
	
	        App_Logger::log('Bank Account Number encrypted : ' .$accountNumber, Zend_Log::INFO);
	        App_Logger::log('IFSC Code : ' .$data['ifsc_code'], Zend_Log::INFO);
	        App_Logger::log('Remitter Id : ' .$data['remitter_id'], Zend_Log::INFO);
	         
	     $select = $this->select()
	                ->from(DbTable::TABLE_RATNAKAR_BENEFICIARIES . " as b", array('id' ))
	                ->where('b.remitter_id =?', $data['remitter_id'])
	                ->where('b.ifsc_code =?', $data['ifsc_code'])
	                ->where('b.bank_account_number =?', $param['bank_account_number']) ;
	            try { 
	         		$res = $this->fetchRow($select);
	         
	         		if(isset($res['id']) && $res['id'] > 0){
	         			App_Logger::log('checkbeneficiary count: ' .$res['id'], Zend_Log::INFO);
	         			return $res['id'];
	         		}else{
	         			return 0;
	         		}
	         
	            }
	            catch (Exception $e ) {
	                App_Logger::log($e->getMessage(), Zend_Log::ERR);
	                throw new Exception($e->getMessage()); 
	            }  
	        	         
	} 
          
    
    public function addbeneficiary($param){
        
        $accountNumber = isset($param['bank_account_number'])?trim($param['bank_account_number']):'';
        $email = isset($param['email'])?trim($param['email']):'';
        $mobile = isset($param['mobile'])?trim($param['mobile']):'';
        $branchAddress = isset($param['branch_address'])?addslashes(trim($param['branch_address'])):'';
        $encryptionKey = App_DI_Container::get('DbConfig')->key;
       
        if($accountNumber!='')
            $param['bank_account_number'] = new Zend_Db_Expr("AES_ENCRYPT('".$accountNumber."','".$encryptionKey."')");
        if($email!='')
            $param['email'] = new Zend_Db_Expr("AES_ENCRYPT('".$email."','".$encryptionKey."')");
        if($mobile!='')
            $param['mobile'] = new Zend_Db_Expr("AES_ENCRYPT('".$mobile."','".$encryptionKey."')");
        if($branchAddress!='')
            $param['branch_address'] = new Zend_Db_Expr("AES_ENCRYPT('".$branchAddress."','".$encryptionKey."')");

      try{
          $resp = $this->insert($param); // adding remitter to t_beneficiaries   
          $beneID =  $this->_db->lastInsertId(DbTable::TABLE_RATNAKAR_BENEFICIARIES, 'id');        
          return $beneID;           
          }
        catch (Exception $e ) {
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                throw new Exception($e->getMessage()); 
            }  

    }          
      
    
    
    /**
     * Overrides findById() in App_Model
     * 
     * @param int $userId 
     * @access public
     * @return array
     */
    public function getBeneficiaryDetails($id){
         $decryptionKey = App_DI_Container::get('DbConfig')->key;
         $bankAccountNumber = new Zend_Db_Expr("AES_DECRYPT(`b`.`bank_account_number`,'".$decryptionKey."') as bank_account_number");
         $branchAddress = new Zend_Db_Expr("AES_DECRYPT(`b`.`branch_address`,'".$decryptionKey."') as branch_address");
         $mobile = new Zend_Db_Expr("AES_DECRYPT(`b`.`mobile`,'".$decryptionKey."') as mobile");
         $email = new Zend_Db_Expr("AES_DECRYPT(`b`.`email`,'".$decryptionKey."') as email");
       
         $details = $this->select()
                ->from(DbTable::TABLE_RATNAKAR_BENEFICIARIES." as b", array('id', 'remitter_id', 'name', 'nick_name', 'ifsc_code', $bankAccountNumber, 'bank_name', 'branch_name', 'branch_city', $branchAddress, 'bank_account_type', 'address_line1', 'address_line2', $mobile, $email,'beneficiary_id','rat_status', 'by_agent_id', 'by_ops_id', 'date_created', 'date_modified', 'status'))
                ->setIntegrityCheck(false)
                ->joinleft(DbTable::TABLE_RATNAKAR_REMITTERS.' as r',"b.remitter_id = r.id ",array('r.name as remitter_name', 'r.mobile as remitter_mobile', 'r.email as remitter_email','r.product_id','r.static_code'))
                ->joinleft(DbTable::TABLE_BANK_IFSC.' as bi','bi.ifsc_code= b.ifsc_code',array('bank_name'))
                ->where("b.id=$id");
   
         
         return $this->fetchRow($details);
    }
    
    public function updateBeneficiaryDetails($data,$id){
        $update = $this->update($data,"id = $id");
        if($update)
                 return TRUE;
             else 
                 return FALSE;
    }
    
      public function getBeneficiaryAccountNo($params){
         
          $encryptionKey = App_DI_Container::get('DbConfig')->key;
          $bankAccountNumber = new Zend_Db_Expr("AES_ENCRYPT('".$params['bank_account_number']."','".$encryptionKey."')");
        
         $details = $this->select()
                ->from(DbTable::TABLE_RATNAKAR_BENEFICIARIES." as b", array('id', $bankAccountNumber ))
                ->setIntegrityCheck(false)
                ->joinleft(DbTable::TABLE_RATNAKAR_REMITTERS.' as r',"b.remitter_id = r.id ",array())
                ->where("b.remitter_id = '".$params['remitter_id']."'")
                ->where("b.ifsc_code = '".$params['ifsc_code']."'")
                ->where("b.bank_account_number = ".$bankAccountNumber)
                ->where("b.status = '".STATUS_ACTIVE."'");
         $res = $this->fetchRow($details);
         if(isset($res) && !empty($res)){
	    throw new Exception(ErrorCodes::BENE_WITH_SAME_ACCOUNT_RESPONSE_MSG, ErrorCodes::BENE_WITH_SAME_ACCOUNT_RESPONSE_CODE);
         }
         else
         {
             return TRUE;
         }
                
         
    }
    
    
    public function getBeneficiaryRegistrations($param)
    {
        $from = isset($param['from']) ? $param['from'] : '';
        $to = isset($param['to']) ? $param['to'] : '';
        
        $decryptionKey = App_DI_Container::get('DbConfig')->key;
        $bankAccountNumber = new Zend_Db_Expr("AES_DECRYPT(`b`.`bank_account_number`,'".$decryptionKey."') as bank_account_number");
        $branchAddress = new Zend_Db_Expr("AES_DECRYPT(`b`.`branch_address`,'".$decryptionKey."') as branch_address");
        $mobile = new Zend_Db_Expr("AES_DECRYPT(`b`.`mobile`,'".$decryptionKey."') as mobile");
        $email = new Zend_Db_Expr("AES_DECRYPT(`b`.`email`,'".$decryptionKey."') as email");

        $select = $this->select();
        $select->from(DbTable::TABLE_RATNAKAR_BENEFICIARIES." as b", array('id', 'remitter_id', 'name', 'nick_name', 'ifsc_code', $bankAccountNumber, 'bank_name', 'branch_name', 'branch_city', $branchAddress, 'bank_account_type', 'address_line1', 'address_line2', $mobile, $email, 'by_agent_id', 'by_ops_id', 'date_created', 'date_modified', 'status'));
        $select->setIntegrityCheck(false);
        $select->joinleft(DbTable::TABLE_RATNAKAR_REMITTERS.' as r',"b.remitter_id = r.id ",array('r.name as remitter_name', 'r.mobile as remitter_mobile', 'r.email as remitter_email','r.product_id','r.static_code'));
        $select->joinleft(DbTable::TABLE_BANK_IFSC.' as bi','bi.ifsc_code= b.ifsc_code',array('bank_name'));;
        
        if ($from != '' && $to != '') {
            $select->where("b.date_created >= '" . $param['from'] . "'");
            $select->where("b.date_created <= '" . $param['to'] . "'");
        }
        
        $select->order("b.name ASC");

        return $this->fetchAll($select);
    }
    
    public function exportGetBeneficiaryRegistrations($param)
    {
        $data = $this->getBeneficiaryRegistrations($param);

        $retData = array();

        if (!empty($data)) {
            foreach ($data as $key => $data) {

                $retData[$key]['name'] = $data['name'];
                $retData[$key]['nick_name'] = $data['nick_name'];
                $retData[$key]['mobile'] = $data['mobile'];
                $retData[$key]['ifsc_code'] = $data['ifsc_code'];
                $retData[$key]['bank_account_number'] = $data['bank_account_number'];
                $retData[$key]['bank_name'] = $data['bank_name'];
                $retData[$key]['branch_name'] = $data['branch_name'];
                $retData[$key]['branch_city'] = $data['branch_city'];
                $retData[$key]['bank_account_type'] = $data['bank_account_type'];
                $retData[$key]['email'] = $data['email'];
                $retData[$key]['status'] = $data['status'];
                
            }
        }

        return $retData;
    }
    
    public function updateAmlRatnakarBeneficiary(){
        $details = $this->_db->select()
                       ->from($this->_name, array('id', 'name'))
                       ->where ("status ='".STATUS_ACTIVE."'")
                       ->where ("aml_status ='".STATUS_AML."'")
                       ->order('date_created ASC');

                       $results = $this->_db->fetchAll($details);
                       $reportsData = array();
                       foreach($results AS $data){
                           $select = $this->_db->select()
                                       ->from(DbTable::TABLE_AML_MASTER." AS a" , array('*'))
                                       ->where('concat(a.first_name," ",a.second_name) = ?',$data['name']) 
                                       ->Orwhere('a.full_name = ?',$data['name']) 
                                       ->Orwhere('a.fake_names LIKE ?','%'.$data['name'].'%');

                               $row = $this->_db->fetchRow($select);

                               if($row['id']){
                                   $this->_db->update($this->_name, array('aml_status' => STATUS_IS_AML), 'id='.$data['id']);	
                               } else {
                                   $this->_db->update($this->_name, array('aml_status' => STATUS_AML_UPDATE), 'id='.$data['id']);	
                               }
                          }
        return $reportsData;
    }
    public function queryBeneficiary($params)
    {
        //$params = array('RemitterFlag'=>'E', 'RemitterCode'=>'riya@transerv.co.in', 'BeneficiaryCode'=>'ewerwr');
        $validateArr = array();
        
        $valid = $this->isValid($params);
        
        if (!$valid) {
            $errMsg = $this->getError();
            return $errMsg;
        } 

        $encryptionKey = App_DI_Container::get('DbConfig')->key;
        $decryptionKey = App_DI_Container::get('DbConfig')->key;
        $bankAccountNumber = new Zend_Db_Expr("AES_DECRYPT(`b`.`bank_account_number`,'".$decryptionKey."') as bank_account_number");
        $branchAddress = new Zend_Db_Expr("AES_DECRYPT(`b`.`branch_address`,'".$decryptionKey."') as branch_address");
        $mobile = new Zend_Db_Expr("AES_DECRYPT(`b`.`mobile`,'".$decryptionKey."') as mobile");
        $email = new Zend_Db_Expr("AES_DECRYPT(`b`.`email`,'".$decryptionKey."') as email");

        $details = $this->select()
               ->from(DbTable::TABLE_RATNAKAR_BENEFICIARIES." as b", array('id', 'ifsc_code', $bankAccountNumber, 'bank_name', 'branch_name', 'branch_city', $branchAddress, 'bank_account_type', 'address_line1', 'address_line2', $mobile, $email))
               ->setIntegrityCheck(false)
               ->joinleft(DbTable::TABLE_RATNAKAR_REMITTERS.' as r',"b.remitter_id = r.id ",array('r.name as remitter_name'))
               ->joinleft(DbTable::TABLE_BANK_IFSC.' as bi','bi.ifsc_code= b.ifsc_code',array('bank_name'));
        
        if($params['RemitterFlag'] == REMITTER_FLAG_EMAIL)
        {
            $params['RemitterCode'] = new Zend_Db_Expr("AES_ENCRYPT('".$params['RemitterCode']."','".$encryptionKey."')");
            $details = $details->where("b.email=".$params['RemitterCode']);
        }
        else
        {
            //$details = $details->where("email='".$param['RemitterCode']."'");
        }
        
        //$details = $details->where("beneficary_code='".$param['RemitterCode']."'");        
        //echo $details; exit;

        $result = $this->fetchAll($details);
        
        $retData = array();
        $i = 0;
        
        if (!empty($result)) {
            foreach ($result as $data) 
            {
                $retData[$i]['bene_code'] = '';
                $retData[$i]['title'] = '';
                $retData[$i]['first_name'] = '';
                $retData[$i]['middle_name'] = '';
                $retData[$i]['last_name'] = '';
                $retData[$i]['gender'] = '';
                $retData[$i]['dob'] = '';                
                $retData[$i]['mobile'] = $data['mobile'];
                $retData[$i]['mobile2'] = '';
                $retData[$i]['email'] = $data['email'];
                $retData[$i]['mother_maiden_name'] = '';
                $retData[$i]['landline'] = '';
                $retData[$i]['address_line1'] = $data['address_line1'];
                $retData[$i]['address_line2'] = $data['address_line2'];
                $retData[$i]['city'] = '';
                $retData[$i]['state'] = '';
                $retData[$i]['country'] = '';
                $retData[$i]['pincode'] = '';
                $retData[$i]['bank_name'] = $data['bank_name'];
                $retData[$i]['branch_name'] = $data['branch_name'];
                $retData[$i]['bank_city'] = $data['branch_city'];
                $retData[$i]['bank_state'] = '';
                $retData[$i]['ifsc_code'] = $data['ifsc_code'];
                $retData[$i]['bank_account_number'] = $data['bank_account_number'];
                $retData[$i]['response_code'] = SUCCESS_RESPONSE_CODE;
                $retData[$i]['response_message'] = SUCCESS_QUERY_BENEFICIARY_MESSAGE;
                
                $i++;
            }
        }
        return $retData;
    }
    
    public function isValid($param)
    {
        if($param['RemitterFlag'] != REMITTER_FLAG_EMAIL && $param['RemitterFlag'] != REMITTER_FLAG_PARTNER_REFERENCE_NUMBER)
        {
            $this->setError('Invalid Remitter Flag');
            return FALSE;
        }
        
        if(empty($param['RemitterCode']))
        {
            $this->setError('Invalid Remitter Code');
            return FALSE;
        }
        elseif($param['RemitterFlag'] == REMITTER_FLAG_EMAIL && (!preg_match("/([\w\-]+\@[\w\-]+\.[\w\-]+)/",$param['RemitterCode'])))
        {
            $this->setError('Invalid Email');            
            return FALSE;
        }
        
        if(isset($param['BeneficiaryCode']))
        {
            if(empty($param['BeneficiaryCode']))
            {
                $this->setError('Invalid Beneficiary Code');
                return FALSE;
            }
        }
        return TRUE;
    }
    
    public function queryBeneficiaryList($params)
    {
        //$params = array('RemitterFlag'=>'E', 'RemitterCode'=>'riya@transerv.co.in');
        
        $valid = $this->isValid($params);
        
        if (!$valid) {
            $errMsg = $this->getError();
            return $errMsg;
        }
        
        $encryptionKey = App_DI_Container::get('DbConfig')->key;
        $decryptionKey = App_DI_Container::get('DbConfig')->key;
                
        $bankAccountNumber = new Zend_Db_Expr("AES_DECRYPT(`b`.`bank_account_number`,'".$decryptionKey."') as bank_account_number");
        $branchAddress = new Zend_Db_Expr("AES_DECRYPT(`b`.`branch_address`,'".$decryptionKey."') as branch_address");
        $mobile = new Zend_Db_Expr("AES_DECRYPT(`b`.`mobile`,'".$decryptionKey."') as mobile");
        $email = new Zend_Db_Expr("AES_DECRYPT(`b`.`email`,'".$decryptionKey."') as email");

        $details = $this->select()
               ->from(DbTable::TABLE_RATNAKAR_BENEFICIARIES." as b", array('id', 'ifsc_code', $bankAccountNumber, 'bank_name', 'branch_name', 'branch_city', $branchAddress, 'bank_account_type', 'address_line1', 'address_line2', $mobile, $email))
               ->setIntegrityCheck(false)
               ->joinleft(DbTable::TABLE_RATNAKAR_REMITTERS.' as r',"b.remitter_id = r.id ",array('r.name as remitter_name'))
               ->joinleft(DbTable::TABLE_BANK_IFSC.' as bi','bi.ifsc_code= b.ifsc_code',array('bank_name'));
        
        if($params['RemitterFlag'] == REMITTER_FLAG_EMAIL)
        {
            $params['RemitterCode'] = new Zend_Db_Expr("AES_ENCRYPT('".$params['RemitterCode']."','".$encryptionKey."')");
            $details = $details->where("b.email=".$params['RemitterCode']);
        }
        else
        {
            //$details = $details->where("b.email='".$params['RemitterCode']."'");
        }

        //echo $details; exit;

        $result = $this->fetchAll($details);
        $count = count($result);
        
        $beneDetail = array();
        $i = 0;
        
        if (!empty($result) && $count > 0) {
            foreach ($result as $data) {
                $beneDetail[$i]['beneficiary_code'] = '';
                $beneDetail[$i]['first_name'] = '';
                $beneDetail[$i]['last_name'] = '';
                $beneDetail[$i]['bank_name'] = $data['bank_name'];
                $beneDetail[$i]['ifsc_code'] = $data['ifsc_code'];
                $beneDetail[$i]['bank_account_number'] = $data['bank_account_number'];  
                
                $i++;
            }
            $beneDetail['beneficiary_count'] = $count;
            $beneDetail['response_code'] = SUCCESS_RESPONSE_CODE;
            $beneDetail['response_message'] = SUCCESS_QUERY_BENEFICIARY_LIST_MESSAGE;
        }
        return $beneDetail;
    }
   
     public function beneficiaryregistration($param){
       
        try{
            $param = array_filter($param);
            if (!empty($param)) { 
                // add empty($param['remitter_code']) || empty($param['txn_ref_no']) || empty($param['first_name']) || empty($param['last_name']) || empty($param['sms_flag'])
                if(empty($param['mobile']) || empty($param['ifsc_code']) || empty($param['bank_account_number'])){
                    throw new Exception('Data missing for beneficiary registration.');
                }
            }else{
                throw new Exception('Data missing for beneficiary registration.');
            }
            
            $this->addbeneficiary($param);
          
        }catch (Exception $e ) {
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                throw new Exception($e->getMessage()); exit;
        }  
    } 

    
     public function addbeneficiaryAPI($param){
        
        $accountNumber = isset($param['bank_account_number'])?trim($param['bank_account_number']):'';
        $email = isset($param['email'])?trim($param['email']):'';
        $mobile = isset($param['mobile'])?trim($param['mobile']):'';
        $branchAddress = isset($param['branch_address'])?addslashes(trim($param['branch_address'])):'';
        $encryptionKey = App_DI_Container::get('DbConfig')->key;
        
        $bankifsc = new BanksIFSC();
        $bankArr = $bankifsc->getDetailsByIFSCCode($param['ifsc_code'], TXN_NEFT);
        
        if($accountNumber!='')
            $param['bank_account_number'] = new Zend_Db_Expr("AES_ENCRYPT('".$accountNumber."','".$encryptionKey."')");
        if($email!='')
            $param['email'] = new Zend_Db_Expr("AES_ENCRYPT('".$email."','".$encryptionKey."')");
        if($mobile!='')
            $param['mobile'] = new Zend_Db_Expr("AES_ENCRYPT('".$mobile."','".$encryptionKey."')");
        if($branchAddress!='')
            $param['branch_address'] = new Zend_Db_Expr("AES_ENCRYPT('".$branchAddress."','".$encryptionKey."')");
        
        $param['bank_name'] = $bankArr['bank_name'];
        $param['branch_name'] = $bankArr['branch_name'];
        $param['branch_address'] = $bankArr['address'];
        $param['branch_city'] = $bankArr['city'];
        
      try{
          $resp = $this->insert($param); // adding remitter to t_beneficiaries 
          $beneID = $this->_db->lastInsertId(DbTable::TABLE_RATNAKAR_BENEFICIARIES,'id');
          $beneCode = Util::getBeneCodeFromId($beneID);
          $this->updateBeneficiaryDetails(array('bene_code'=> $beneCode),$beneID);
          return $beneCode;           
          }
        catch (Exception $e ) {
                App_Logger::log($e->getMessage(), Zend_Log::ERR); 
		throw new Exception(ErrorCodes::ERROR_BENE_REGISTRATION_FAIL_MSG, ErrorCodes::ERROR_BENE_REGISTRATION_FAIL_CODE);
		exit;
            }  
    }
    
     public function getBeneInfo($param) {
        $select = $this->getBeneSearchSql($param);
        return $this->fetchAll($select);
    }
    
    public function getBeneInfoRow($param) {
        $select = $this->getBeneSearchSql($param);
        return $this->fetchRow($select);
    }
    	
    
    public function getBeneSearchSql($param) {
         $decryptionKey = App_DI_Container::get('DbConfig')->key;
         $bankAccountNumber = new Zend_Db_Expr("AES_DECRYPT(`rb`.`bank_account_number`,'".$decryptionKey."') as bank_account_number");
         $branchAddress = new Zend_Db_Expr("AES_DECRYPT(`rb`.`branch_address`,'".$decryptionKey."') as branch_address");
         $mobile = new Zend_Db_Expr("AES_DECRYPT(`rb`.`mobile`,'".$decryptionKey."') as mobile");
         $email = new Zend_Db_Expr("AES_DECRYPT(`rb`.`email`,'".$decryptionKey."') as email");
        
        $remitterID = isset($param['remitter_id']) ? $param['remitter_id'] : '';
        $beneCode = isset($param['bene_code']) ? $param['bene_code'] : '';
        $queryRefNo = isset($param['queryrefno']) ? $param['queryrefno'] : '';
        $status = isset($param['status']) ? $param['status'] : '';
        $orderBy = isset($param['order']) ? $param['order'] : '';
        $txnRefNum = isset($param['txnrefnum']) ? $param['txnrefnum'] : '';
        
        $select = $this->select();
        $select->from(DbTable::TABLE_RATNAKAR_BENEFICIARIES . " as rb", 
                array('rb.id as id','rb.title','rb.name as first_name','rb.middle_name','rb.last_name','rb.gender','rb.date_of_birth',$mobile,'rb.mobile2',$email,'rb.mother_maiden_name','rb.landline','rb.address_line1','rb.address_line2','rb.city','rb.state','rb.country','rb.pincode','rb.bank_name','rb.branch_name','rb.branch_city',$branchAddress,'rb.ifsc_code',$bankAccountNumber,'rb.bene_code','rb.queryrefno','rb.txnrefnum'));
        $select->joinleft(DbTable::TABLE_BANK_IFSC.' as bi','bi.ifsc_code= rb.ifsc_code',array('bank_name','state'));
        $select->setIntegrityCheck(false);
        
        if ($remitterID != ''){
            $select->where("rb.remitter_id = '" . $remitterID . "'");
        }
        if ($beneCode != ''){
            $select->where("rb.bene_code = '" . $beneCode . "'");
        }
        if (($queryRefNo != '') || ($txnRefNum != '')) {
            $select->where("rb.queryrefno = '" . $queryRefNo . "' OR rb.txnrefnum = '" . $txnRefNum . "'");
        }
        if ($status != ''){
            $select->where("rb.status = '" . $status . "'");
        }
        if ($orderBy != ''){
            $select->order($orderBy);
        }else{
            $select->order("rb.id");
        }    
        return $select;
    }
   
     public function getBeneficiaryDetailsByCode($beneCode,$remitterID = 0){
         $decryptionKey = App_DI_Container::get('DbConfig')->key;
         $bankAccountNumber = new Zend_Db_Expr("AES_DECRYPT(`b`.`bank_account_number`,'".$decryptionKey."') as bank_account_number");
         $branchAddress = new Zend_Db_Expr("AES_DECRYPT(`b`.`branch_address`,'".$decryptionKey."') as branch_address");
         $mobile = new Zend_Db_Expr("AES_DECRYPT(`b`.`mobile`,'".$decryptionKey."') as mobile");
         $email = new Zend_Db_Expr("AES_DECRYPT(`b`.`email`,'".$decryptionKey."') as email");
         
         $details = $this->select()
                ->from(DbTable::TABLE_RATNAKAR_BENEFICIARIES." as b", array('id', 'remitter_id', 'name', 'nick_name', 'ifsc_code', $bankAccountNumber, 'bank_name', 'branch_name', 'branch_city', $branchAddress, 'bank_account_type', 'address_line1', 'address_line2', $mobile, $email, 'by_agent_id', 'by_ops_id', 'date_created', 'date_modified', 'status'))
                ->setIntegrityCheck(false)
                ->joinleft(DbTable::TABLE_RATNAKAR_REMITTERS.' as r',"b.remitter_id = r.id ",array('r.name as remitter_name', 'r.mobile as remitter_mobile', 'r.email as remitter_email','r.product_id','r.static_code'))
                ->joinleft(DbTable::TABLE_BANK_IFSC.' as bi','bi.ifsc_code= b.ifsc_code',array('bank_name'))
                ->where("b.bene_code =?", $beneCode)
                ->where("b.status = '".STATUS_ACTIVE."'");
                
         if($remitterID > 0)
         {
             $details->where("b.remitter_id =?", $remitterID);
         }

         return $this->fetchRow($details);
    }
    
    public function getBeneficiaryByTransRefNo($params){
         
         $details = $this->select()
                ->from(DbTable::TABLE_RATNAKAR_BENEFICIARIES." as b", array('id' ))
                ->where("b.txnrefnum = ".$params['txnrefnum'])
                ->where("b.bank_id = ".$params['bank_id']);
                //->where("b.status = '".STATUS_ACTIVE."'");
         $res = $this->fetchRow($details);
         if(isset($res) && !empty($res)){
	    throw new Exception(ErrorCodes::ERROR_EDIGITAL_TRAN_REF_NO_USED_MSG, ErrorCodes::ERROR_EDIGITAL_TRAN_REF_NO_USED_CODE);
         }
         else
         {
             return TRUE;
         }
                
         
    }
}
