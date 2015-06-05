<?php
/*
 * kotak beneficiary model
 */
class Remit_Kotak_Beneficiary extends Remit_Kotak
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
    protected $_name = DbTable::TABLE_KOTAK_BENEFICIARIES;
    
    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
//    protected $_rowClass = 'App_Table_Beneficiaries';
    
    

   
          
    
    public function addbeneficiary($param){
        
        $accountNumber = isset($param['bank_account_number'])?trim($param['bank_account_number']):'';
        $email = isset($param['email'])?trim($param['email']):'';
        $mobile = isset($param['mobile'])?trim($param['mobile']):'';
        $branchAddress = isset($param['branch_address'])?addslashes(trim($param['branch_address'])):'';
        $encryptionKey = App_DI_Container::get('DbConfig')->key;
        //exit;
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
        $beneID =  $this->_db->lastInsertId(DbTable::TABLE_KOTAK_BENEFICIARIES, 'id');        
        return $beneID;  
        
      }
        catch (Exception $e ) {
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                throw new Exception($e->getMessage()); exit;
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
                ->from(DbTable::TABLE_KOTAK_BENEFICIARIES." as b", array('id', 'remitter_id', 'name', 'nick_name', 'ifsc_code', $bankAccountNumber, 'bank_name', 'branch_name', 'branch_city', $branchAddress, 'bank_account_type', 'address_line1', 'address_line2', $mobile, $email, 'by_agent_id', 'by_ops_id', 'date_created', 'date_modified', 'status'))
                ->setIntegrityCheck(false)
                ->joinleft(DbTable::TABLE_KOTAK_REMITTERS.' as r',"b.remitter_id = r.id ",array('r.name as remitter_name', 'r.mobile as remitter_mobile', 'r.email as remitter_email','r.product_id'))
                ->joinleft(DbTable::TABLE_BANK_IFSC.' as bi','bi.ifsc_code= b.ifsc_code',array('bank_name'))
                ->where("b.id=$id");
//         echo $details; exit;
         
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
                ->from(DbTable::TABLE_KOTAK_BENEFICIARIES." as b", array('id', $bankAccountNumber ))
                ->setIntegrityCheck(false)
                ->joinleft(DbTable::TABLE_KOTAK_REMITTERS.' as r',"b.remitter_id = r.id ",array())
                ->where("b.remitter_id = '".$params['remitter_id']."'")
                ->where("b.ifsc_code = '".$params['ifsc_code']."'")
                ->where("b.bank_account_number = ".$bankAccountNumber)
                ->where("b.status = '".STATUS_ACTIVE."'");
         $res = $this->fetchRow($details);
         if(isset($res) && !empty($res)){
            throw new Exception (ErrorCodes::BENE_WITH_SAME_ACCOUNT_RESPONSE_MSG, ErrorCodes::BENE_WITH_SAME_ACCOUNT_RESPONSE_CODE); 
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
        $select->from(DbTable::TABLE_KOTAK_BENEFICIARIES." as b", array('id', 'remitter_id', 'name', 'nick_name', 'ifsc_code', $bankAccountNumber, 'bank_name', 'branch_name', 'branch_city', $branchAddress, 'bank_account_type', 'address_line1', 'address_line2', $mobile, $email, 'by_agent_id', 'by_ops_id', 'date_created', 'date_modified', 'status'));
        $select->setIntegrityCheck(false);
        $select->joinleft(DbTable::TABLE_KOTAK_REMITTERS.' as r',"b.remitter_id = r.id ",array('r.name as remitter_name', 'r.mobile as remitter_mobile', 'r.email as remitter_email','r.product_id','r.static_code'));
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
    
    public function updateAmlKotakBeneficiary(){
        $details = $this->_db->select()
                       ->from($this->_name,array('id', 'name'))
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
    
    /*
     * check duploacte by mobile number
     */
    public function checkDuplicateTransNum($param) {
        $agentID = isset($param['agent_id']) ? $param['agent_id'] : '';
        $select = $this->select();
        $select->from($this->_name, array('id'));
        $select->where('txnrefnum = ?', $param['txnrefnum']); 
        if($agentID !=''){
        $select->where('by_agent_id = ?', $agentID);     
        }
        $rs = $this->fetchRow($select);
        if( !empty($rs) ){
            return TRUE;
        }else{
            return FALSE;
        }

    }

    
    public function addbeneficiaryAPI($param){
        
        $accountNumber = isset($param['bank_account_number'])?trim($param['bank_account_number']):'';
        $email = isset($param['email'])?trim($param['email']):'';
        $mobile = isset($param['mobile'])?trim($param['mobile']):'';
        $branchAddress = isset($param['branch_address'])?addslashes(trim($param['branch_address'])):'';
        $encryptionKey = App_DI_Container::get('DbConfig')->key;
        
        $bankifsc = new BanksIFSC();
        $bankDetailArr = $bankifsc->getDetailsByIFSCCode($param['ifsc_code'], TXN_IMPS);
        
        if($accountNumber!='')
            $param['bank_account_number'] = new Zend_Db_Expr("AES_ENCRYPT('".$accountNumber."','".$encryptionKey."')");
        if($email!='')
            $param['email'] = new Zend_Db_Expr("AES_ENCRYPT('".$email."','".$encryptionKey."')");
        if($mobile!='')
            $param['mobile'] = new Zend_Db_Expr("AES_ENCRYPT('".$mobile."','".$encryptionKey."')");
        if($branchAddress!='')
            $param['branch_address'] = new Zend_Db_Expr("AES_ENCRYPT('".$branchAddress."','".$encryptionKey."')");
        
        $param['bank_name'] = $bankDetailArr['bank_name'];
        $param['branch_name'] = $bankDetailArr['branch_name'];
        $param['branch_city'] = $bankDetailArr['city'];
        $param['branch_address'] = $bankDetailArr['address'];
       
        try{
            $resp = $this->insert($param); // adding remitter to t_beneficiaries 
            $beneID = $this->_db->lastInsertId(DbTable::TABLE_KOTAK_BENEFICIARIES,'id');
            $beneCode = Util::getBeneCodeFromId($beneID);
            $this->updateBeneficiaryDetails(array('bene_code'=> $beneCode),$beneID);
            return $beneCode;    
        }catch (Exception $e ) {
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                throw new Exception($e->getMessage(), ErrorCodes::ERROR_BENE_REGISTRATION_FAIL_CODE); exit;
        }  
    }
    
    public function getBeneInfoRow($param) {
        $select = $this->getBeneSearchSql($param);
        return $this->fetchRow($select);
    }
    
    public function getBeneInfo($param) {
        $select = $this->getBeneSearchSql($param);
        return $this->fetchAll($select);
    }
    
    public function getBeneSearchSql($param) {
        $decryptionKey = App_DI_Container::get('DbConfig')->key;
        $bankAccountNumber = new Zend_Db_Expr("AES_DECRYPT(`kb`.`bank_account_number`,'".$decryptionKey."') as bank_account_number");
        $branchAddress = new Zend_Db_Expr("AES_DECRYPT(`kb`.`branch_address`,'".$decryptionKey."') as branch_address");
        $mobile = new Zend_Db_Expr("AES_DECRYPT(`kb`.`mobile`,'".$decryptionKey."') as mobile");
        $email = new Zend_Db_Expr("AES_DECRYPT(`kb`.`email`,'".$decryptionKey."') as email");
        
        $remitterID = isset($param['remitter_id']) ? $param['remitter_id'] : '';
        $beneCode = isset($param['bene_code']) ? $param['bene_code'] : '';
        $status = isset($param['status']) ? $param['status'] : '';
        $orderBy = isset($param['order']) ? $param['order'] : '';
        $txnRefNum = isset($param['txnrefnum']) ? $param['txnrefnum'] : '';
        $queryRefNo = isset($param['queryrefno']) ? $param['queryrefno'] : '';

        $beneId = isset($param['id']) ? $param['id'] : '';
        
        $select = $this->select();
        $select->from(DbTable::TABLE_KOTAK_BENEFICIARIES . " as kb", 
                array('kb.id as id','kb.title','kb.name','kb.name as nick_name',$mobile,$email,'kb.address_line1','kb.address_line2','kb.bank_name','kb.branch_name','kb.branch_city',$branchAddress,'kb.ifsc_code',$bankAccountNumber,'kb.bene_code'));
        $select->joinleft(DbTable::TABLE_BANK_IFSC.' as bi','bi.ifsc_code= kb.ifsc_code',array('bank_name'));
        $select->setIntegrityCheck(false);
        
        if ($beneId != ''){
            $select->where("kb.id = '" . $beneId . "'");
        }
        if ($remitterID != ''){
            $select->where("kb.remitter_id = '" . $remitterID . "'");
        }
        if ($beneCode != ''){
            $select->where("kb.bene_code = '" . $beneCode . "'");
        }
        if ($queryRefNo != ''){
            $select->where("kb.queryrefno = '" . $queryRefNo . "'");
        }

        if ($status != ''){
            $select->where("kb.status = '" . $status . "'");
        }
        if ($orderBy != ''){
            $select->order($orderBy);
        }else{
            $select->order("kb.id");
        }    
        return $select;
    }
    
    
}
