<?php

class ApiUser extends App_Model
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
    protected $_name = DbTable::TABLE_API_USER;
    
    /**
     * Holds the Session Fie Path
     *
     * @var string
     * @access protected
     */
    protected $_filePath = BASE_URL_SHMART_LOGS;


    protected $_userdata;


    /**
     * Api User Login
     *
     * @param array $inputArray 
     * @access public
     * @return bool
     */
   /* public function login(array $inputArray)
    {
        
        //Validate Username
        if(!isset($inputArray['username']) || $inputArray['username'] == '') {
            $this->setError("Invalid Username provided");
            return false;
        }
        
        
        //Validate Password
        if(!isset($inputArray['password']) || $inputArray['password'] == '') {
            $this->setError("Invalid password provided");
            return false;
        }
        
        $rs = $this->select()
                ->where("username = ?",$inputArray['username'])
                ->where("password=?",$inputArray['password'])
                ->where("status=?",'active');
        $data = $this->fetchRow($rs);
        return $data;
        
    }*/
    /**
     * Api User Login
     *
     * @param array $inputArray 
     * @access public
     * @return bool
     */
    public function login(array $inputArray)
    {
        
        //Validate Username
        if(!isset($inputArray['username']) || $inputArray['username'] == '') {
            $this->setError("Invalid Username provided");
            return false;
        }
        
        
        //Validate Password
        if(!isset($inputArray['password']) || $inputArray['password'] == '') {
            $this->setError("Invalid password provided");
            return false;
        }
        
        //Validate Password
        if(!isset($inputArray['ip']) || $inputArray['ip'] == '') {
            $this->setError("Unable to fetch user IP");
            return false;
        }
        
        $encryptionKey = App_DI_Container::get('DbConfig')->key;
        $password = new Zend_Db_Expr("AES_ENCRYPT('".$inputArray['password']."','".$encryptionKey."')");
        
        $rs = $this->select()
                ->from($this->_name, array('id'))
                ->where("username = ?",$inputArray['username'])
                ->where("password=?",$password)
                ->where("status=?",'active');
        if(isset($inputArray['tp_user_id']) && !empty($inputArray['tp_user_id']) ) {
            $rs->where('tp_user_id=?', $inputArray['tp_user_id']);
        }
        $data = $this->fetchRow($rs);
        //echo '<pre>';print_r($data);exit;
        if(!empty($data)) {
            $ipData = $this->getUserIP($data);
            $flg = $this->validateIP($inputArray['ip'], $ipData);
            if(!$flg) {
                return false;
            }
            $sessionId = $this->generateSessionId($data);
            if( $sessionId === FALSE) {
                return FALSE;
            }
            return $sessionId;
        }
        return FALSE;
        
    }
    
    
    private function getUserIP($data) {
        $sql = $this->_db->select()
                ->from(DbTable::TABLE_API_USER_IP, array('tp_user_ip'))
                ->where('tp_user_id = ?',$data['id']);
        $rs = $this->_db->fetchRow($sql);
        return $rs;
        
    }
    
    
    private function validateIP($userIP, $data) {

        if(empty($data)) {
            return false;
        }
        $ipArr = explode(',', $data['tp_user_ip']);
        if(in_array($userIP, $ipArr)) {
            return true;
        }
        return false;
    }
    
    /**
     * Api User logoff
     *
     * @param array $inputArray 
     * @access public
     * @return bool
     */
    public function logoff(array $inputArray)
    {
        
        //Validate Password
        if(!isset($inputArray['sessionId']) || $inputArray['sessionId'] == '') {
            $this->setError("Invalid SessionID provided");
            return false;
        }
        
        $iterator = new GlobIterator($this->_filePath . DIRECTORY_SEPARATOR . "shmart_session" . DIRECTORY_SEPARATOR . "*_" . $inputArray['sessionId'] . ".txt" ,  GlobIterator::CURRENT_AS_PATHNAME);
        if (isset($iterator))
        {
            foreach ($iterator as $item) 
            {
                $oldFile = $iterator->key();
                $newFile = str_replace("shmart_session","shmart_session_completed", $oldFile);
                rename($oldFile, $newFile);
            }
        }
        return true;
        
    }
    
    
    private function generateSessionId($inputArr) {
        $sessionId = $this->generateRandId();
        $userId = $inputArr['id'];
        $iterator = new GlobIterator($this->_filePath . DIRECTORY_SEPARATOR . "shmart_session" . DIRECTORY_SEPARATOR . $userId . "_*.txt" ,  GlobIterator::CURRENT_AS_PATHNAME);
        if (isset($iterator))
        {
            foreach ($iterator as $item) 
            {
                $oldFile = $iterator->key();
                $newFile = str_replace("shmart_session","shmart_session_completed", $oldFile);
                rename($oldFile, $newFile);
            }
        }

        $updateArr = array(
            'api_user_id'   => $userId,
            'status'        => 'started',
            'session_id'    => $sessionId,
            'date_created'  => date('Y-m-d H:i:s'),
            'date_updated'  => date('Y-m-d H:i:s')
            );

        $filePath = $this->_filePath . DIRECTORY_SEPARATOR . "shmart_session" . DIRECTORY_SEPARATOR . $userId . "_" . $sessionId . ".txt";

        $resp = $this->writeFile($filePath, json_encode($updateArr));
        if($resp == false)
            return FALSE;
        else
            return $sessionId;
    }
    
    
    public function validateSession($sessionId) {

        $allowedSessionInMin = App_DI_Container::get('ConfigObject')->system->api->timeout_in_min;
        $currentFile = "";

        $iterator = new GlobIterator($this->_filePath . DIRECTORY_SEPARATOR . "shmart_session" . DIRECTORY_SEPARATOR . "*_" . $sessionId . ".txt" ,  GlobIterator::CURRENT_AS_PATHNAME);
        if (isset($iterator))
        {
            // Take only first file of session.
            foreach ($iterator as $item) 
            {
                $currentFile = $iterator->key();
                break;
            }
        }
        if($currentFile == ""){

            return false;
        }
        $rawData =  $this->readFile($currentFile);
        $authData = json_decode($rawData);
        $userId = $authData->api_user_id;
        $status = $authData->status;
        $dateUpdated = $authData->date_updated;
        $dateCreated = $authData->date_created;

        $validUpto = new DateTime();
        $validUpto->createFromFormat('Y-m-d H:i:s', $dateUpdated);
        $validUpto->add(new DateInterval('PT' . $allowedSessionInMin . 'M'));  

        $currentTime = new DateTime('NOW');

        if($currentTime < $validUpto){
            $updateArr = array(
                'api_user_id'   => $userId,
                'status'        => 'started',
                'session_id'    => $sessionId,
                'date_created'  => $dateCreated,
                'date_updated'  => date('Y-m-d H:i:s')
                );

            $resp = $this->writeFile($currentFile, json_encode($updateArr),'w');
            if($resp == false)
                return false;
            else
                return true;
        }
        else{
            return false;
        }
        return false;
    }
    
    private function generateRandId() {
        $a = sha1(rand(0, 1000000));    
        return strtoupper(substr($a, 0, 10));
    }
    
    
    public function setError($msg) {
        $this->msg = $msg;
    }
    
    public function getError() {
        return $this->msg;
    }
    
     public function chkLogin(array $inputArray) {
        
        //Validate Username
        if(!isset($inputArray['username']) || $inputArray['username'] == '') {
            $this->setError("Invalid Username provided");
            return false;
        }
        
        
        //Validate Password
        if(!isset($inputArray['password']) || $inputArray['password'] == '') {
            $this->setError("Invalid password provided");
            return false;
        }
        
        //Validate Password
        if(!isset($inputArray['ip']) || $inputArray['ip'] == '') {
            $this->setError("Unable to fetch user IP");
            return false;
        }
        
        $encryptionKey = App_DI_Container::get('DbConfig')->key;
        $password = new Zend_Db_Expr("AES_ENCRYPT('".$inputArray['password']."','".$encryptionKey."')");
        
       
        $rs = $this->select()
                ->where("username = ?",$inputArray['username'])
                ->where("password=?",$password)
                ->where("status=?",'active');
       
        $data = $this->fetchRow($rs);
        if(!empty($data)) {
            return TRUE;
        }
        return FALSE;
        
    }
    
    public function writeFile($filePath, $data, $mode = 'a'){
        try{
            $writer = new Zend_Log_Writer_Stream($filePath,$mode);
            $formatter = new Zend_Log_Formatter_Simple('%message%');
            $writer->setFormatter($formatter);
            $logger = new Zend_Log($writer);
            $logger->info($data);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function readFile($filePath){
        try{
            $sessionFile = fopen($filePath, "r");
            $rawData =  fread($sessionFile, filesize($filePath));
            fclose($sessionFile);
            return $rawData;
        } catch (Exception $e) {

            return false;
        }
    }
}
