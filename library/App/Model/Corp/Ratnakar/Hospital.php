<?php
/**
 * Hospital Model
 * This will contain all the method Related to the handling of 
 * Hospital Management)
 *
 * @author Vikram Singh
 * @package HIC Ratnakar
 * @copyright transerv
 */

class Corp_Ratnakar_Hospital extends Corp_Ratnakar
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
    protected $_name = DbTable::TABLE_RAT_CORP_HOSPITAL;
    
    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    //protected $_rowClass = 'App_Table_CardholderUser';
    
    
    /*
     *  getHospitalSearchSql function will return the sql for hospital search
     *  as params:- hospital_id, hospital_id_code, terminal_id_code, hospital_name
     *  pin_code, state, city 
     *  any of above params can be accepted
     */
    public function getHospitalSearchSql($param){
        $hospitalId = isset($param['hospital_id'])?$param['hospital_id']:'';
        $hospitalIdCode = isset($param['hospital_id_code'])?$param['hospital_id_code']:'';
        $terminalIdCode = isset($param['terminal_id_code'])?$param['terminal_id_code']:'';
        $hospitalName = isset($param['hospital_name'])?$param['hospital_name']:'';
        $pinCode = isset($param['pin_code'])?$param['pin_code']:'';
        $state = isset($param['state'])?$param['state']:'';
        $city = isset($param['city'])?$param['city']:'';
       
        
        $select =  $this->select() ; 
        $select->from(DbTable::TABLE_RAT_CORP_HOSPITAL." as hich",array('hich.id as id','hich.hospital_id_code','hich.name','hich.address', 'hich.city', 'hich.state', 'hich.pincode', 'hich.std_code', 'hich.phone'));
        $select->setIntegrityCheck(false);
        $select->joinLeft(DbTable::TABLE_RAT_CORP_TERMINAL." as hict", "hict.hospital_id = hich.id AND hict.status = '".STATUS_ACTIVE."'", array('GROUP_CONCAT(hict.terminal_id_code SEPARATOR ",") AS terminal_id_code', 'GROUP_CONCAT(hict.id SEPARATOR ",") AS terminal_id'));
        $select->where("hich.status = '".STATUS_ACTIVE."'");
        if($terminalIdCode!='')
           $select->where("hict.terminal_id_code = '".$terminalIdCode."'");
        if($hospitalIdCode!='')
           $select->where("hich.hospital_id_code = '".$hospitalIdCode."'");
        if($hospitalId!='')
           $select->where("hich.id = '".$hospitalId."'");
        if($hospitalName!='')
           $select->where("hich.name like '%".$hospitalName."%'");
        if($pinCode!='')
           $select->where("hich.pincode = '".$pinCode."'");
        if($state!='')
           $select->where("hich.state = '".$state."'");
        if($city!='')
           $select->where("hich.city = '".$city."'");
        
           $select->group("hich.hospital_id_code");
           $select->order("hich.hospital_id_code");
        //echo $select->__toString(); exit;
        return $select; 
    }
    
    /*
     *  getHospitalSearch function will search hospital with relavant tids
     *  as params:- hospital_id, hospital_id_code, terminal_id_code, hospital_name
     *  pin_code, state, city 
     *  any of above params can be accepted
     */
    public function getHospitalSearch($param, $page, $paginate=NULL){
        $select = $this->getHospitalSearchSql($param);
        $result = $this->_paginate($select, $page, $paginate); 
        return $result; 
    }
    
    /*
     *  getHospitalDetails function will find hospital details with relavant tids
     *  as params:- hospital_id, hospital_id_code, terminal_id_code, hospital_name
     *  pin_code, state, city 
     *  any of above params can be accepted
     */
    public function getHospitalDetails($param){
        $select = $this->getHospitalSearchSql($param);
        return $this->fetchAll($select);
    }
   
     /* isHospitalDuplicate() will validate hospital duplicacy
      * as param : hospital id
     */
    public function isHospitalDuplicate($hid){
        if($hid!=''){
           $result = $this->getHospitalDetails(array('hospital_id_code'=>$hid));
           $hospital = $result->toArray();
           $countHospital = count($hospital);
           if($countHospital>0)
               throw new Exception('Hospital Id exists');
           else 
               return false;
        } else 
               throw new Exception('Hospital Id not found');
    }
    
    
     /* isTerminalDuplicate() will validate terminals duplicacy
      * as param : terminal id
     */
    public function isTerminalDuplicate($tids){
        $countIds = count($tids); // should be in array form
        $duplicateTids = '';
       
        if($countIds>0){
           for($i=0; $i<$countIds; $i++){
               $tid = trim($tids[$i]); 
               $result = $this->getHospitalDetails(array('terminal_id_code'=>$tid));
               $hospital = $result->toArray();
           
           $countHospital = count($hospital);
           
           if($countHospital>0){
              $countTids = count($duplicateTids);
              if($duplicateTids!='')
                 $duplicateTids .= ', ';
                      
                 $duplicateTids .= $tid;
           }
         }
         
         if($duplicateTids!='')
             throw new Exception("Terminal ids (".$duplicateTids.") exist already");
           
        } else 
               throw new Exception('Terminal id not found');
        
        return false;
    }
    
    
    /* addHospital() will add the hospital details db
     * as param: hospital details
    */
    public function addHospital($params){
        if(is_array($params) && count($params)>0){
        
            $this->_db->insert(DbTable::TABLE_RAT_CORP_HOSPITAL, $params);
            return $this->_db->lastInsertId(DbTable::TABLE_RAT_CORP_HOSPITAL, 'id');
        } else 
            throw new Exception('Adding hospital but details not found');
        
    }
    
    
    /* updateHospital() will update the hospital details db
     * as param: hospital details with hospital id
    */
    public function updateHospital($params, $hospitalId){
        if(is_array($params) && count($params)>0 && $hospitalId>0){
            $logData = $params;
            $objLog = new Log();
            $params['date_updated']= new Zend_Db_Expr('NOW()');
            
            $this->_db->update(DbTable::TABLE_RAT_CORP_HOSPITAL, $params, 'id="'.$hospitalId.'"');
            
            $logData['date_created'] = new Zend_Db_Expr('NOW()');
            $logData['hospital_id'] = $hospitalId;
            $logData['status'] = STATUS_ACTIVE;
            $objLog->insertlog($logData,  DbTable::TABLE_LOG_RAT_CORP_HOSPITAL);

            return true;
        } else 
            throw new Exception('Adding hospital but details not found');
        
    }
    
    
    /* addTerminal() will add the hospital Terminal details db
     * as param: terminal details 
    */
    public function addTerminal($params, $terminalIds){
        if((is_array($params) && count($params)>0) && (is_array($terminalIds) && count($terminalIds)>0)){
        
            $countTerminalIds = count($terminalIds);
            $objLog = new Log();
                    
            $this->_db->beginTransaction(); 
          
        try 
        {
                for($i=0; $i<$countTerminalIds; $i++){
                    $params['terminal_id_code'] = trim($terminalIds[$i]);
                    
                    $this->_db->insert(DbTable::TABLE_RAT_CORP_TERMINAL, $params);
                    $terminalId = $this->_db->lastInsertId(DbTable::TABLE_RAT_CORP_TERMINAL, 'id');
                    $terminalLogData = $params;
                    $terminalLogData['terminal_id'] = $terminalId;        
                    $objLog->insertlog($terminalLogData,  DbTable::TABLE_LOG_RAT_CORP_TERMINAL);
                    
                }
            
                $this->_db->commit();
                return true;

        } catch (Exception $e) {
            // If any of the queries failed and threw an exception,
            // we want to roll back the whole transaction, reversing
            // changes made in the transaction, even those that succeeded.
            // Thus all changes are committed together, or none are.
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_db->rollBack();
            //throw new Exception ("Transaction not completed due to system failure");
            throw new Exception($e->getMessage());
            App_Logger::log($e->getMessage(),  Zend_Log::ERR);
        }
        
    } else 
        throw new Exception('Adding terminal but details not found');
        
  }
    
  /* deleteHospital() will update the hospital and Terminal as deleted status
     * as param: hospital id 
    */
  public function deleteHospital($hospitalId){
      
      if($hospitalId>0){
          $delParam = array('status'=>STATUS_DELETED, 'date_updated'=>new Zend_Db_Expr('NOW()'));         
            
          $this->_db->update(DbTable::TABLE_RAT_CORP_HOSPITAL, $delParam, 'id="'.$hospitalId.'"');
          
          $this->deleteHospitalTerminal($hospitalId);
          return true;
      } else 
          throw new Exception('Deleting hospital but id not found');
  }
  
  /* deleteHospitalTerminal() will update the Terminal as deleted status
     * as param: hospital id, terminal ids, terminals code ids
   *   any of above can be accepted 
    */
  public function deleteHospitalTerminal($hospitalId, $terminalIds = array(), $terminalCodeIds = array()){
      if($hospitalId>0){
          $deleteParams = array('status'=> STATUS_DELETED, 'date_updated'=>new Zend_Db_Expr('NOW()'));
          
          $this->_db->update(DbTable::TABLE_RAT_CORP_TERMINAL, $deleteParams, 'hospital_id="'.$hospitalId.'"');

      } 
      else if(!empty($terminalIds[0]) || !empty($terminalCodeIds[0])){
            
            
            if($terminalIds[0]>0){
                $loopCount = count($terminalIds);
                $data = $terminalIds;
                $fieldName = 'id';
            }
            else if($terminalCodeIds[0]>0){
                $loopCount = count($terminalCodeIds);
                $data = $terminalCodeIds;
                $fieldName = 'terminal_id_code';
                $paramName = 'terminal_code_id';
            }
            
            
            $this->_db->beginTransaction(); 
            
             try 
            {
             for($i=0; $i<$loopCount; $i++){

                 $terminalInfo = $this->getTerminalDetails(array($paramName => $data[$i]));
                 $this->_db->update(DbTable::TABLE_RAT_CORP_TERMINAL, array('status'=>STATUS_DELETED, 'date_updated'=>new Zend_Db_Expr('NOW()')), $fieldName.'="'.$data[$i].'"');
            }
               
               $this->_db->commit();

          } catch (Exception $e) {
               // If any of the queries failed and threw an exception,
               // we want to roll back the whole transaction, reversing
               // changes made in the transaction, even those that succeeded.
               // Thus all changes are committed together, or none are.
               App_Logger::log($e->getMessage(), Zend_Log::ERR);
               $this->_db->rollBack();
               //throw new Exception ("Transaction not completed due to system failure");
               throw new Exception($e->getMessage());
               App_Logger::log($e->getMessage(),  Zend_Log::ERR);
           }

               
      } else
          throw new Exception('Deleting terminal but id not found');
      
      return true;
  }
  
  /* addDeleteHospitalLog() will add the hostpical delete log
     * as param: hospital details , terminal ids(optional)
   *   any of above can be accepted 
    */
  public function addDeleteHospitalLog($hospitalParam, $terminalIds=array()){ 
      if(!empty($hospitalParam)){
          $objLog = new Log();
          $objLog->insertlog($hospitalParam,  DbTable::TABLE_LOG_RAT_CORP_HOSPITAL);
          
          
      } else 
          throw new Exception('Deleting hospital but id not found');
      
      if(!empty($terminalIds[0]))
             $this->addDeleteTerminalLog($terminalIds);
          
      return true;
  }
  
  
  
  
  /*
     *  getTerminalDetails function will return terminal details 
     *  as param:- terminal id and status (optional)
     */
    public function getTerminalDetails($param){
        
        
        if(!empty($param)){
            $terminalId = isset($param['terminal_id'])?$param['terminal_id']:'';
            $terminalCodeId = isset($param['terminal_code_id'])?$param['terminal_code_id']:'';
            $status = isset($param['status'])?$param['status']:'';
            $select =  $this->_db->select(); 
            $select->from(DbTable::TABLE_RAT_CORP_TERMINAL." as hict",array('hict.id','hict.hospital_id', 'hict.terminal_id_code'));
            if($terminalId>0)
                $select->where("hict.id = '".$terminalId."'");
            
            if($terminalCodeId>0)
                $select->where("hict.terminal_id_code = '".$terminalCodeId."'");
            
            if($status!='')
               $select->where("hict.status = '".$status."'");
            
            //echo $select.'---'; exit;
            return $this->_db->fetchRow($select);
        }
    }
  
    /* addDeleteTerminalLog() will add the terminal delete log
     * as param: $terminalIds , $terminalCodeIds (any of both)
   *   any of above can be accepted 
    */
    
  public function addDeleteTerminalLog($terminalIds=array(), $terminalCodeIds=array()){ 
     
      if($terminalIds[0]>0 || $terminalCodeIds[0]>0){
          $objLog = new Log();
          $user = Zend_Auth::getInstance()->getIdentity();
          $this->_db->beginTransaction(); 

        if($terminalIds[0]>0){
             $loopCount = count($terminalIds);
             $data = $terminalIds;
             $fieldName = 'id';
         }
         else if($terminalCodeIds[0]>0){
             $loopCount = count($terminalCodeIds);
             $data = $terminalCodeIds;
             $fieldName = 'terminal_code_id';
         }
            
            
          try 
         {
          for($i=0; $i<$loopCount; $i++){
              
              $terminalInfo = $this->getTerminalDetails(array($fieldName => $data[$i]));
             
              $logParam = array(
                                'terminal_id' => $terminalInfo['id'],
                                'hospital_id' => $terminalInfo['hospital_id'],
                                'terminal_id_code' => $terminalInfo['terminal_id_code'],
                                'status' => STATUS_DELETED,
                                'by_agent_id' => $user->id,
                                'ip' => $this->formatIpAddress(Util::getIP()),
                               );
          
           $objLog->insertlog($logParam,  DbTable::TABLE_LOG_RAT_CORP_TERMINAL);
         }
            $this->_db->commit();
            
       } catch (Exception $e) {
            // If any of the queries failed and threw an exception,
            // we want to roll back the whole transaction, reversing
            // changes made in the transaction, even those that succeeded.
            // Thus all changes are committed together, or none are.
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_db->rollBack();
            //throw new Exception ("Transaction not completed due to system failure");
            throw new Exception($e->getMessage());
            App_Logger::log($e->getMessage(),  Zend_Log::ERR);
        }
      } else 
          throw new Exception('Adding delete terminal log but id not found');
  }
  
  /*
   * Get Hospital Drop down
   */
   public function getHospital()
    {
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_RAT_CORP_HOSPITAL,array('hospital_id_code','name'));
        $select->where("status = ?",STATUS_ACTIVE);
        $bankArr =  $this->_db->fetchAll($select);
        $dataArray = array();
        foreach ($bankArr as $id => $val) {
            $dataArray[$val['hospital_id_code']] = $val['name'];
        }
        return $dataArray;
  
    }
    
    
}