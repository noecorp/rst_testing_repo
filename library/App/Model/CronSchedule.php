<?php

class CronSchedule extends BaseUser
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
    protected $_name = DbTable::TABLE_CRON_SCHEDULE;
    

    /**
     * getCronlist
     * Get list of active cron for defined parameter
     * @param array $param
     * @return resultset
     */
  public function getCronlist($param){

      try {
      $allowedMin = '00:15:00';
      
                        $select = $this->select()
                        ->setIntegrityCheck(false)
                        ->from(DbTable::TABLE_CRON_SCHEDULE." as cs")
                        ->join(DbTable::TABLE_CRON." as c", "cs.cron_id = c.id")
                       ->where('cs.schedule_day=?',$param['day'])
                       ->where("cs.schedule_time BETWEEN SUBTIME('".$param['time']."','".$allowedMin."') AND '".$param['time']."' ")
                       ->where('cs.status=?',STATUS_ACTIVE); 
                       return $this->fetchAll($select);
      } catch (Exception $e) {
          App_Logger::log($e->getMessage(), Zend_Log::ERR);          
          return false;
      }
   }
   
    
}