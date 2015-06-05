<?php

class App_ApiServer_MVC 
{


    /**
     * 
     * @param string $_userId
     * @param string $_password
     * @param string $_channel
     * @return string
     */
  public function validateSession($_userId, $_password, $_channel) 
  {
//      $msg = __CLASS__ . " : " . __METHOD__ . " TEST";
//      App_Logger::log($msg,  Zend_Log::INFO);//exit;
      return 'SESSIONKEY123213123';
  }
  
  
  /**
   * 
   * @param string $userName
   * @param string $password
   * @return array
   */
  public function Logon($userName, $password) 
  {
        $sessionID = 'SESSIONKEY123213123';
        $msg = "MVC Creating new Connection with session id:" . $sessionID;
        App_Logger::log($msg,  Zend_Log::INFO);          
      return array(
          'SessionID' => $sessionID,
          'ReplyCode' => '0',
          'ReplyMessage' => 'Successful',
      );
  }
  
  /**
   * 
   * @param string $_userId
   * @param string $_password
   * @param string $_channel
   * @return string
   */
  public function getResponseCode($_userId, $_password, $_channel)
  {
        $sessionID = 'SESSIONKEY123213123';
        $msg = "MVC Creating new Connection with session id:" . $SessionID;
        App_Logger::log($msg,  Zend_Log::INFO);    
        return $session;
  }
  
  /**
   * Send download link to mobile
   * @param string $SessionID
   * @param string $MobileNumber
   * @return array Description
   */
  public function DownloadLink($SessionID, $MobileNumber)
  {
      $msg = "Sending download link to " . $MobileNumber . " with session id:" . $SessionID;
      App_Logger::log($msg,  Zend_Log::INFO);//exit;
      return array(
          'SessionID' => $SessionID,
          'ResponseCode' => '0',
          'ResponseMessage' => 'Successful'
      );
  }
  
  /**
   * The purpose of the keep‐alive message is to keep the connection alive and prevent 
   * network‐level or applicationlevel timeouts and closure of the connection due to inactivity.
   * @param string $SessionID
   * @param string $DateTime
   * @return string
   */
  public function KeepAlive($SessionID,$DateTime)
  {
      $msg = "Checking Connection with session id:" . $SessionID;
      App_Logger::log($msg,  Zend_Log::INFO);      
      return array(
          'DateTime' => date('Y-m-d h:m:s')
      );
  }
  
  /**
   * 
   * @param string $SessionID
   * @param string $CAFNumber
   * @param string $FirstName
   * @param string $LastName
   * @param string $MobileNumber
   * @param string $DeviceID
   * @param string $crn
   * @param string $AccountAlias
   * @param string $CustommerType
   * @return array
   */
  public function Registration($SessionID, $CAFNumber, $FirstName, $LastName, $MobileNumber, $DeviceID, $crn, $AccountAlias, $CustommerType)
  {
       
       $msg = "Registring Cardholder with MVC Session id: $sessionID  CAFNumber : $CAFNumber  FirstName : $FirstName LastName : $LastName MobileNumber : $MobileNumber DeviceID : $DeviceID crn : $crn AccountAlias : $AccountAlias  CustommerType : $CustommerType";
       App_Logger::log($msg,  Zend_Log::INFO);            
      return array(
          'SessionID'       => $SessionID,
          'ResponseCode'    => 0,
          'ResponseMessage' => 'Successful'
      );
  }

}
