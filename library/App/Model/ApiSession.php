<?php

class ApiSession extends App_Model
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
    protected $_name = DbTable::TABLE_API_SESSION;
    
 
    
    /**
     * Update API Session
     *
     * @param array $inputArray 
     * @access public
     * @return bool
     */
    public function updateSession(array $inputArray)
    {
        //print_r($inputArray);exit;
        //Validate Session
        if(!isset($inputArray['sessionId'])) {
            //$this->setError("Invalid SessionID provided");
            return false;
        }
        
        //Validate User Id
        if(!isset($inputArray['userId']) || $inputArray['userId'] == '') {
            //$this->setError("Invalid SessionID provided");
            return false;
        }

        $rs = $this->select()
             ->where("tp_user_id = ?",$inputArray['userId'])
             //->where("status=?",'active')
             ->order('date_updated');        
        
        $data = $this->fetchRow($rs);      
        //echo "<pre>";print_r($data);exit;
        if(empty($data)) {
            //print 'In here';exit;
            $insArr = array(
              'status'          => (isset($inputArray['status']) &&  $inputArray['status'] !='') ? $inputArray['status'] : 'success',
              'session_id'      => $inputArray['sessionId'],
              'tp_user_id'      => $inputArray['userId'],                
              'date_updated'    => new Zend_Db_Expr('NOW()')
            );

            $this->insert($insArr);
            
        }  else {      

            $updateArr = array(
              'status'  => $inputArray['status'],
              'session_id'  => $inputArray['sessionId'],
              'date_updated'  => new Zend_Db_Expr('NOW()')
            );

            $this->_db->update("api_session",$updateArr , 'tp_user_id = "'.$inputArray['userId'].'"');
        }
        return true;
        
    }
    
     /**
     * getLastApiSession
     * Get Last API Session
     * @param int $tpUserId
     * @return array
     */
    public function getLastSession($tpUserId) {
           $rs = $this->select()
                ->where("tp_user_id = ?",$tpUserId)
                ->where("status=?",'success')
                ->order('date_updated');
        $data = $this->fetchRow($rs);      
        if(!empty($data)) {
            return $data->toArray();
        }
        return array();
    }
       
 
}