<?php

class App_ApiServer_ECS
{
    
    public function __construct() {

    }

  /**
   * Method to validate session
   * @param string $userId
   * @param int $password
   * @param string $_channel
   * @return string
   */
  public function validateSession($userId, $password, $_channel) 
  {
	return 'SESSIONKEY123213123';
  }
  
  /**
   * Method to validate session
   * @param string $userId
   * @param int $password
   * @param string $_channel
   * @return string
   */  
  public function getResponseCode($_userId, $_password, $_channel)
  {
	//return ' 000 UserId: ' . $_userId .' Channel: '. $_channel .' Password: '. $_password . ' <br />' ;
        $message = 'UserID: '.$_userId . " Password : ".$_password . " Channel : ".$_channel . ' YAHOOO FINALLYYY';
        App_Logger::log($message,  Zend_Log::INFO);  
	return 'SESSIONKEY123213123';
  }
  
/**
 * 
 * @param array $array
 * @return string
 */
  public function CustomerRegistration(array $array)
  {
	//return ' 000 UserId: ' . $_userId .' Channel: '. $_channel .' Password: '. $_password . ' <br />' ;
      return '000';
	return array(
            'AuthNumber' =>'100',
            'AuthNumber2' =>'200',
            'AuthNumber3' =>'300',
        );
  }
  public function CardLoad($array)
  {
	//return ' 000 UserId: ' . $_userId .' Channel: '. $_channel .' Password: '. $_password . ' <br />' ;
      //return '000';
	return array(
            'ResponseCode' =>'000',
            'ErrorCode' =>'000',
            'ErrorDescription' =>'',
            'CardPackId' =>'AEDP0100000001',
            'UniqueRefNumber' =>'5295461000039411',
        );
  }
  
  public function writelog($message)
  {
            //$msg = __CLASS__ . " : " . __METHOD__ .'***************Welcome - Step 2**********';
            App_Logger::log($message,  Zend_Log::INFO);  
  }
  
  /**
   * 
   * @param string $ID
   * @param string $SessionKey
   * @param string $Channel
   * @param string $CardNumber
   * @param string $PassCodeFlag
   * @param string $PassCode
   * @return array
   */
  public function BalanceInquiry($ID, $SessionKey, $Channel, $CardNumber, $PassCodeFlag, $PassCode)
  {
      return array(
          'Balance' => '1000'
      );
  }
}

