<?php

class Crons extends BaseUser
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
    protected $_name = DbTable::TABLE_CRON;
    
    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    //protected $_rowClass = 'App_Table_Crons';
    
    
   public function addCronLog($param){ 
       $cronId = isset($param['cron_id'])?$param['cron_id']:'';
       $cronParam['status_cron'] = STATUS_STARTED;
       
       if($cronId>0){
        $resp = $this->_db->insert(DbTable::TABLE_LOG_CRON, $param); // adding cron log   
        return $this->_db->lastInsertId(DbTable::TABLE_LOG_CRON, 'id');
       } else {
           //throw new Exception('No sufficient data found');
           App_Logger::log('Unable to update cron due to insufficiant data', Zend_Log::ERR);
       }
   }
   
   public function updateCronLog($param) {
        $cronId = isset($param['cron_id']) ? $param['cron_id'] : '';
        $cronLogId = isset($param['id']) ? $param['id'] : '';
        $param['date_end'] = NEW Zend_Db_Expr('NOW()');

        if ($cronLogId > 0 && $cronId > 0) {
            //$where = " id = '".$cronId."' AND date_end = '0000-00-00 00:00:00' AND message = ''";
            $resp = $this->_db->update(DbTable::TABLE_LOG_CRON, $param, "id='$cronLogId'"); // updating cron log   
        } else {
            //throw new Exception('No sufficient data found');
            App_Logger::log('Unable to update cron due to insufficiant data', Zend_Log::ERR);
        }
    }

    public function getCronInfo($param){
        $cronId = isset($param['cron_id'])?$param['cron_id']:'';
       
        if($cronId>0) {

                        $select = $this->select()
                       ->setIntegrityCheck(false)
                       ->from(DbTable::TABLE_CRON." as c", array('c.status','c.status_cron', 'c.date_updated', 'c.name'))
                       ->where('c.id=?',$cronId); 
            
                       //echo $select->__toString();exit;
                       return $this->fetchRow($select);
        }
        else {
            throw new Exception('Insufficient data found!');
        }
   }
   
   public function updateCron($param){ 
       $cronId = isset($param['id'])?$param['id']:'';
       
       if($cronId>0){
          $where = " id = '".$cronId."'";
          $resp = $this->_db->update(DbTable::TABLE_CRON, $param, $where); // updating cron log   
       } else {
                throw new Exception('No sufficient data found');
       }
   }
   
   
   public function sendECSCRNAlert(){
      // $objEcs = new ECS();
       //$resp = $objEcs->getECSCount();
   }
   
   
    /*  removeIncompleteAgentsCardholders function will remove agents and cardholders and thier concerned details with incomplete status from db
     */
   public function removeIncompleteAgentsCardholders(){
    
      // removing agents 
      $totalAgents=0;
      $agentUserObj = new AgentUser();
      try{
         $totalAgents = $agentUserObj->removeIncompleteAgents();
        }catch(Exception $e){
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            throw new Exception($e->getMessage());
        }
        
      // removing cardholders
     /* $totalCH=0;
      $chUserObj = new Mvc_Axis_CardholderUser();
      try{
         $totalCH = $chUserObj->removeIncompleteCardholders();
        }catch(Exception $e){
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            throw new Exception($e->getMessage());
        }
        
        return array('agentsRemoved'=>$totalAgents, 'cardholdersRemoved'=>$totalCH);
*/
        return array('agentsRemoved'=>$totalAgents, 'cardholdersRemoved'=>0);
   
   }   
   
   /*  getCronsForDD() will return crons for drop down
   */
   public function getCronsForDD($param)
   {
        $select  = $this->_db->select();        
        //$select->setIntegrityCheck(FALSE)
        $select->from(DbTable::TABLE_CRON.' as c',array('c.id', 'c.name',));
                
        if(isset($param['status']))
            $select->where("c.status =?", $param['status']);
               
        //echo $select; exit;
        
        $crons = $this->_db->fetchAll($select);     
           
        $dataArray = array();
        //$dataArray[''] = "Select Fund Transfer Type";
        foreach ($crons as $id => $val) {
            $dataArray[$val['id']] = $val['name'];
        }

        return $dataArray;     
    }
    
    
   /*  getCronLogs() will return cron logs
    * as param :- cron id and status
   */
   public function getCronLogs($param, $page = 1, $paginate = NULL)
   {
        $from = isset($param['from'])?$param['from']:'';
        $to = isset($param['to'])?$param['to']:'';
        $cronId = isset($param['cron_id'])?$param['cron_id']:'';
        $select  = $this->_db->select();        
        //$select->setIntegrityCheck(FALSE)
        $select->from(DbTable::TABLE_LOG_CRON.' as lc',array('lc.message', 'lc.date_start', 'lc.date_end' ));
        $select->joinLeft(DbTable::TABLE_CRON.' as c', 'lc.cron_id=c.id', array('c.status_cron'));
        $select->where("lc.cron_id = ?", $cronId);
        $select->where("DATE(lc.date_start) BETWEEN '".$from."' AND '".$to."'");        
               
        //echo $select; exit;
        
        $cronLogs = $this->_db->fetchAll($select);     
           
        return $cronLogs;     
    }
}
