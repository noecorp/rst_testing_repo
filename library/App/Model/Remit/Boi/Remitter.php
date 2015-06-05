<?php
/*
 * BOI Remitter Model
 */
class Remit_Boi_Remitter extends Remit_Boi
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
    protected $_name = DbTable::TABLE_REMITTERS;
    
    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    //protected $_rowClass = 'App_Table_Remitters';
    
    

    /*public function getList(){
           $select = $this->select()
                   ->setIntegrityCheck(false)
                ->from("t_agents as a",array('id'))
                   ->joinLeft("t_opertaion as o", "o.id = a.operation_id",array('id','name'))
                ->where('email =?','vikram@transerv.co.in');
           //echo $select->__toString();exit;
           return  $this->fetchAll($select);

    }*/
      
    /*
     * Get Remitter details by mobile number
     */
    public function getRemitter($mobile){ 
         if(strlen($mobile) <> Mobile::$length || !is_numeric($mobile)) {
            throw new Exception("Invalid mobile number");
        }
        
        $select = $this->select()
                 ->from(DbTable::TABLE_REMITTERS)
                 ->where("mobile = '$mobile' AND status = '".STATUS_ACTIVE."'");
       
        $res = $this->fetchRow($select);
           if (!empty($res)){
               return $res;
           }
           else{
               throw new Exception("Mobile not registered"); 
           }

    }
    

     /*
     * Get Remitter details by mobile number
     */
    public function getRemitterById($id){  
        $select = $this->select()
                 ->from(DbTable::TABLE_REMITTERS)
                 ->where("id = '$id'")
                 ->where("status = '".STATUS_ACTIVE."'");
        //echo $select;exit;
        $res = $this->fetchRow($select);
           if (!empty($res)){
               return $res;
           }
           else{
               throw new Exception("Remitter with Id does not exist"); 
           }

    }
    public function addRemitter($param, $remitterId=0, $oldVals=array()){        

         $mobile = isset($param['mobile'])?$param['mobile']:'';
         $email = isset($param['email'])?$param['email']:'';
         $emailOld = isset($oldVals['email_old'])?$oldVals['email_old']:'';
        
         $mobObj = new Mobile();  
         $emailObj = new Email(); 
         $session = new Zend_Session_Namespace('App.Agent.Controller');
          
         // mobile duplicacy check
         if( ($remitterId==0) && ($mobile!='') ) {
          try {  
                 $mobCheck = $mobObj->checkRemitterMobileDuplicate($mobile);                
            } catch (Exception $e ) {
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                throw new Exception($e->getMessage());
            }   
         } 
         
         // email duplicacy check
         if( ($email!='') || ($emailOld!='' && $email!='' && strtolower($emailOld)!=strtolower($email)) ) {
          try {                
                 $emailCheck = $emailObj->checkRemitterEmailDuplicate($email);                
            } catch (Exception $e ) {
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                throw new Exception($e->getMessage());
            }   
         }       
         
         
         if($remitterId<1) {
          
                $resp = $this->_db->insert(DbTable::TABLE_REMITTERS, $param); // adding remitter to t_remitters   
                if($resp)
                    $remitterId = $this->_db->lastInsertId(DbTable::TABLE_REMITTERS, 'id');                
                
                $session->remitter_id = $remitterId;
         } else {    
                 $chResp  = $this->_db->update(DbTable::TABLE_REMITTERS, $param, 'id="'.$remitterId.'"');                 
         }    
         
            return TRUE;               
    }
    
    public function updateRemitter($param, $remitterId){
        if($remitterId>0 && !empty($param)){
            $resp  = $this->_db->update(DbTable::TABLE_REMITTERS, $param, 'id="'.$remitterId.'"');
        } else throw new Exception('Unicode not found for update!');
    }
    
    public function getRemitterInfo($remitterId = 0, $mobile=0)
    {
        
        $mobLen = strlen($mobile);
        $whereString='';
        
        if(($remitterId != 0 && is_numeric($remitterId)) || $mobLen==10) {
            
            if($remitterId!=0)
                $whereString = " id = '$remitterId'";            
            else if($mobLen==10)
                $whereString = " mobile = '$mobile'";
            
                        $select = $this->_db->select()
                        //->setIntegrityCheck(false)
                        ->from(DbTable::TABLE_REMITTERS)
                        ->where($whereString);
            
          // echo $select->__toString();exit;
           return  $this->_db->fetchRow($select);          
        } else throw new Exception('Remitter id or mobile missing!');
        
    }
      
    
    /*
     * Get Remitter beneficiaries details by mobile number
     */
    public function getRemitterbeneficiaries($id){  
        $decryptionKey = App_DI_Container::get('DbConfig')->key;
        $bankAccountNumber = new Zend_Db_Expr("AES_DECRYPT(`b`.`bank_account_number`,'".$decryptionKey."') as bank_account_number");
        $branchAddress = new Zend_Db_Expr("AES_DECRYPT(`b`.`branch_address`,'".$decryptionKey."') as branch_address");
        $mobile = new Zend_Db_Expr("AES_DECRYPT(`b`.`mobile`,'".$decryptionKey."') as mobile");
        $email = new Zend_Db_Expr("AES_DECRYPT(`b`.`email`,'".$decryptionKey."') as email");
         
        $select = $this->select()
                 ->from(DbTable::TABLE_BENEFICIARIES." as b", array('id', 'remitter_id', 'name', 'nick_name', 'ifsc_code', $bankAccountNumber, 'bank_name', 'branch_name', 'branch_city', $branchAddress, 'bank_account_type', 'address_line1', 'address_line2', $mobile, $email, 'by_agent_id', 'by_ops_id', 'date_created', 'date_modified', 'status'))
                  ->setIntegrityCheck(false)
                   ->joinLeft(DbTable::TABLE_REMITTERS." as r", "r.id = b.remitter_id and b.status = '".STATUS_ACTIVE."'",array('id as rid'))
                 ->where('r.id =?',$id);
//        echo $select;
        return $this->_paginate($select, $page, $paginate);

    } 
    
    
    /* searchRemitter() will return remitters details after searching remitter
     */
    public function searchRemitter($param, $page = 1, $paginate = NULL, $force = FALSE){ 
       
       $columnName = $param['searchCriteria'];
       $keyword = $param['keyword'];
       $whereString = "$columnName LIKE '%$keyword%'";
      
       $details = $this->select()
                ->from(DbTable::TABLE_REMITTERS,array('id','name','address','mobile','email'))
                ->setIntegrityCheck(false)
                ->where("status='".STATUS_ACTIVE."'")
                ->where($whereString)
                ->order('date_created DESC');
       return $this->_paginate($details, $page, $paginate);

    }

    
    
   

    /*
     * Get remitter registration fee for an agent on a particular date for a product
     */
    public function getRemitterRegnfee($param){
        $date = $param['date'];
        $agentId = isset($param['agent_id'])?$param['agent_id']:0;
        $checkRegFee = isset($param['check_fee'])?$param['check_fee']:true;
        
        $details = $this->select();
        $details->from(DbTable::TABLE_REMITTERS." as r",array('r.regn_fee as fee','r.mobile as mobile_number','r.service_tax', 'r.id AS rid','r.product_id','DATE(r.date_created) as date_created','r.date_created as txn_date','r.txn_code', 'r.name as remit_name'));
        $details->setIntegrityCheck(false);
        $details->joinLeft(DbTable::TABLE_PRODUCTS . " as p", "r.product_id = p.id ", array('p.ecs_product_code','p.unicode as pro_unicode'));
        $details->joinLeft(DbTable::TABLE_AGENTS . " as a", "r.by_agent_id = a.id ", array('a.id as agent_id', 'a.agent_code', 'concat(a.first_name," ",a.last_name) as name','a.mobile1 as agent_mobile','a.email as agent_email', 'a.user_type as agent_user_type'));
        $details->joinLeft(DbTable::TABLE_AGENT_DETAILS . " as ad", "a.id = ad.agent_id AND ad.status = '" . STATUS_ACTIVE . "'", array('ad.estab_city', 'ad.estab_pincode', 'ad.bank_ifsc_code as agent_ifsc_code', 'ad.bank_account_number as agent_bank_account_number'));
        $details->where("r.status = '".STATUS_ACTIVE."' OR r.status = '".STATUS_INACTIVE."' ");
        if($checkRegFee)
            $details->where("r.regn_fee > ? ", 0); 
        
        
        if($agentId >=1)
            $details->where("r.by_agent_id = ? ", $agentId); 

        $details->where("DATE(r.date_created) = ?", $date);
        return $this->fetchAll($details); 
    }
    
   
    
   
    
     /* getRemittersOnDateBasis() will return remitters who got registered successfully on date basis
     */
    public function getRemittersOnDateBasis($param){ 
      
       $retData = array();
       $retNewData = array();
       
       if(!empty($param)){        
                
         $param['check_fee'] = false;
         $retData = $this->getRemitterRegnfee($param);  
         $totalRemitRegnFee = count($retData);
         
         if($totalRemitRegnFee>=1){
            $retData =  $retData->toArray();
            $totalData = count($retData);
            
             // recreating array with adding new records for service tax 
            $k=0;
            $alterData=array();
            for($j=0;$j<$totalData;$j++){
                
                // adding transaction type field
                $alterData = $retData[$j];
                $alterData['txn_type'] = TXNTYPE_REMITTER_REGISTRATION;
                $alterData['crn'] = '';
                $alterData['amount'] = $retData[$j]['fee'];
                $alterData['txn_date'] = Util::returnDateFormatted($retData[$j]['txn_date'], "Y-m-d", "d-m-Y", "-");
                $alterData['agent_name'] = $retData[$j]['name'];
                
                // recreating array with adding new records for service tax and fee 
                $retNewData[$k] = $alterData;
                $retNewData[$k]['batch_name'] = '';
                $k++;
                $retNewData[$k] = $alterData;
                $retNewData[$k]['amount'] = $retData[$j]['service_tax'];
                $retNewData[$k]['txn_type'] = TXNTYPE_REMITTANCE_SERVICE_TAX;
                $retNewData[$k]['txn_code'] = $retData[$j]['txn_code'];
                $retNewData[$k]['batch_name'] = '';
                $k++;
                
            }
            
         }
       }
       
       return $retNewData;
    }
    
    
    
    
     /*
     *  getRemitterRegistrations function will fetch remitters details registred during a time span
     */
    public function getRemitterRegistrations($param){
        //Enable DB Slave
        $this->_enableDbSlave();
        $from = $param['from'];
        $to = $param['to'];
        
        
        $details = $this->select();
        $details->from(DbTable::TABLE_REMITTERS." as r",array('r.name','r.mobile','r.unicode', 'r.id AS rid','r.product_id',"DATE_FORMAT(DATE(r.date_created),'%m-%d-%Y') as date_created",'r.address'));
        $details->setIntegrityCheck(false);
        $details->joinLeft(DbTable::TABLE_PRODUCTS." as p", "r.product_id = p.id ",array('p.ecs_product_code','p.bank_id'));
        $details->joinLeft(DbTable::TABLE_BANK." as b", "b.id = p.bank_id ",array('b.name as bank_name'));
        $details->joinLeft(DbTable::TABLE_AGENTS." as a", "r.by_agent_id = a.id ",array('a.agent_code','concat(a.first_name," ",a.last_name) as agent_name'));
        $details->joinLeft(DbTable::TABLE_AGENT_DETAILS." as ad", "a.id = ad.agent_id AND ad.status = '".STATUS_ACTIVE."'",array('ad.estab_city','ad.estab_pincode','ad.estab_state'));
        $details->where("r.status = '".STATUS_ACTIVE."' OR r.status = '".STATUS_INACTIVE."' ");
        $details->where("DATE(r.date_created) >= '$from' AND DATE(r.date_created) <= '$to'");
        //Disable DB Slave
        $this->_disableDbSlave(); 

        return $this->fetchAll($details); 
    }
    
    
    /*  getAgentTotalRemitterRegnFeeSTax() is responsible for fetch data for agent total remitter regn fee & Service Tax amount 
     *  as params it will accept agent id and transaction date
     */    
    public function getAgentTotalRemitterRegnFeeSTax($param){

        $agentId = isset($param['agent_id'])?$param['agent_id']:'';
        $date = isset($param['date'])?$param['date']:'';
                
        if($date!=''){ 
            //Enable DB Slave
            $this->_enableDbSlave();
            $select =  $this->_db->select() ; 
            $select->from(DbTable::TABLE_REMITTERS.' as r', array('sum(r.regn_fee) as agent_total_remitter_regn_fee', 'sum(r.service_tax) as agent_total_remitter_regn_stax', 'count(r.id) as count_agent_total_remitters'));
            if($agentId>=1)
               $select->where('r.by_agent_id=?',$agentId);
            
            $select->where("r.status='".STATUS_ACTIVE."'");         
            $select->where("DATE(r.date_created) ='".$date."'"); 
            
//           echo $select.'<br><br>'; //exit;
         $row = $this->_db->fetchRow($select);
         //Disable DB Slave
        $this->_disableDbSlave();
        return $row;
       } else return array();
    }
    
    
    /*  getAgentRemitterRegnFee() is responsible for fetch data for agent total remitter regn fee & Service Tax amount 
     *  as params it will accept agent id and transaction date
     */    
    public function getAgentRemitterRegnFee($param){

        $agentId = isset($param['agent_id'])?$param['agent_id']:'';
        $productId = isset($param['product_id'])?$param['product_id']:'';
        $date = isset($param['date'])?$param['date']:'';
        
        if($date==''){
           return array();
        }
        $select =  $this->_db->select() ; 
        $select->from(DbTable::TABLE_REMITTERS.' as r', array('r.regn_fee as transaction_fee', 'r.service_tax as transaction_service_tax', 'r.txn_code as transaction_ref_no'));
        $select->where("r.status='".STATUS_ACTIVE."'");         
        $select->where("DATE(r.date_created) ='".$date."'");         

        if($agentId > 0) {
            $select->where('r.by_agent_id=?',$agentId); 
        }
        if($productId > 0){
            $select->where('r.product_id=?',$productId); 
        }
//        echo $select.'<br><br>';
        return $this->_db->fetchAll($select);
      
    }
    
    
    
    /* getRemittersForDD() will return remitters array for drop down
     */
    public function getRemittersForDD(){ 
       
        $select = $this->select()
                 ->from(DbTable::TABLE_REMITTERS, array('id', 'name as remitter_name'))
                 ->where("status = '".STATUS_ACTIVE."'");
        //echo $select; exit;
        $remitters = $this->_db->fetchAll($select);     
           
        $dataArray = array();
        //$dataArray[''] = "Select Fund Transfer Type";
        foreach ($remitters as $id => $val) {
            $dataArray[$val['id']] = $val['remitter_name'];
        }
        return $dataArray;
    }
    
    
    
     /*
     *  getRemitterRegistrationsCount function will fetch remitters registred count for a time span
     */
    public function getRemitterRegistrationsCount($param){
        $from = $param['from'];
        $to = $param['to'];
        
        $details = $this->_db->select();
        $details->from(DbTable::TABLE_REMITTERS." as r",array('count(r.id) as total_remitter_count'));
        $details->where("r.status = '".STATUS_ACTIVE."' OR r.status = '".STATUS_INACTIVE."'");
        $details->where("DATE(r.date_created) >= '$from' AND DATE(r.date_created) <= '$to'");
        //ECHO $details; EXIT;
        return $this->_db->fetchRow($details); 
    }
    
    
    /* getRemittersCount() will return the number of remitters registerd for the month
     */
    public function getRemittersCount($param){ 
        $agentId = isset($param['agentId'])?$param['agentId']:'';
        $from = $param['from'];
        $to   = $param['to'];
        $select = $this->select()
                 ->from(DbTable::TABLE_REMITTERS, array('count(id) as remitter_count','DATE(date_created) as date_created'))
                 ->where("status = '".STATUS_ACTIVE."'");
        if($agentId != ''){
            $select->where('by_agent_id=?',$agentId); 
        }
                 $select->where ("date_created between '$from' AND '$to'");
                 $select->group('date_created');
        $remitters = $this->_db->fetchAll($select);     
        
        return $remitters;
    }
    
    /* getRemittersRgnCount() will return the number of remitters registerd for the month
     */
    public function getRemittersRgnCount($param){ 
        $agentId = isset($param['agentId'])?$param['agentId']:'';
        $from = $param['from'];
        $to   = $param['to'];
        $select = $this->select()
                 ->from(DbTable::TABLE_REMITTERS, array('count(id) as remitter_count','DATE(date_created) as date_created'))
                 ->where("status = '".STATUS_ACTIVE."'");
        if($agentId != ''){
            $select->where('by_agent_id=?',$agentId); 
        }
                 $select->where ("date_created between '$from' AND '$to'");
//                 $select->group('date_created');
        $remitters = $this->_db->fetchRow($select);     
        
        return $remitters;
    }
    
    /* getBoiRemittance() will return the number of remittance for the duration
     */
    public function getBoiRemittance($param){ 
        //Enable DB Slave
       $this->_enableDbSlave();
          if(isset($param['duration']) && $param['duration'] != ''){
            $dates = Util::getDurationAllDates($param['duration']);
            }
            else{
            $dates = Util::getDurationRangeAllDates($param);  
            }
        
        $retTxnData = array();
        $objRemitRequest = new Remit_Remittancerequest();
        foreach($dates as $queryDate){
                
                $to = isset($queryDate['to'])?$queryDate['to']:'';
                $from = isset($queryDate['from'])?$queryDate['from']:'';
               
                
                $queryDateArr = explode(' ', $to);
                $queryDate = array('date'=>$queryDateArr[0],'agent_id'=> $param['agent_id']);
                
                /**** getting agent remitters registered for particular date ****/
                $remitters  = $this->getRemittersOnDateBasis($queryDate);
                if(!empty($remitters))
                    $retTxnData = array_merge($retTxnData, $remitters);


                /**** getting agent remitters's fund transfer request for particular date *****/
                $remitRequests  = $objRemitRequest->getRemitRequestOnDateBasis($queryDate);
                if(!empty($remitRequests))
                    $retTxnData = array_merge($retTxnData, $remitRequests);

                /**** getting agent remitters's refunds for particular date *****/
                $remitRefunds  = $objRemitRequest->getRemitRefundsOnDateBasis($queryDate);
                if(!empty($remitRefunds))
                    $retTxnData = array_merge($retTxnData, $remitRefunds);

          } // for each loop  
          //Disable DB Slave
         $this->_disableDbSlave();  
          return $retTxnData ;
    }
    
    public function updateAmlboiRemitters(){
        $details = $this->select()
                       ->from($this->_name,array('id', 'name'))
                       ->where ("status ='".STATUS_ACTIVE."'")
                       ->where ("aml_status ='".STATUS_AML."'")
                       ->order('date_created ASC');

                       $results = $this->fetchAll($details);
                       $reportsData = array();
                       foreach($results AS $data){
                           $select = $this->_db->select()
                                       ->from(DbTable::TABLE_AML_MASTER." AS a" , array('*'))
                                       ->where('a.full_name LIKE ?',$data['name']) 
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
    
    public function getRemitterRegnfeeAll($param){
        $to = $param['to'];
        $from = $param['from'];
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : ''; 
        
        
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
                DbTable::TABLE_REMITTERS . " as r", 
                    array(
                        'r.regn_fee as fee', 'r.service_tax' ,'DATE_FORMAT(r.date_created,"%d-%m-%Y") as txn_date','DATE(r.date_created) as date_created','r.status','r.txn_code'
                ));
        $select->joinLeft(
                DbTable::TABLE_AGENTS . " as a", "r.by_agent_id = a.id ", 
                    array(
                        'a.agent_code', 'concat(a.first_name," ",a.last_name) as name', 'a.user_type as agent_user_type'
                ));
        $select->joinLeft(
                DbTable::TABLE_AGENT_DETAILS . " as ad", "a.id = ad.agent_id AND ad.status = '" . STATUS_ACTIVE . "'", 
                    array(
                        'ad.estab_city', 'ad.estab_pincode'
                ));
         $select->joinLeft(
                DbTable::TABLE_REMITTANCE_REFUND." as ref", "ref.remittance_request_id = r.id" , 
                    array(
                        'ref.txn_code as refund_txn_code'
                ));
        $select->joinLeft(DbTable::TABLE_BIND_OBJECT_RELATION." as orel", "a.id = orel.to_object_id" ,array( ));
        $select->joinLeft(
                DbTable::TABLE_AGENTS." as dis", "dis.id = orel.from_object_id" , 
                    array(
                        'dis.agent_code AS dist_code',' concat(dis.first_name," ",dis.last_name) as dist_name',
                ));
        $select->joinLeft(DbTable::TABLE_BIND_OBJECT_RELATION." as orel_sdis", "dis.id = orel_sdis.to_object_id" ,array());
        $select->joinLeft(
                DbTable::TABLE_AGENTS." as sdis", "sdis.id = orel_sdis.from_object_id" , 
                    array(
                        'sdis.agent_code AS sup_dist_code' ,'concat(sdis.first_name," ",sdis.last_name) as sup_dist_name'
                )); 
        $select->where("r.status = '".STATUS_ACTIVE."' OR r.status = '".STATUS_INACTIVE."' ");
        $select->where("r.date_created >= ?", $from);
        $select->where("r.date_created <= ?", $to); 
        if ($agentId != '') {
            $select->where('r.by_agent_id=?', $agentId);
        }
        
        //echo $select; exit();
        $row = $this->fetchAll($select); 
        return $row;
    }
    
    /*
     * getRemitterRegistrationfee() remitter registration fee for an agent on a particular date for a product. This is called from Operation portal. Please do not modify this function
     */
    public function getRemitterRegistrationfee($param){
        $to = $param['to'];
        $from = $param['from'];
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : ''; 
        
        
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
                DbTable::TABLE_REMITTERS . " as r", 
                    array(
                        'r.regn_fee as fee_amount', 'r.service_tax as service_tax_amount' ,'DATE_FORMAT(r.date_created,"%d-%m-%Y") as txn_date','DATE(r.date_created) as date_created','r.status as txn_status','r.txn_code', new Zend_Db_Expr("'".TXNTYPE_REMITTER_REGISTRATION."' as transaction_type_name"), new Zend_Db_Expr("'0.00' as transaction_amount"), new Zend_Db_Expr("0 as reversal_fee"), new Zend_Db_Expr("0 as reversal_service_tax"), new Zend_Db_Expr("'' as refund_txn_code"), new Zend_Db_Expr("'' as utr")
                ));
        $select->joinLeft(
                DbTable::TABLE_AGENTS . " as a", "r.by_agent_id = a.id ", 
                    array(
                        'a.agent_code', 'concat(a.first_name," ",a.last_name) as name', 'a.user_type as agent_user_type'
                ));
        $select->joinLeft(
                DbTable::TABLE_AGENT_DETAILS . " as ad", "a.id = ad.agent_id AND ad.status = '" . STATUS_ACTIVE . "'", 
                    array(
                        'ad.estab_city', 'ad.estab_pincode'
                ));
         $select->joinLeft(
                DbTable::TABLE_REMITTANCE_REFUND." as ref", "ref.remittance_request_id = r.id" , 
                    array(
                        'ref.txn_code as refund_txn_code'
                ));
        $select->joinLeft(DbTable::TABLE_BIND_OBJECT_RELATION." as orel", "a.id = orel.to_object_id" ,array( ));
        $select->joinLeft(
                DbTable::TABLE_AGENTS." as dis", "dis.id = orel.from_object_id" , 
                    array(
                        'dis.agent_code AS dist_code',' concat(dis.first_name," ",dis.last_name) as dist_name',
                ));
        $select->joinLeft(DbTable::TABLE_BIND_OBJECT_RELATION." as orel_sdis", "dis.id = orel_sdis.to_object_id" ,array());
        $select->joinLeft(
                DbTable::TABLE_AGENTS." as sdis", "sdis.id = orel_sdis.from_object_id" , 
                    array(
                        'sdis.agent_code AS sup_dist_code' ,'concat(sdis.first_name," ",sdis.last_name) as sup_dist_name'
                )); 
        $select->where("r.status = '".STATUS_ACTIVE."' OR r.status = '".STATUS_INACTIVE."' ");
        $select->where("r.date_created >= ?", $from);
        $select->where("r.date_created <= ?", $to); 
        if ($agentId != '') {
            $select->where('r.by_agent_id=?', $agentId);
        }
        
        //echo $select; exit();
        return $this->fetchAll($select); 
    }
}