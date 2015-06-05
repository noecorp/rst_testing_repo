<?php
/**
 * Model that manages the products
 *
 * @package Operation_Models
 * @copyright transerv
 */

class GlobalPurse extends App_Model
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
    protected $_name = DbTable::TABLE_GLOBAL_PURSE_MASTER;
    
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
     
    public function mcclist(){
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_MCC_MASTER);
        $select->order('category');
        $select->order('sub_category');
        
        return $this->_db->fetchAll($select);
    }
    
    
     public function bindWalletToMCC($dataArr,$walletId){

         $user = Zend_Auth::getInstance()->getIdentity(); 
         $insertMccArr = array();
         $this->_db->beginTransaction(); 
          try 
            {
         
        if(!empty($dataArr)) {
            $this->insertLogWalletMCCdetails($walletId);
           
            $this->deletePrevWalletMCCdetails($walletId);
           
            foreach ($dataArr as $val) {
             
                    $insertMccArr['global_purse_id'] = $walletId; 
                    $insertMccArr['mcc_code'] = $val; 
                    $insertMccArr['datetime_start'] = new Zend_Db_Expr('NOW()'); 
                    $insertMccArr['date_created'] = new Zend_Db_Expr('NOW()'); 
                    $insertMccArr['date_updated'] = new Zend_Db_Expr('NOW()'); 
                    $insertMccArr['by_ops_id'] = $user->id; 
                    $insertMccArr['status'] = STATUS_ACTIVE; 
                    $this->_db->insert(DbTable::TABLE_BIND_GLOBAL_PURSE_MCC,$insertMccArr);
                }
             $this->_db->commit();
                
            }
            
             }catch (Exception $e) {
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                        $this->_db->rollBack();
                }
        }

        
        public function deletePrevWalletMCCdetails($walletId){
        $res = $this->getWalletMCCdetails($walletId);
       
            if(!empty($res)){
                $this->_db->delete(DbTable::TABLE_BIND_GLOBAL_PURSE_MCC,"global_purse_id = $walletId");
            }
            return TRUE;
        }
  
        public function insertLogWalletMCCdetails($walletId){
          $user = Zend_Auth::getInstance()->getIdentity(); 
          $previousbinding = $this->getWalletMCCdetails($walletId);
          $logArr = array();
          if(!empty($previousbinding)) {
            foreach ($previousbinding as $data) {
                
                    $loggArr['bind_id'] = $data['id']; 
                    $loggArr['global_purse_id'] = $data['global_purse_id']; 
                    $loggArr['mcc_code'] = $data['mcc_code']; 
                    $loggArr['datetime_start'] = $data['datetime_start']; 
                    $loggArr['datetime_end'] = new Zend_Db_Expr('NOW()'); 
                    $loggArr['date_updated'] = new Zend_Db_Expr('NOW()'); 
                    $loggArr['by_ops_id'] = $user->id; 
                    $loggArr['status'] = STATUS_ACTIVE; 
                    $this->_db->insert(DbTable::TABLE_LOG_BIND_GLOBAL_PURSE_MCC,$loggArr);
                }
            
                
            }
            return TRUE;
        
        }
        
        
        public function getWalletMCCdetails($walletId){
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_BIND_GLOBAL_PURSE_MCC);
        $select->where("global_purse_id = ?",$walletId);
        return $this->_db->fetchAll($select);
        
        }
        
        public function getWalletMCCArray($walletId){
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_BIND_GLOBAL_PURSE_MCC, array('mcc_code'));
        $select->where("global_purse_id = ?",$walletId);
        $res =  $this->_db->fetchAll($select);
        $bindArr = array();
        $i = 0;
        foreach($res as $val){
            $bindArr[$i] = $val['mcc_code'];
            $i++;
        }
        return $bindArr;
        
        }
        
        public function getWalletMCCList($walletId){
        $select = $this->select();
        $select->setIntegrityCheck(FALSE);
        $select->from(DbTable::TABLE_BIND_GLOBAL_PURSE_MCC ." as bp",array('bp.datetime_start','bp.date_created'));
        $select->joinLeft(DbTable::TABLE_MCC_MASTER ." as mcc","mcc.mcc_code = bp.mcc_code",array('mcc.mcc_code','mcc.category','mcc.sub_category'));
        $select->joinLeft(DbTable::TABLE_OPERATION_USERS ." as ops","bp.by_ops_id = ops.id",array('concat(ops.firstname," ",ops.lastname) as created_by'));
        $select->where("bp.global_purse_id = ?",$walletId);
       
        return $this->fetchAll($select);
        
        }
}
    
    
