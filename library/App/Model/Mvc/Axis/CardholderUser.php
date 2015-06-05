<?php

class Mvc_Axis_CardholderUser extends Mvc_Axis
{
    /**
     * Column for the primary key
     *
     * @var string
     * @access protected
     */
    protected $_primary = 'id';
    
    protected $_msg = '';
    
    /**
     * Holds the table's name
     *
     * @var string
     * @access protected
     */
    protected $_name = DbTable::TABLE_CARDHOLDERS;
    
    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    protected $_rowClass = 'App_Table_CardholderUser';
    
    
    protected $_cardholderId;
    /*public function getList(){
           $select = $this->select()
                   ->setIntegrityCheck(false)
                ->from("t_agents as a",array('id'))
                   ->joinLeft("t_opertaion as o", "o.id = a.operation_id",array('id','name'))
                ->where('email =?','vikram@transerv.co.in');
           //echo $select->__toString();exit;
           return  $this->fetchAll($select);

    }*/
          
    
    public function addCardHolder($param){        
        
         $chData = $param['chData'];   
         $chDetails = $param['chDetails']; 
         $oldVals = $param['oldVals']; 
         $mobObj = new Mobile();  
         $emailObj = new Email();
         $validObj = new Validator();
         $chIdUpd = isset($oldVals['chId'])?$oldVals['chId']:0;
         $mobileNumberOld = isset($oldVals['mobile_number_old'])?$oldVals['mobile_number_old']:'';
         $emailOld = isset($oldVals['email_old'])?$oldVals['email_old']:'';
         $agentId = isset($chData['reg_agent_id'])?$chData['reg_agent_id']:'';
         $opsId = isset($chData['reg_ops_id'])?$chData['reg_ops_id']:'';
         $session = new Zend_Session_Namespace('App.Agent.Controller');
         $ip = $this->formatIpAddress(Util::getIP());
         $chDetails['ip'] = $ip;
         
         // arn duplicacy check
         if( ($chIdUpd==0) || ($arnOld!='' && $arnOld!=$chData['arn']) ) {
          try {                
                 $arnCheck = $validObj->checkARNDuplicate(array('arn'=>$chDetails['arn'], 'tablename'=>'cardholder_details'));                
            } catch (Exception $e ) {
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                return $e->getMessage();
            }   
         }
         
            
         // mobile duplicacy check
         if( ($chIdUpd==0) || ($mobileNumberOld!='' && $mobileNumberOld!=$chData['mobile_number']) ) {
          try {  
             
                 $mobCheck = $mobObj->checkDuplicate($chData['mobile_number'],'cardholder');                
            } catch (Exception $e ) {
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                return $e->getMessage();
            }   
         } 
         
         // email duplicacy check
         if( ($chIdUpd==0) || ($emailOld!='' && $emailOld!=$chData['email']) ) {
          try {                
                 $emailCheck = $emailObj->checkDuplicate($chData['email'],'cardholder');                
            } catch (Exception $e ) {
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                return $e->getMessage();
            }   
         }       
         
          
         if($chIdUpd<1) {
                $resp = $this->_db->insert(DbTable::TABLE_CARDHOLDERS, $chData); // adding card holder to t_cardholders   
                if($resp)
                    $chId = $this->_db->lastInsertId(DbTable::TABLE_CARDHOLDERS, 'id');                
                
                $chDetails['cardholder_id']=$chId;
                
                // adding cardholder details to t_cardholder_details
                $this->_db->insert(DbTable::TABLE_CARDHOLDER_DETAILS, $chDetails); 
                
                // adding product to t_cardholders_product              
                $chProd = array('cardholder_id'=>$chId,  'product_id'=>$param['product_id'],
                                'by_agent_id'=>$agentId, 'by_ops_id'=>$opsId,
                                'datetime'=>date('Y-m-d H:i:s'), 'status'=>STATUS_ACTIVE                                
                               );
                
                $this->_db->insert(DbTable::TABLE_CARDHOLDERS_PRODUCT, $chProd);                 
                         
                $session->cardholder_id = $chId;
                $session->cardholder_product_id = $param['product_id'];
         } else {    
            
                 $chResp  = $this->_db->update(DbTable::TABLE_CARDHOLDERS, $chData, 'id="'.$chIdUpd.'"');                 
                 $oprId = isset($chDetails['by_ops_id'])?$chDetails['by_ops_id']:'';
                 
                 if($oprId>0){
                        $chResp  = $this->_db->update(DbTable::TABLE_CARDHOLDER_DETAILS, array('status'=>STATUS_INACTIVE), 'cardholder_id="'.$chIdUpd.'"');
                        $chResp  = $this->_db->insert(DbTable::TABLE_CARDHOLDER_DETAILS, $chDetails);
                 } else{                 
                    $chResp  = $this->_db->update(DbTable::TABLE_CARDHOLDER_DETAILS, $chDetails, 'cardholder_id="'.$chIdUpd.'"');
                 }
                 
                 $chProd = array('cardholder_id'=>$chIdUpd,  'product_id'=>$param['product_id'],
                                 'by_agent_id'=>$agentId, 'by_ops_id'=>$opsId,
                                 'datetime'=>date('Y-m-d H:i:s'), 'status'=>STATUS_ACTIVE                                
                                );
                
                // updating cardholder product in t_cardholders_product
                 if($oprId>0){
                     $chProd['by_ops_id'] = $oprId;
                    $this->_db->update(DbTable::TABLE_CARDHOLDERS_PRODUCT, array('status'=>STATUS_INACTIVE), 'cardholder_id='.$chIdUpd);                
                    $this->_db->insert(DbTable::TABLE_CARDHOLDERS_PRODUCT, $chProd);                
                 }else 
                    $this->_db->update(DbTable::TABLE_CARDHOLDERS_PRODUCT, $chProd, 'cardholder_id='.$chIdUpd);                
                 
                $session->cardholder_product_id = $param['product_id'];
         }    
         
            return 'success';               
    }          
      
    
    public function updateCardholder($param) { 
       $opsId = isset($param['by_ops_id'])?$param['by_ops_id']:'';
       $ip = $this->formatIpAddress(Util::getIP());
       $param['ip'] = $ip;
         
       if(isset($param['crn']) && $param['crn']!= ''){
	    $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
	    $param['crn'] = new Zend_Db_Expr("AES_ENCRYPT('".$param['crn']."','".$encryptionKey."')"); 
       }   
        if($opsId>0){
            $resp = $this->_db->update(DbTable::TABLE_CARDHOLDER_DETAILS, array('status'=>STATUS_INACTIVE), 'cardholder_id="'.$param['cardholder_id'].'"'); 
            $resp = $this->_db->insert(DbTable::TABLE_CARDHOLDER_DETAILS, $param); 
            
        }
        else{        
            //$paramCHD = array('status'=>$param['status']);
            if(isset($param['enroll_status'])&& $param['enroll_status']!=''){
                $paramCH = array('enroll_status'=>$param['enroll_status']);
                $resp = $this->_db->update(DbTable::TABLE_CARDHOLDERS,$paramCH, 'id="'.$param['cardholder_id'].'"'); 
                unset($param['enroll_status']);
            }
            
            $resp = $this->_db->update(DbTable::TABLE_CARDHOLDER_DETAILS,$param, 'cardholder_id="'.$param['cardholder_id'].'"'); 
           
            $session = new Zend_Session_Namespace('App.Agent.Controller');         
            $session->cardholder_step2 = 1;
        }
        return true;
    }
    
     public function updateOffers($param) {   
       
       $session = new Zend_Session_Namespace('App.Agent.Controller');         
       $alreadyAdded = isset($session->cardholder_offers)?$session->cardholder_offers:'0'; 
     
       if($alreadyAdded==1)
            $resp = $this->_db->update(DbTable::TABLE_CARDHOLDER_OFFERS, $param, 'cardholder_id="'.$param['cardholder_id'].'"'); 
       else 
       {
           $resp = $this->_db->insert(DbTable::TABLE_CARDHOLDER_OFFERS, $param);    
           $session->cardholder_offers=1;
       }      
       return true;
    }
    
    
    public function updateOffersOps($newOffers) {   
       
       $param = $newOffers['param'];
       $shmartRewardsOld = $newOffers['shmart_rewards_old'];       
       $shmartRewards = $newOffers['shmart_rewards'];       
       
       if($shmartRewards=='yes' && $shmartRewardsOld=='yes'){
           $resp = $this->_db->update(DbTable::TABLE_CARDHOLDER_OFFERS, array('status'=>STATUS_INACTIVE), 'cardholder_id="'.$param['cardholder_id'].'"'); 
           $resp = $this->_db->insert(DbTable::TABLE_CARDHOLDER_OFFERS, $param);    
       }
       else if($shmartRewards=='yes' && $shmartRewardsOld=='no'){ 
           $resp = $this->_db->insert(DbTable::TABLE_CARDHOLDER_OFFERS, $param);    
       }
       else if($shmartRewards=='no' && $shmartRewardsOld='yes'){ 
           $resp = $this->_db->update(DbTable::TABLE_CARDHOLDER_OFFERS, array('status'=>STATUS_INACTIVE), 'cardholder_id="'.$param['cardholder_id'].'"'); 
       }

       $session->cardholder_offers=1;         
       return true;
    }
    
    
    public function getCardHolderInfo($cardholderId = 0, $status='', $mobile=0)
    {
        
        $mobLen = strlen($mobile);
        if(($cardholderId != 0 && is_numeric($cardholderId)) || $mobLen==10) {
                    $decryptionKey = App_DI_Container::get('DbConfig')->crnkey; 
                    $crn = new Zend_Db_Expr("AES_DECRYPT(`ch`.`crn`,'".$decryptionKey."') as crn");
                   
                    if($status!=''){
                       // echo $status.'==';
                         $select = $this->_db->select()
                        //->setIntegrityCheck(false)
                        ->from(DbTable::TABLE_CARDHOLDERS . " as ch", array('ch.id','ch.reg_agent_id','ch.reg_ops_id','ch.approval_ops_id','ch.reg_datetime','ch.approval_datetime',$crn,'ch.email','ch.title','ch.first_name','ch.middle_name','ch.last_name','ch.mobile_country_code','ch.mobile_number','ch.email_verification_id','ch.enroll_status','ch.status'))
                        ->joinLeft(DbTable::TABLE_CARDHOLDER_DETAILS." as chd", "ch.id = chd.cardholder_id AND chd.status = '".STATUS_ACTIVE."'", array('chd.cardholder_id','chd.date_of_birth', 'state', 'city', 'address_line1', 'address_line2', 'country', 'gender', 'mother_maiden_name', 'pincode', 'arn','customer_mvc_type','device_id','educational_qualifications','shmart_rewards','family_members','already_bank_account','vehicle_type','alternate_contact_number','products_acknowledgement','rewards_acknowledgement'))                    
                        //->joinLeft("t_cardholders_product as chp", "ch.id = chp.cardholder_id", array('product_id'))                    
                        ->where('ch.id =?',$cardholderId)                    
                        ->where('chd.status =?',STATUS_ACTIVE)
                        ->where('chd.cardholder_id =?',$cardholderId)
                        ->order('chd.status asc');                   
                        //->limit('1') ; 
                    }else if($mobLen<10) {
                        $select = $this->_db->select()
                        //->setIntegrityCheck(false)
                        ->from(DbTable::TABLE_CARDHOLDERS . " as ch", array('ch.id','ch.reg_agent_id','ch.reg_ops_id','ch.approval_ops_id','ch.reg_datetime','ch.approval_datetime',$crn,'ch.email','ch.title','ch.first_name','ch.middle_name','ch.last_name','ch.mobile_country_code','ch.mobile_number','ch.email_verification_id','ch.enroll_status','ch.status'))
                        ->joinLeft(DbTable::TABLE_CARDHOLDER_DETAILS." as chd", "ch.id = chd.cardholder_id AND chd.status = '".STATUS_ACTIVE."'", array('chd.cardholder_id','chd.date_of_birth', 'state', 'city', 'address_line1', 'address_line2', 'country', 'gender', 'mother_maiden_name', 'pincode', 'arn','customer_mvc_type','device_id','educational_qualifications','shmart_rewards','family_members','already_bank_account','vehicle_type','alternate_contact_number','products_acknowledgement','rewards_acknowledgement', 'status', 'date_created'))                    
                        //->joinLeft("t_cardholders_product as chp", "ch.id = chp.cardholder_id", array('product_id'))                    
                        ->where('ch.id =?',$cardholderId)                    
                        ->order('chd.status asc');                    
                        //->limit('1') ;                   
                    } else { // on mobile basis
                        $select = $this->_db->select()
                        //->setIntegrityCheck(false)
                        ->from(DbTable::TABLE_CARDHOLDERS . " as ch", array('ch.id','ch.reg_agent_id','ch.reg_ops_id','concat(ch.first_name," ",ch.last_name) as name', 'ch.approval_ops_id','ch.reg_datetime','ch.approval_datetime',$crn,'ch.email','ch.title','ch.first_name','ch.middle_name','ch.last_name','ch.mobile_country_code','ch.mobile_number','ch.email_verification_id','ch.enroll_status','ch.status'))
                        ->joinLeft(DbTable::TABLE_CARDHOLDER_DETAILS." as chd", "ch.id = chd.cardholder_id AND chd.status = '".STATUS_ACTIVE."'", array('chd.cardholder_id','chd.date_of_birth', 'state', 'city', 'address_line1', 'address_line2', 'country', 'gender', 'mother_maiden_name', 'pincode', 'arn','customer_mvc_type','device_id','educational_qualifications','shmart_rewards','family_members','already_bank_account','vehicle_type','alternate_contact_number','products_acknowledgement','rewards_acknowledgement', 'status', 'res_type'))                    
                        //->from("t_cardholders as ch", array('ch.id','ch.email','concat(ch.first_name," ",ch.last_name) as name', 'mobile_number', 'ch.crn'))
                        ->where('ch.mobile_number =?',$mobile);                                            
                       // ->limit('1') ;                   
                        //echo $select->__toString();exit;
                    }
                        
                    
            
           //echo $select->__toString();exit;
           return  $this->_db->fetchRow($select);            
        }
        return false;
    }
    
    public function getCardHolderInfoApproved($cardholderId = 0, $status='', $mobile=0)
    {
        
        $mobLen = strlen($mobile);
        if(($cardholderId != 0 && is_numeric($cardholderId)) || $mobLen==10) {
                    $decryptionKey = App_DI_Container::get('DbConfig')->crnkey; 
                    $crn = new Zend_Db_Expr("AES_DECRYPT(`ch`.`crn`,'".$decryptionKey."') as crn");
                   
                    if($status!=''){
                       // echo $status.'==';
                         $select = $this->select()
                        ->setIntegrityCheck(false)
                        ->from(DbTable::TABLE_CARDHOLDERS . " as ch", array('ch.id','ch.reg_agent_id','ch.reg_ops_id','ch.approval_ops_id','ch.reg_datetime','ch.approval_datetime',$crn,'ch.email','ch.title','ch.first_name','ch.middle_name','ch.last_name','ch.mobile_country_code','ch.mobile_number','ch.email_verification_id','ch.enroll_status','ch.status'))
                        ->joinLeft(DbTable::TABLE_CARDHOLDER_DETAILS." as chd", "ch.id = chd.cardholder_id AND chd.status = '".STATUS_ACTIVE."'", array('chd.cardholder_id','chd.date_of_birth', 'state', 'city', 'address_line1', 'address_line2', 'country', 'gender', 'mother_maiden_name', 'pincode', 'arn','customer_mvc_type','device_id','educational_qualifications','shmart_rewards','family_members','already_bank_account','vehicle_type','alternate_contact_number'))                    
                        //->joinLeft("t_cardholders_product as chp", "ch.id = chp.cardholder_id", array('product_id'))                    
                        ->where('ch.id =?',$cardholderId)                    
                        ->where('chd.status =?',STATUS_ACTIVE)
                        ->where('chd.cardholder_id =?',$cardholderId)
                      //  ->where('ch.enroll_status =?', STATUS_APPROVED)
                        ->order('chd.status asc');                   
                        //->limit('1') ; 
                    }else if($mobLen<10) {
                        $select = $this->select()
                        ->setIntegrityCheck(false)
                        ->from(DbTable::TABLE_CARDHOLDERS . " as ch", array('ch.id','ch.reg_agent_id','ch.reg_ops_id','ch.approval_ops_id','ch.reg_datetime','ch.approval_datetime',$crn,'ch.email','ch.title','ch.first_name','ch.middle_name','ch.last_name','ch.mobile_country_code','ch.mobile_number','ch.email_verification_id','ch.enroll_status','ch.status'))
                        ->joinLeft(DbTable::TABLE_CARDHOLDER_DETAILS." as chd", "ch.id = chd.cardholder_id AND chd.status = '".STATUS_ACTIVE."'", array('chd.cardholder_id','chd.date_of_birth', 'state', 'city', 'address_line1', 'address_line2', 'country', 'gender', 'mother_maiden_name', 'pincode', 'arn','customer_mvc_type','device_id','educational_qualifications','shmart_rewards','family_members','already_bank_account','vehicle_type','alternate_contact_number','products_acknowledgement','rewards_acknowledgement', 'status'))                    
                        //->joinLeft("t_cardholders_product as chp", "ch.id = chp.cardholder_id", array('product_id'))                    
                        ->where('ch.id =?',$cardholderId) 
                     //   ->where('ch.enroll_status =?', STATUS_APPROVED)
                        ->order('chd.status asc');                    
                        //->limit('1') ;                   
                    } else { // on mobile basis
                        $select = $this->select()
                        ->setIntegrityCheck(false)
                        ->from(DbTable::TABLE_CARDHOLDERS . " as ch", array('ch.id','ch.reg_agent_id','ch.reg_ops_id','concat(ch.first_name," ",ch.last_name) as name', 'ch.approval_ops_id','ch.reg_datetime','ch.approval_datetime',$crn,'ch.email','ch.title','ch.first_name','ch.middle_name','ch.last_name','ch.mobile_country_code','ch.mobile_number','ch.email_verification_id','ch.enroll_status','ch.status'))
                        ->joinLeft(DbTable::TABLE_CARDHOLDER_DETAILS." as chd", "ch.id = chd.cardholder_id AND chd.status = '".STATUS_ACTIVE."'", array('chd.cardholder_id','chd.date_of_birth', 'state', 'city', 'address_line1', 'address_line2', 'country', 'gender', 'mother_maiden_name', 'pincode', 'arn','customer_mvc_type','device_id','educational_qualifications','shmart_rewards','family_members','already_bank_account','vehicle_type','alternate_contact_number','products_acknowledgement','rewards_acknowledgement', 'status', 'res_type'))                    
                        //->from("t_cardholders as ch", array('ch.id','ch.email','concat(ch.first_name," ",ch.last_name) as name', 'mobile_number', 'ch.crn'))
                        ->where('ch.mobile_number =?',$mobile)                                            
                        ->where('ch.enroll_status =?', STATUS_APPROVED);                                            
                       // ->limit('1') ;                   
                        //echo $select->__toString();exit;
                    }
                         
           return  $this->fetchRow($select);            
        }
        return false;
    }
    
    public function getCardHolderProducts($cardholderId = 0)
      {
        if($cardholderId != 0 && is_numeric($cardholderId) ) {
                   
            $select = $this->_db->select()
                    //->setIntegrityCheck(false)
                    ->from(DbTable::TABLE_CARDHOLDERS_PRODUCT)                                                           
                    ->where('cardholder_id =?',$cardholderId)
                    ->where('status =?',STATUS_ACTIVE);
            
            
           //echo $select->__toString();exit;
           return  $this->_db->fetchRow($select);            
        }
        return false;
      }
      
      
       public function getCHDropDownProducts($cardholderId = 0)
      {
        if($cardholderId != 0 && is_numeric($cardholderId) ) {
                   
            $select = $this->_db->select()
                    //->setIntegrityCheck(false)
                    ->from(DbTable::TABLE_CARDHOLDERS_PRODUCT." as cp")   
                    ->joinLeft(DbTable::TABLE_PRODUCTS." as p", "cp.product_id = p.id", array('p.name'))                    
                    ->where('cp.cardholder_id =?',$cardholderId)
                    ->where('cp.status =?',STATUS_ACTIVE);
            
            
             //echo $select->__toString();exit;
            $chProds = $this->_db->fetchAll($select);   
           
            $dataArray = array();
            //$dataArray[''] = "Select Fund Transfer Type";
            foreach ($chProds as $id => $val) {
                $dataArray[$val['product_id']] = $val['name'];
            }
            
            return $dataArray;
        }
        return false;
      }
      
      public function getCardHolderOffers($cardholderId = 0)
      {
        if($cardholderId != 0 && is_numeric($cardholderId) ) {
                   
            $select = $this->_db->select()
                    //->setIntegrityCheck(false)
                    ->from(DbTable::TABLE_CARDHOLDER_OFFERS." as cho")                                                           
                    ->where('cardholder_id =?',$cardholderId)
                    ->where('status =?',STATUS_ACTIVE);
            
            
           //echo $select->__toString();exit;
           return  $this->_db->fetchRow($select);            
        }
        return false;
      }
    
    
     
    
    public function getCardholderList($param,$agent_id, $page = 1, $paginate = NULL, $force = FALSE){
       $columnName = $param['searchCriteria'];
       $keyword = $param['keyword'];
      
       if($columnName == 'city'){
            $whereString = "cd.$columnName LIKE '%$keyword%'";
       }
       else{
            $whereString = "c.$columnName LIKE '%$keyword%'";
       }

        if ($agent_id == 0){
             $select = $this->select()
                     ->from(DbTable::TABLE_CARDHOLDERS . " as c",array('c.*','concat(c.first_name," ",c.last_name) as name','concat(c.mobile_country_code,c.mobile_number) as mobile'))
                     
                     ->setIntegrityCheck(false)
                     ->joinLeft(DbTable::TABLE_CARDHOLDER_DETAILS." as cd","c.id=cd.cardholder_id AND cd.status = '".STATUS_ACTIVE."'",array('DATE_FORMAT(cd.date_of_birth, "%d-%m-%Y") as date_of_birth','cd.gender','cd.city','cd.state','cd.pincode'))
                     ->where("c.enroll_status = '".STATUS_APPROVED."'")
                     ->where($whereString)
                     ->order('cd.date_created DESC');
        
        }
        else {
        $select =   $this->select()
                    ->setIntegrityCheck(false)
                    ->from(DbTable::TABLE_CARDHOLDERS)
                    ->where('ag_id=?',$agent_id)
                    ->where("status = '".STATUS_ACTIVE."'")
                    ->order('date_created DESC');
        }
        
       // echo $select->__toString();exit;        
    
        return $this->_paginate($select, $page, $paginate); //$this->_db->fetchAll($select);       
    }
    
    public function deactivate($id,$dataArr){
        
        $data = array('status' => STATUS_BLOCKED);
        $update = $this->update($data,"id=".$id);
        
        $insertIntoLog = $this->_db->insert(DbTable::TABLE_CHANGE_STATUS_LOG,$dataArr);
      
        
                if($update && $insertIntoLog >=1)
                    return 'updated';
                else
                    return 'not_updated';
    }
   
     public function changeStatus($id,$status){
         
        //$ip = $this->formatIpAddress(Util::getIP());
        $data = array('status' => $status);
        $update = $this->update($data,"id=".$id);
        
        if($update)
                    return 'updated';
                else
                    return 'not_updated';
    }
     public function activate($id,$dataArr){
        
        $data = array('status' => STATUS_UNBLOCKED);
        $update = $this->update($data,"id=".$id);
        
        $insertIntoLog = $this->_db->insert(DbTable::TABLE_CHANGE_STATUS_LOG,$dataArr);
      
        
                if($update && $insertIntoLog >=1)
                    return 'updated';
                else
                    return 'not_updated';
    }
  public function finddetailsById($chid){
      $decryptionKey = App_DI_Container::get('DbConfig')->crnkey; 
      $crn = new Zend_Db_Expr("AES_DECRYPT(`c`.`crn`,'".$decryptionKey."') as crn");
      $select = $this->_db->select()    
                ->from(DbTable::TABLE_CARDHOLDERS . " as c",array($crn,'c.id as cid','c.id','c.status as Cstatus','concat(c.first_name," ",c.last_name) as name','concat(c.mobile_country_code,c.mobile_number) as mobile', "if(c.approval_ops_id > 0 ,c.approval_ops_id,'Not Approved') as approval_ops_id", "if(c.approval_ops_id > 0 ,approval_datetime,'N.A.') as approval_datetime", 'c.email_verification_id', 'c.email_verification_status', 'c.ecs_ref_no'))
                //->setIntegrityCheck(false)
                ->joinLeft(DbTable::TABLE_CARDHOLDER_DETAILS." as cd", "cd.cardholder_id=c.id AND cd.status = '".STATUS_ACTIVE."'")
                ->joinLeft(DbTable::TABLE_OPERATION_USERS." as ou", "c.approval_ops_id=ou.id");
               $select->where('c.id = ?', $chid);
               
        return $this->_db->fetchRow($select);
  }
    
     public function findById($chId,$force = false){
        $cardholder = parent::findById($chId);
        return $cardholder;
    } 
     public function detailsById($chId){
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey; 
        $crn = new Zend_Db_Expr("AES_DECRYPT(`c`.`crn`,'".$decryptionKey."') as crn");
        $select = $this->select()    
                ->from(DbTable::TABLE_CARDHOLDERS . " as c",array($crn,'concat(c.first_name," ",c.last_name) as name'));
                $select->where('c.id = ?', $chId);
        
        return $this->fetchRow($select);
    } 
 
    
    public function updateEmailVerification($param){
        $email = new Email();    
       
        $cardholderId = $param['cardholder_id'];
        try{
            //echo 'im in updateEmailVerification function of cardholderuser';
           // App_Logger::log('im in updateEmailVerification function of cardholderuser');
            $id = $email->updateEmailVerification($param); // updating email verification
            //App_Logger::log('after updateEmailVerification function of cardholderuser');
            //echo 'updateEmailVerification function of cardholderuser is fired';
            // updating email verification id to t_cardholders
            //$chData['id'] = $cardholderId;
            $chData['email_verification_id'] = $id;
            $chData['email_verification_status'] = STATUS_PENDING;
            
            $this->update($chData,"id=".$cardholderId);
            //$this->save($ch);
           // App_Logger::log('email verification details saved');
        }catch (Exception $e ) {
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                return $e->getMessage();
        }  
        
    }
    
    
    public function updateCardholderDetail($param, $cardholderId, $chDetails =array()){
      
        try{

            $this->update($param,"id=".$cardholderId);
            
            if(!empty($chDetails)){
                $chDetails['cardholder_id'] = $cardholderId;
                $resp = $this->updateCardholder($chDetails);
            }
            
        }catch (Exception $e ) {
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                return $e->getMessage();
        }  
        
    }

    
    /*  getApprovedClosedCHCount function will fetch the approved/closed cardholders count
     *  it will expect datefrom and dateto in param array
     */
    public function getApprovedClosedCHCount($param){
        $dateFrom = isset($param['dateFrom'])?$param['dateFrom']:'';
        $dateTo   = isset($param['dateTo'])?$param['dateTo']:'';
        $whereEnrollStatus = "enroll_status='".STATUS_APPROVED."' OR enroll_status='".STATUS_CLOSED."'";
        
        if($dateFrom!='' && $dateTo!=''){
            $select  = $this->_db->select()        
            ->from(DbTable::TABLE_CARDHOLDERS,array('count(*) as cardholders_count'))
            ->where("reg_datetime>=?", $dateFrom)
            ->where("reg_datetime<=?", $dateTo)
            ->where($whereEnrollStatus);
            //echo $select; exit;
            return $this->_db->fetchRow($select);
        } else return '';
    }
    
    
    /*  getCardholders function will fetch the cardholders details from t_cardholders table on enroll status basis
     *  it will expect enroll status in param
     */
    public function getCardholders($param){
            $enrollStatus = isset($param['enrollStatus'])?$param['enrollStatus']:'';
            
            $select  = $this->_db->select()        
            ->from(DbTable::TABLE_CARDHOLDERS,array('id'));
            
            if($enrollStatus!=''){
                $select->where("enroll_status=?", $enrollStatus);
            }
            //echo $select; exit;
            return $this->_db->fetchAll($select);
    }
 
    
     /*  removeIncompleteCardholders function will fetch the cardholders with 'incomplete' status and will remove 
      *  these cardholders with thier concerned details from db
      */
    public function removeIncompleteCardholders(){
            $chParams = array('enrollStatus'=>STATUS_INCOMPLETE);
            
            // getting cardholders with incomplete status 
            $incompleteCH =  $this->getCardholders($chParams);
            $totalCH = count($incompleteCH);
            
            // deleting cardholder from cardholder's tables
            for($i=0;$i<$totalCH;$i++){
                $cardholderId = $incompleteCH[$i]['id'];
                $this->_db->delete(DbTable::TABLE_CARDHOLDERS,"id=$cardholderId");
                $this->_db->delete(DbTable::TABLE_CARDHOLDER_DETAILS,"cardholder_id=$cardholderId");
                $this->_db->delete(DbTable::TABLE_CARDHOLDER_PR,"cardholder_id=$cardholderId");
                $this->_db->delete(DbTable::TABLE_CARDHOLDERS_PRODUCT,"cardholder_id=$cardholderId");
            }
            
      return $totalCH;            
    }
    public static function getType(){
       return array(
           ''=>'Select',
           'mvcc' => 'MVC Client Mobile App.',
           //'mvci' => 'MVC IVRS',
       );
    }
    
    
    /* addCardholderMVCDetails function will add cardholder details in t_cardholders_mvc if cardholder successfully paid and registred.
     * it will accept the certain params in $param array argument, e.g.. cardholder id, mvc type, mvc enroll status etc....
     */
    public function addCardholderMVCDetails($param){
      
        if($param['cardholder_id']<1 || $param['mvc_type']=='' || $param['mvc_enroll_status']==''){
            throw new Exception('Insufficient data found for adding cardholder MVC details');
        }
       
       $resp = $this->_db->insert(DbTable::TABLE_CARDHOLDERS_MVC, $param);    
       
       return true;    
    }
    
    
    
    
    /* getCardholderMVCInfo function will return the cardholder MVC details from t_cardholders_mvc table.
     * it will accept the carholder id in params
     */
    public function getCardholderMVCInfo($cardholderId){
       if($cardholderId>0){
            $select = $this->_db->select()
                     ->from(DbTable::TABLE_CARDHOLDERS_MVC." as cmvc" ,array('cmvc.cardholder_id', 'cmvc.mvc_type as customer_mvc_type'))
                     //->setIntegrityCheck(false) 
                     //->joinleft("t_cardholders as ch","cmvc.cardholder_id=ch.id", array('ch.first_name', 'ch.last_name', 'ch.crn'))
                     //->joinleft("t_cardholder_details as chd","cmvc.cardholder_id=chd.cardholder_id and chd.status='".STATUS_ACTIVE."'", array('chd.arn', 'chd.mobile_number', 'chd.mobile_country_code'))
                     ->where("cmvc.cardholder_id='".$cardholderId."'");
            //echo $select.'----'; exit;
            return $this->_db->fetchRow($select) ;                 
       }
       return array();
    }
    
    
    
    /* registerCardholder function will register cardholder with MVC, it will attempt registration till either ch registered successfully or
     * registration attempts has reached upto maximum attempts allowed by system 
     * it will accept the certain params in $param array, e.g.. cardholder id, mvc type, mvc enroll status etc....
     */
    public function registerCardholder(){
      
            $apisettingModel = new APISettings();
            $validator = new Validator();
            $chRegistered=0;
            $chNotRegistered=0;
            $chFailed=0;
            $failedInfo = array();
            $errorMsg='';
            $apiErrorMsg='';
            
            // first of all checking the api setting is true or not
            if($chkApi = $apisettingModel->checkAPIresponse()) {                
                    
                // now getting 'pending' status's chs from t_cardholders_mvc              
                $cardholders = $this->getCardholders();
                $mvc = new App_Api_MVC_Transactions();
                
                foreach($cardholders as $key=>$chInfo){   
                        $filteredInfo = $validator->validMvcCardholderData($chInfo);
                        
               try {
                    $flg = $mvc->Registration($filteredInfo);
               } catch (Exception $e) {
                   App_Logger::log($e->getMessage(), Zend_Log::ERR);
                   $errorMsg = $e->getMessage(); 
               }
               
               if($flg) {
                            $chRegistered++;
                            $mvcEnrollStatus = STATUS_SUCCESS;
                            $mvcEnrollAttempts = $chInfo['mvc_enroll_attempts'] + 1;
               } else {
                        $mvcEnrollAttempts = $chInfo['mvc_enroll_attempts'] + 1;                        
                        $mvcAttemptsAllowed = App_DI_Container::get('ConfigObject')->mvc->registration->attempts;
                        if($mvcEnrollAttempts>=$mvcAttemptsAllowed){
                           $mvcEnrollStatus = STATUS_FAILED;
                           $failedInfo[] = array('cardholder_name'=>$chInfo['first_name'].' '.$chInfo['last_name'], 
                                               'mobile_number'=>$chInfo['mobile_country_code'].$chInfo['mobile_number'],
                                               'crn'=>$chInfo['crn'],
                                               'errMessage'=>$errorMsg
                                              );
                           $chFailed++;
                        } else {
                                 // case if not registered, but eligible to more attempts
                                 $chNotRegistered++;
                                 $mvcEnrollStatus = STATUS_PENDING;
                        }
                     }
               
               // updating the status of cardholder mvc registration in t_cardholders_mvc
               $this->updateCardholderMVCDetails(array('mvc_enroll_status'=>$mvcEnrollStatus, 'mvc_enroll_attempts'=>$mvcEnrollAttempts), $chInfo['cardholder_id']);
          }    
          
        } // checked api setting true of false
        else
        { 
           $apiErrorMsg = SETTING_API_ERROR_MSG;
        }
        
       // echo $chFailed.'=='; exit;
        // sending intimation mail to ops for failed registration which also exceeds the max registration attempts limit
        if($chFailed>0){
            $m = new App\Messaging\MVC\Axis\Operation();
            $mailInfo['chFailed'] = $chFailed;
            $mailInfo['mvcAttemptsAllowed'] = $mvcAttemptsAllowed;
            $mailInfo['failedInfo'] = $failedInfo;  
            
            $m->failedMvcRegistration($mailInfo);
        }
        
        $ret = array('apiSettingError'=>$apiErrorMsg, 
                     'chRegistered'=>$chRegistered, 
                     'chNotRegistered'=>$chNotRegistered,
                     'chFailed'=>$chFailed,
                    );
       
        return $ret;
      
    } // function closes here
    
    
     /* updateCardholderMVCDetails function will update cardholder details in t_cardholders_mvc .
     * it will accept the certain params in $param array argument, e.g.. cardholder id, mvc enroll status ....
     */
    public function updateCardholderMVCDetails($param, $cardholderId, $mvcEnrollStatus=''){
      
        if($cardholderId<1 || empty($param)){
            throw new Exception('Insufficient data for cardholder MVC updations'); exit;
        }
        
            // checking if not already inserted in db then will insert else will update
            $chMVCInfo = $this->getCardholderMVCInfo($cardholderId);
            if(empty($chMVCInfo)){
                $param['cardholder_id'] = $cardholderId;
                $param['mvc_enroll_status'] = STATUS_PENDING;
                $param['mvc_enroll_attempts'] = 0;                
                $res = $this->addCardholderMVCDetails($param);
            } 
            else { 
                    $where = "cardholder_id='".$cardholderId."'";
                    if($mvcEnrollStatus!='')
                        $where .= " AND mvc_enroll_status ='".$mvcEnrollStatus."'";

                    $param['date_updated'] = NEW Zend_Db_Expr('NOW()');
                    $resp = $this->_db->update(DbTable::TABLE_CARDHOLDERS_MVC, $param, $where);    
                 }
       return true;    
    }
    
    
    public function addCardHolderByAPI($param) {

        $mobObj = new Mobile();
        $emailObj = new Email();
        $validObj = new Validator();
        //$session = new Zend_Session_Namespace('App.Agent.Controller');
        $ip = $this->formatIpAddress(Util::getIP());
        //$chDetails['ip'] = $ip;
        // arn duplicacy check
//          try {                
//                 //$arnCheck = $validObj->checkARNDuplicate(array('arn'=>$chDetails['arn'], 'tablename'=>'cardholder_details'));                
//            } catch (Exception $e ) {
//                App_Logger::log($e->getMessage(), Zend_Log::ERR);
//                return $e->getMessage();
//            }   


        try {
            $mobCheck = $mobObj->checkDuplicate($param['mobile_number'], 'cardholder');
        } catch (Exception $e) {
            //$this->setError($e->getMessage());
            //App_Logger::log($e->getMessage(), Zend_Log::ERR);
            //return $e->getMessage();
            //return false;
            throw new Exception('Duplicate Mobile Number');
        }
        try {
            $emailCheck = $emailObj->checkDuplicate($param['email'], 'cardholder');
        } catch (Exception $e) {
//            echo $e->getMessage();exit('END');
//            App_Logger::log($e->getMessage(), Zend_Log::ERR);
//            return $e->getMessage();
           throw new Exception('Duplicate Email');
            
        }
//        }


        $cardHolderParam= array();
        //$cardHolderParam['product_id'] = $param['product_id'];
        $cardHolderParam['reg_agent_id'] = $param['reg_agent_id'];
        $cardHolderParam['email'] = $param['email'];
        $cardHolderParam['title'] = $param['title'];
        $cardHolderParam['first_name'] = $param['first_name'];
        $cardHolderParam['middle_name'] = $param['middle_name'];
        $cardHolderParam['last_name'] = $param['last_name'];
        $cardHolderParam['mobile_country_code'] = isset($param['mobile_country_code']) ? $param['mobile_country_code'] : '+91';
        $cardHolderParam['mobile_number'] = $param['mobile_number'];
        $cardHolderParam['enroll_status'] = STATUS_PENDING;
        $cardHolderParam['status'] = STATUS_UNBLOCKED;
        //echo '<pre>';print_r($cardHolderParam);exit;
        $resp = $this->_db->insert(DbTable::TABLE_CARDHOLDERS, $cardHolderParam); // adding card holder to t_cardholders   
        if ($resp)
            $chId = $this->_db->lastInsertId(DbTable::TABLE_CARDHOLDERS, 'id');
        $cardholderDetailParam = $param;
        $cardholderDetailParam['cardholder_id'] = $chId;
        $cardholderDetailParam['by_agent_id'] = $param['reg_agent_id'];
        $cardholderDetailParam['status'] = STATUS_INACTIVE;
        
        unset($cardholderDetailParam['reg_agent_id']);
        unset($cardholderDetailParam['product_id']);
        // adding cardholder details to t_cardholder_details
        $this->_db->insert(DbTable::TABLE_CARDHOLDER_DETAILS, $cardholderDetailParam);
//echo '<pre>';print_r($param);exit;
        // adding product to t_cardholders_product              
        $chProd = array('cardholder_id' => $chId, 'product_id' => $param['product_id'],
            'by_agent_id' => $param['reg_agent_id'], 'by_ops_id' => '',
            'datetime' => date('Y-m-d H:i:s'), 'status' => STATUS_ACTIVE
        );

        $this->_db->insert(DbTable::TABLE_CARDHOLDERS_PRODUCT, $chProd);

        //$session->cardholder_id = $chId;
        //$session->cardholder_product_id = $param['product_id'];
        return $chId;
        //return 'success';               
    }

    public function registerCardholderByApi($param) {
        try {
            $ecs = new ECS();
            $chId = $this->addCardHolderByAPI($param);
            $this->setCardholderId($chId);
            $ecs->assignCRN($chId);
            if ($chId > 0) {
                $flg = $this->registerCardholderInECSByApi($chId);
                
                if ($flg) {
                    $flgMVC = $this->registerCardholderInMVCByApi($chId);
                    if ($flgMVC) {
                        $chData = array(
                            'enroll_status' => STATUS_APPROVED,
                            'status' => STATUS_UNBLOCKED,
                        );
                        $this->_db->update(DbTable::TABLE_CARDHOLDERS, $chData, 'id="' . $chId . '"');
                        $chDetailData = array(
                            'status' => STATUS_ACTIVE
                        );
                        $this->_db->update(DbTable::TABLE_CARDHOLDER_DETAILS, $chDetailData, 'cardholder_id="' . $chId . '"');
                        return TRUE;
                    } 
                } 
            }
            return FALSE;
        } catch (App_Api_Exception $e) {
            $this->setError($e->getMessage());
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            return FALSE;
        } catch (Exception $e) {
            $this->setError($e->getMessage());
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            return FALSE;
        }
    }

    private function registerCardholderInECSByApi($chId) {
        $chModel = new Mvc_Axis_CardholderUser();
        $validator = new Validator();
        $chInfo = $chModel->getPendingCardholder($chId);
        $paramArray = $validator->validCardholderData($chInfo);
        $ecsApi = new App_Api_ECS_Transactions();
        $resp = $ecsApi->cardholderRegistration($paramArray);
        if(!$resp) {
            $this->setError($ecsApi->getError());
        }
        return $resp;
    }
    
    private function registerCardholderInMVCByApi($chId) {
        $chModel = new Mvc_Axis_CardholderUser();
        $validator = new Validator();
        $chInfo = $chModel->getPendingCardholder($chId);
        //$paramArray = $validator->validCardholderData($chInfo);
        $array = $validator->validMvcCardholderData($chInfo);        
        $mvcApi = new App_Api_MVC_Transactions();
        $mvcflg = $mvcApi->Registration($array);
        if(!$mvcflg) {
            $this->setError($mvcApi->getError());
        }
        return $mvcflg;
    }
    
    private function setCardholderId($chId)
    {
        $this->_cardholderId = $chId;
    }

    public function getCardholderId()
    {
        return $this->_cardholderId;
    }
    
    /*
     * 
     *  $decryptionKey = App_DI_Container::get('DbConfig')->crnkey; 
        $crn = new Zend_Db_Expr("AES_DECRYPT(`c`.`crn`,'".$decryptionKey."') as crn");
        $select = $this->select()    
                ->from(DbTable::TABLE_CARDHOLDERS . " as c",array($crn,'concat(c.first_name," ",c.last_name) as name'));
                $select->where('c.id = ?', $chId);
     * 
     * 
     * 
     */
    public function getPendingCardholder($chId) {
	$decryptionKey = App_DI_Container::get('DbConfig')->crnkey; 
        $crn = new Zend_Db_Expr("AES_DECRYPT(`ch`.`crn`,'".$decryptionKey."') as crn");
        $select = $this->_db->select()
                //->setIntegrityCheck(false)
                ->from(DbTable::TABLE_CARDHOLDERS . " as ch", array('ch.id', 'ch.reg_agent_id', 'ch.reg_ops_id', 'concat(ch.first_name," ",ch.last_name) as name', 'ch.approval_ops_id', 'ch.reg_datetime', 'ch.approval_datetime', $crn, 'ch.email', 'ch.title', 'ch.first_name', 'ch.middle_name', 'ch.last_name', 'ch.mobile_country_code', 'ch.mobile_number', 'ch.email_verification_id', 'ch.enroll_status', 'ch.status'))
                ->joinLeft(DbTable::TABLE_CARDHOLDER_DETAILS . " as chd", "ch.id = chd.cardholder_id ", array('chd.cardholder_id', 'chd.date_of_birth', 'state', 'city', 'address_line1', 'address_line2', 'country', 'gender', 'mother_maiden_name', 'pincode', 'arn', 'customer_mvc_type', 'device_id', 'educational_qualifications', 'shmart_rewards', 'family_members', 'already_bank_account', 'vehicle_type', 'alternate_contact_number', 'products_acknowledgement', 'rewards_acknowledgement', 'status', 'res_type'))
                ->where('ch.id=?',$chId);
        return $this->_db->fetchRow($select);
    }
    public function getCardholderDetailsByCRN($crn){
	$decryptionKey = App_DI_Container::get('DbConfig')->crnkey; 
        $crnFetch = new Zend_Db_Expr("AES_DECRYPT(`ch`.`crn`,'".$decryptionKey."') as crn");
	$crn = new Zend_Db_Expr("AES_ENCRYPT('".$crn."','".$decryptionKey."')");
	
        $select = $this->select()
                        ->setIntegrityCheck(false)
                        ->from(DbTable::TABLE_CARDHOLDERS . " as ch", array('ch.id','ch.reg_agent_id','ch.reg_ops_id','concat(ch.first_name," ",ch.last_name) as name', 'ch.approval_ops_id','ch.reg_datetime','ch.approval_datetime', $crnFetch,'ch.email','ch.title','ch.first_name','ch.middle_name','ch.last_name','ch.mobile_country_code','ch.mobile_number','ch.email_verification_id','ch.enroll_status','ch.status'))
                        ->joinLeft(DbTable::TABLE_CARDHOLDER_DETAILS." as chd", "ch.id = chd.cardholder_id AND chd.status = '".STATUS_ACTIVE."'", array('chd.cardholder_id','chd.date_of_birth', 'state', 'city', 'address_line1', 'address_line2', 'country', 'gender', 'mother_maiden_name', 'pincode', 'arn','customer_mvc_type','device_id','educational_qualifications','shmart_rewards','family_members','already_bank_account','vehicle_type','alternate_contact_number','products_acknowledgement','rewards_acknowledgement', 'status', 'res_type'))                    
                        ->where('ch.crn =?',$crn)                                            
                        ->where('ch.enroll_status =?', STATUS_APPROVED);   
        $res = $this->fetchRow($select);
        return $res;
    }
    
    
    public function getCardHolderInfoMobile($mobile=0)
    {
	$decryptionKey = App_DI_Container::get('DbConfig')->crnkey; 
        $crnFetch = new Zend_Db_Expr("AES_DECRYPT(`ch`.`crn`,'".$decryptionKey."') as crn");
       
            $select = $this->_db->select()
                ->from(DbTable::TABLE_CARDHOLDERS . " as ch", array('ch.id','ch.reg_agent_id','ch.reg_ops_id','concat(ch.first_name," ",ch.last_name) as name', 'ch.approval_ops_id','ch.reg_datetime','ch.approval_datetime',$crnFetch,'ch.email','ch.title','ch.first_name','ch.middle_name','ch.last_name','ch.mobile_country_code','ch.mobile_number','ch.email_verification_id','ch.enroll_status','ch.status'))
                ->joinLeft(DbTable::TABLE_CARDHOLDER_DETAILS." as chd", "ch.id = chd.cardholder_id AND chd.status = '".STATUS_ACTIVE."'", array('chd.cardholder_id','chd.date_of_birth', 'state', 'city', 'address_line1', 'address_line2', 'country', 'gender', 'mother_maiden_name', 'pincode', 'arn','customer_mvc_type','device_id','educational_qualifications','shmart_rewards','family_members','already_bank_account','vehicle_type','alternate_contact_number','products_acknowledgement','rewards_acknowledgement', 'status', 'res_type'))                    
                ->where('ch.mobile_number =?',$mobile)                                            
                ->where('ch.enroll_status =?',STATUS_APPROVED)                                            
                ->where('ch.status =?',STATUS_UNBLOCKED)                                            
                ->limit('1') ;                   
           return  $this->_db->fetchRow($select);            
    }
}
