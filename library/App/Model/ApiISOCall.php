<?php

class ApiISOCall extends App_Model
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
    protected $_name = DbTable::TABLE_API_ISO_CALLS;
    
  
    /**
     * log api details into database
     * 
     * @param array $inputArray 
     * @access public
     * @return bool
     */
    public function log(array $inputArray)
    {
        //Validate value - Logger function can't afford to generate error
        $inputArr = array(
                'tp_user_id'    => isset($inputArray['user_id']) ? $inputArray['user_id'] : '',
                'method'        => isset($inputArray['method']) ? $inputArray['method'] : '',
                'request'       => isset($inputArray['request']) ? $inputArray['request'] : '',
                'response'      => isset($inputArray['response']) ? $inputArray['response'] : '',
                'exception'     => isset($inputArray['exception']) ? $inputArray['exception'] : '',
                'response_message'     => isset($inputArray['response_message']) ? $inputArray['response_message'] : '',
                'date_created' => new Zend_Db_Expr('NOW()')
        );
        return $this->insert($inputArr);
    }
     
    
    public function getInfoById($id) {
        $sql = $this->select()
                ->where('id=?',$id);
        return $this->fetchRow($sql);
    }
}