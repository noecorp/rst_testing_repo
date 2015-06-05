<?php

class ApiSoapCall extends App_Model
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
    protected $_name = DbTable::TABLE_API_SOAP_CALLS;
    
  
    /**
     * log api details into database
     *
     * @param array $inputArray 
     * @access public
     * @return bool
     */
    public function log(array $inputArray)
    {
       
        $request = Util::apiSerialize($inputArray['request']);
        $response = Util::apiSerialize($inputArray['response']);
        $encryptionKey = App_DI_Container::get('DbConfig')->key;
        
        $inputRequest = new Zend_Db_Expr("AES_ENCRYPT('".$request."','".$encryptionKey."')");
        $inputResponse = new Zend_Db_Expr("AES_ENCRYPT('".$response."','".$encryptionKey."')");
        
      
        $inputArr = array(
                'tp_user_id'    => $inputArray['user_id'],
                'method'        => $inputArray['method'],
                'request'       => $inputRequest,
                'response'      => $inputResponse,
                'user_ip'       => Util::getIP(),
                //'source'        => isset($inputArray['source']) ? $inputArray['source'] : '',
                'date_created' => new Zend_Db_Expr('NOW()')
        );
        //return $this->insert($inputArr);
        $logger = new ShmartLogger(ShmartLogger::LOG_API_SOAP);
        return $logger->log($inputArray);        
    }
 
}