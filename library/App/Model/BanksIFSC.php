<?php
/**
 * Model that manages the products
 *
 * @package Operation_Models
 * @copyright transerv
 */

class BanksIFSC extends App_Model
{
    /**
     * Column for the primary key
     *
     * @var string
     * @access protected
     */
    protected $_primary = 'ifsc_code';
    
    /**
     * Holds the table's name
     *
     * @var string
     * @access protected
     */
    protected $_name = DbTable::TABLE_BANK_IFSC;
    
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
    
    
     
    /**
     * Retrieves all the products attached to
     * the specified master product
     * 
     * @param integer $resourceId
     * @access public
     * @return void
     */
    
   
   
    
    public function getBank($excludeArr = array())
    {
        
        $select = $this->select();
        $select->from(DbTable::TABLE_BANK_IFSC,array('bank_name'));
        $select->distinct(TRUE);
        $select->order('bank_name');
        $excludeStr = '';
        if(!empty($excludeArr)){
            foreach($excludeArr as $val){
                $excludeStr .= "'".$val."',";
            }
        $excludeStr = substr($excludeStr,0,-1);
       
        $select->where("bank_name NOT IN ($excludeStr)");
        }
        $select->where("branch_name !=?", UNIVERSAL_BRANCH);
        $bankArr =  $this->fetchAll($select);
//        echo $select->__toString();
        $dataArray = array(''=>'Select Bank Name');
        foreach ($bankArr as $id => $val) {
            $dataArray[$val['bank_name']] = $val['bank_name'];
        }
        return $dataArray;
  
    }
    
     public function getIFSC($bank = NULL,$ifsc = NULL,$type='')
    {
        $select = $this->select();
        $select->from(DbTable::TABLE_BANK_IFSC,array('ifsc_code'));
        if($bank)
        	$select->where("bank_name = '$bank' ");	
        if($ifsc)
        	$select->where("ifsc_code LIKE '$ifsc%' ");
        if($type)
        {
            $select = $select->where("enable_for='".ENABLE_FOR_ALL."' OR  enable_for = '".strtolower($type)."'");
        }

        $select->order("ifsc_code ASC");
        $ifscArr =  $this->fetchAll($select);
        $dataArray = array();
        $dataArray[''] = 'Select IFSC';
        foreach ($ifscArr as $id => $val) {
            $dataArray[$val['ifsc_code']] = $val['ifsc_code'];
        }
        return $dataArray;
  
    }
    
    public function getDetailsByIFSC($ifsc=NULL,$type='')
    {
        $select = $this->select();
        $select->from(DbTable::TABLE_BANK_IFSC,array('address','city','branch_name','bank_name','state'));
        $select->where("ifsc_code = '$ifsc' ");
        if($type)
        {
            $select = $select->where("enable_for = '".strtolower($type)."' OR enable_for='".ENABLE_FOR_ALL."'");
        }

        $detailsArr =  $this->fetchRow($select);
        $dataStr=0;
        $dataStr = $detailsArr['bank_name'].'^'.$detailsArr['state'].'^'.$detailsArr['city'].'^'.$detailsArr['branch_name'].'^'.$detailsArr['address'];
        return $dataStr;
  
    }
    public function getStateByName($bank)
    { 
        $select = $this->select();
        $select->from(DbTable::TABLE_BANK_IFSC,array('state'));
        $select->where("bank_name = '$bank' ");
        $select->distinct(TRUE);
        $select->order('state');
        
        $ifscArr =  $this->fetchAll($select);
        $dataArray = array();
        foreach ($ifscArr as $id => $val) {
            $dataArray[$val['state']] = $val['state'];
        }
        return $dataArray;
  
    }
    public function getCityByName($bank,$state)
    { 
        $select = $this->select();
        $select->from(DbTable::TABLE_BANK_IFSC,array('city'));
        if($bank)
        	$select->where("bank_name = '$bank' ");
        if($state)
        	$select->where("state = '$state' ");
      	$select->distinct(TRUE);
        $select->order('city');
        $cityArr =  $this->fetchAll($select);
        $dataArray = array();
        foreach ($cityArr as $id => $val) {
            $dataArray[$val['city']] = $val['city'];
        }
        return $dataArray;
  
    }
     public function getBranchesByName($bank,$city)
    { 
        $select = $this->select();
        $select->from(DbTable::TABLE_BANK_IFSC,array('branch_name'));
        if($city)
        	$select->where("city = '$city' ");
        if($bank)
        	$select->where("bank_name = '$bank' ");
      	$select->distinct(TRUE);
        $select->order('branch_name');
        $ifscArr =  $this->fetchAll($select);
        $dataArray = array();
        foreach ($ifscArr as $id => $val) {
            $dataArray[$val['branch_name']] = $val['branch_name'];
        }
        return $dataArray;
  
    }
     public function getBranchAddress($bank,$branch,$type)
    {
        $select = $this->select();
        $select->from(DbTable::TABLE_BANK_IFSC,array('address','ifsc_code'));
        $select->where("bank_name = '$bank' ");
        $select->where("branch_name = '$branch' ");
        if($type)
        {
            $select->where("enable_for='".ENABLE_FOR_ALL."' OR  enable_for = '".strtolower($type)."' ");
        }

        $detailsArr =  $this->fetchRow($select);
        $dataStr = $detailsArr['address'].'^'.$detailsArr['ifsc_code'];
        return $dataStr;
  
    }
    
    public function getUniverSalBankDetails($bank=NULL)
    {
        $select = $this->select();
        $select->from(DbTable::TABLE_BANK_IFSC,array('address','city','branch_name','bank_name','state','ifsc_code'));
        $select->where("bank_name = '$bank' ");
        $select->where("branch_name =?", UNIVERSAL_BRANCH);   
        $detailsArr =  $this->fetchRow($select);
        $dataStr=0;
        $dataStr = $detailsArr['bank_name'].'^'.$detailsArr['state'].'^'.$detailsArr['city'].'^'.$detailsArr['branch_name'].'^'.$detailsArr['address'].'^'.$detailsArr['ifsc_code'];
        return $dataStr;
  
    }
    
    public function getUniverSalBank()
    {
        
        $select = $this->select();
        $select->from(DbTable::TABLE_BANK_IFSC,array('bank_name'));
	$select->where("branch_name =?", UNIVERSAL_BRANCH);       
        $select->distinct(TRUE);
        $select->order('bank_name');
        $bankArr =  $this->fetchAll($select);
        $dataArray = array(''=>'Select Bank Name');
        foreach ($bankArr as $id => $val) {
            $dataArray[$val['bank_name']] = $val['bank_name'];
        }
        return $dataArray;
    }
  
    public function getDetailsByIFSCCode($ifsc,$remitType)
    {
        $select = $this->select();
        $select->from(DbTable::TABLE_BANK_IFSC,array('ifsc_code', 'bank_name', 'micr_code', 'branch_name', 'address', 'contact', 'city', 'district', 'state', 'enable_for', 'exclude'));
        $select->where("ifsc_code = '$ifsc' ");
        if(!empty($remitType)) {
            $select->where("enable_for='".strtolower($remitType)."' OR enable_for='all'");
        }
        return $this->fetchRow($select);
    }
    
    public function getListIfsc($data) {
        $select = $this->select();
        $select->from(DbTable::TABLE_BANK_IFSC,array('*'));
        $select->where("ifsc_code = ?",$data['ifsc_code']);
	$select->where("bank_name = ?",$data['bank_name']);
        return $this->fetchAll($select);
    }
    
    
    public function addnewIfsc($data) {
	/*
	 * Check Already Exist Ifsc Code 
	 */
	if(count($this->getListIfsc($data)) > 0){
	    throw new Exception('This ifsc Code Already exist.');
	} else{ 
	    try {
		$this->insert($data);
		return array('bank_name'=>$data['bank_name'],'ifsc_code'=>$data['ifsc_code']);
	    } catch (Exception $e) {
		App_Logger::log($e->getMessage(), Zend_Log::ERR);
		throw new Exception($e->getMessage());
		return false;
	    }
	} 
    }
}
