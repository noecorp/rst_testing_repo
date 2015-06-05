<?php

class SolIdList extends App_Model
{
    /**
     * Column for the primary key
     *
     * @var string
     * @access protected
     */
    protected $_primary = 'code';
    
    /**
     * Holds the table's name
     *
     * @var string
     * @access protected
     */
    protected $_name = DbTable::TABLE_SOL_ID_LIST;
    
    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    //protected $_rowClass = 'App_Table_AgentUser';
    
    
    public function getSTDcode(){
         $select = $this->select()
                 ->from($this,array('std_code'));
        $select->distinct(TRUE);
        $select->order('std_code');
                
         $stdArray = $this->fetchAll($select);
         $dataArray = array();
         $dataArray = array('' => 'Select STD Code'); 
        foreach ($stdArray as $val) {
            $dataArray[str_pad($val['std_code'], 3, "0", STR_PAD_LEFT)] = str_pad($val['std_code'], 3, "0", STR_PAD_LEFT);
        }
        
        return $dataArray;
         
     }
     public function getCityByStateCode($stateCode=''){
                $select = $this->select();
                $select->from($this);
                if($stateCode!=''){
                    $select->where("state_code = '$stateCode'");
                }
                $select->order('name');
          $cityArray = $this->fetchAll($select);
          $dataArray = array();
         
        foreach ($cityArray as $id => $val) {
            $dataArray[$val['name']] = $val['name'];
        }
        
        return $dataArray;
         
     }
   
       public function getCityCode($cityName){
         $select = $this->select()
                 ->from($this)
                 ->where("name = '$cityName'");
         $cityArray = $this->fetchRow($select);
                 
        return $cityArray['code'];
         
         
     }
     
     public function getStateList($countryCode = 356){
          $select = $this->_db->select()
                 ->from(DbTable::TABLE_STATES)
                  ->order('name ASC');
          $stateArray = $this->_db->fetchAll($select);
          $dataArray = array();
         $dataArray = array('' => '  Select State ');
        foreach ($stateArray as $id => $val) {
            $dataArray[$val['code']] = $val['name'];
        }
        
        return $dataArray;
         
     }
     
      public function getPincodeList($cityCode){
          $select = $this->select()
                 ->from($this)
                 ->where("code = '$cityCode'") 
                 ->order('pincode')
                 ->group('pincode');
          $pincodeArray = $this->fetchAll($select);
          $dataArray = array();
         
        foreach ($pincodeArray as $id => $val) {
            $dataArray[$val['pincode']] = $val['pincode'];
        }
        
        return $dataArray;
         
     }
     
     public function getStateCode($stateName){
         $select = $this->_db->select()
                 ->from(DbTable::TABLE_STATES)
                 ->where("name = '$stateName'");
         $stateArray = $this->_db->fetchRow($select);
         
        
        return $stateArray['code'];
         
     }
     
      public function getStateName($stateCode){
         $select = $this->_db->select()
                 ->from(DbTable::TABLE_STATES)
                 ->where("code = '$stateCode'");
         $stateArray = $this->_db->fetchRow($select);
         
        
        return $stateArray['name'];
         
     }
        public function getPincodeByState($stateCode){
          $select = $this->select()
                 ->from($this)
                 ->where("state_code = '$stateCode'") 
                 ->order('pincode')
                 ->group('pincode');
          $pincodeArray = $this->fetchAll($select);
          $dataArray = array();
         
        foreach ($pincodeArray as $id => $val) {
            $dataArray[$val['pincode']] = $val['pincode'];
        }
        
        return $dataArray;
         
     }
     
}