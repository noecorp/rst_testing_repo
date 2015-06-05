<?php

class RctMaster extends App_Model
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
    protected $_name = DbTable::TABLE_RCT_MASTER;
    
    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    //protected $_rowClass = 'App_Table_AgentUser';
    

     public function getCityList(){
                $select = $this->select();
                $select->from(DbTable::TABLE_RCT_MASTER);
                $select->where("ref_rec_type = '".RCT_MASTER_CITY_CODE."'");
                $select->order('ref_code');
          $cityArray = $this->fetchAll($select);
          $dataArray = array();
         $dataArray[''] = 'Select City';
        foreach ($cityArray as $id => $val) {
            $dataArray[$val['ref_code']] = $val['ref_desc'];
        }
        
        return $dataArray;
         
     }
   
    
          public function getStateList(){
                $select = $this->select();
                $select->from(DbTable::TABLE_RCT_MASTER);
                $select->where("ref_rec_type = '".RCT_MASTER_STATE_CODE."'");
                $select->order('ref_code');
          $cityArray = $this->fetchAll($select);
          $dataArray = array();
          $dataArray[''] = 'Select State';
        foreach ($cityArray as $id => $val) {
            $dataArray[$val['ref_code']] = $val['ref_desc'];
        }
        
        return $dataArray;
         
     }
     
      public function getOccupationList(){
                $select = $this->select();
                $select->from(DbTable::TABLE_RCT_MASTER);
                $select->where("ref_rec_type = '".RCT_MASTER_OCCUPATION_CODE."'");
                $select->order('ref_code');
          $cityArray = $this->fetchAll($select);
          $dataArray = array();
         $dataArray[''] = 'Select Occupation';
        foreach ($cityArray as $id => $val) {
            $dataArray[$val['ref_code']] = $val['ref_desc'];
        }
        
        return $dataArray;
         
     }
     
       public function getLocationList(){
                $select = $this->select();
                $select->from(DbTable::TABLE_RCT_MASTER);
                $select->where("ref_rec_type = '".RCT_MASTER_DISTRICT_CODE."'");
                $select->order('ref_code');
          $cityArray = $this->fetchAll($select);
          $dataArray = array();
          $dataArray[''] = 'Select District';
        foreach ($cityArray as $id => $val) {
            $dataArray[$val['ref_code']] = $val['ref_desc'];
        }
        
        return $dataArray;
         
     }
     public function getRelationshipList(){
                $select = $this->select();
                $select->from(DbTable::TABLE_RCT_MASTER);
                $select->where("ref_rec_type = '".RCT_MASTER_RELATIONSHIP_CODE."'");
                $select->order('ref_code');
          $cityArray = $this->fetchAll($select);
          $dataArray = array();
         $dataArray[''] = 'Select Relationship';
        foreach ($cityArray as $id => $val) {
            $dataArray[$val['ref_code']] = $val['ref_desc'];
        }
        
        return $dataArray;
         
     }
     
     public function getNomineeRelationshipList(){
         
         
                $select = $this->select();
                $select->from(DbTable::TABLE_RCT_MASTER);
                $select->where("ref_rec_type = '".RCT_MASTER_RELATIONSHIP_CODE."'");
                $select->where("ref_code IN (01, 02, 03, 04, 05)");
                $select->order('ref_code');
                $cityArray = $this->fetchAll($select);
                $dataArray = array();
                $dataArray[''] = 'Select Relationship';
                foreach ($cityArray as $id => $val) {
                    $dataArray[$val['ref_code']] = $val['ref_desc'];
                }

                return $dataArray;

         
     }
     
     
       public function getCommunityList(){
                $select = $this->select();
                $select->from(DbTable::TABLE_RCT_MASTER);
                $select->where("ref_rec_type = '".RCT_MASTER_COMMUNITY_CODE."'");
                $select->order('ref_code');
          $cityArray = $this->fetchAll($select);
          $dataArray = array();
          $dataArray[''] = 'Select Community';
        foreach ($cityArray as $id => $val) {
            $dataArray[$val['ref_code']] = $val['ref_desc'];
        }
        
        return $dataArray;
         
     }
      public function getCityName($cityCode){
         $select = $this->_db->select()
                 ->from(DbTable::TABLE_RCT_MASTER)
                 ->where("ref_code = '$cityCode'")
                 ->where("ref_rec_type = '01'");
         $stateArray = $this->_db->fetchRow($select);
         
        
        return $stateArray['ref_desc'];
         
     }

      
      public function getStateName($stateCode){
         $select = $this->_db->select()
                 ->from(DbTable::TABLE_RCT_MASTER)
                 ->where("ref_code = '$stateCode'")
                 ->where("ref_rec_type = '02'");
         $stateArray = $this->_db->fetchRow($select);
         
        
        return $stateArray['ref_desc'];
         
     }
     
      public function getOccupationName($occupationCode){
         $select = $this->_db->select()
                 ->from(DbTable::TABLE_RCT_MASTER)
                 ->where("ref_code = '$occupationCode'")
                 ->where("ref_rec_type = '21'");
         $stateArray = $this->_db->fetchRow($select);
         
        
        return $stateArray['ref_desc'];
         
     }
      public function getRelationName($relationCode){
         $select = $this->_db->select()
                 ->from(DbTable::TABLE_RCT_MASTER)
                 ->where("ref_code = '$relationCode'")
                 ->where("ref_rec_type = '04'");
         $stateArray = $this->_db->fetchRow($select);
         
        
        return $stateArray['ref_desc'];
         
     }
     
       public function getStateID($stateName){
                $select = $this->select();
                $select->distinct(TRUE);
                $select->from(DbTable::TABLE_RCT_MASTER,array('state_id'));
                $select->where("ref_desc LIKE  '%".$stateName."%'");
                $select->where('NOT ISNULL( state_id ) ');
                $res = $this->fetchRow($select);
                return $res['state_id'];
        }
        
    public function checkCityDuplicacy($params){
         $stateId = isset($params['state_id']) && $params['state_id'] >  0 ? $params['state_id'] :0;
         $select = $this->select();
         $select->from($this);
         $select->where("ref_code = '".$params['ref_code']."' OR ref_desc = '".$params['ref_desc']."'");
         if($stateId > 0){
         $select->where("state_id =?",$params['state_id']);
         }
         $select->where("ref_rec_type =?",RCT_MASTER_CITY_CODE);
         $res = $this->fetchRow($select);  
         if(empty($res)){
            return FALSE;
         }
         else{
           return TRUE;  
         }
         
         
     }    
  
     }
