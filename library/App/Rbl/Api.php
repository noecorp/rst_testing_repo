<?php
class App_Rbl_Api {
	
	protected $array2xml;
	protected $validator;
	
    //public function __construct(App_Rbl_Array2xml $array2xml,App_Rbl_Validator $validator) {
	public function __construct() { 
	    $this->array2xml = new App_Rbl_Array2xml();
		$this->validator = new App_Rbl_Validator();
	}
	
	// default request sender to api
	private function _sendRequestToRbl($method,$xmlRequest) {
        App_Logger::log($xmlRequest, Zend_Log::INFO);
		$temp = $xmlRequest;
	   $url = 'http://10.80.45.46:1000/BCAPI/Apiservices.aspx';
	   //setting the curl parameters.
		 $headers = array(
			"Content-type: application/xml",
			"Accept: Application/xml",
			"Cache-Control: no-cache",
			"Pragma: no-cache",
		 );
	
		try{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_POSTFIELDS,  $xmlRequest);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_VERBOSE, 0);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			$data = curl_exec($ch);
			//App_Logger::log($data, Zend_Log::INFO);
			
			$data=preg_replace('/(<\?xml[^?]+?)utf-16/i', '$1utf-8', $data);
			$data = json_decode(json_encode(simplexml_load_string($data)), true);  
			App_Logger::log($data, Zend_Log::INFO);

			if(is_array($data)){
				if(isset($data['status']) && $data['status'] == 0 && isset($data['description'])){
					$sessionError='sessionerror';
					$reloginError='Your Session has been Expired.Please Relogin the Application';
					$tokenError = 'sessiontoken error';
					
					if(in_array($data['description'],array($sessionError,$reloginError,$tokenError))) {
						$user = Zend_Auth::getInstance()->getIdentity();
						//session - auto extend
						$session = new Zend_Session_Namespace('App.Agent.Controller');
						$this->doLogin($session,$user->bcagent);

						$newXmlRequestArr = new SimpleXMLElement($xmlRequest);
						$newXmlRequestArr->header->sessiontoken= $session->rblSessionID;
						$newXmlRequest = $newXmlRequestArr->asXML();
						
						App_Logger::log($newXmlRequest, Zend_Log::INFO);
						//resend the api request again
						curl_setopt($ch, CURLOPT_POSTFIELDS,  $newXmlRequest);
						
						$data = curl_exec($ch);
						$data=preg_replace('/(<\?xml[^?]+?)utf-16/i', '$1utf-8', $data);
						$data = json_decode(json_encode(simplexml_load_string($data)), true);
						App_Logger::log($data, Zend_Log::INFO);
					}
				}
			}
			curl_close($ch);
				
			return $data;
			
		}catch(Exception  $e){
			return $e->getMessage();
		}
		//return $this->_mockResponse($method,$data);
	}
	

	
	private function _mockResponse($method,$data) {
		
		$method = str_replace('App_Rbl_Api::','',$method);
		
		try {
			
			$this->validator->{$method}($this->_XmlToArray($data));
			
			$mockObject = new App_Rbl_MockApiResponse();
			
			return $mockObject->{$method}($data);
			
		} catch(Exception $e) {
			
			echo $e->getMessage();
			
		}
		
	}
	
	private function _prepareXML($root,$array) {
		$xmlRequest = '<?xml version="1.0" encoding="utf-8"?>';
		$xmlRequest .= '<'.$root.'>';
	 foreach($array as $key=>$value) {
		 if(is_array($value)) {
			  $xmlRequest .= '<'.$key.'>';
			 foreach($value as $k=>$v) {
				 $xmlRequest .= '<'.$k.'>'.$v.'</'.$k.'>';
			 }
			  $xmlRequest .= '</'.$key.'>';
			 
		 } else {
				$xmlRequest .= '<'.$key.'>'.$value.'</'.$key.'>';
		 }
		 
	 }
		$xmlRequest .='</'.$root.'>';
		return $xmlRequest;
	}
	
	private function _XmlToArray($data) {
		return (array)simplexml_load_string($data);
	}
	
	public function channelPartnerLogin($data) {
		
		  $apiPath = __METHOD__;	  
		  
		  $xml = $this->_prepareXML('channelpartnerloginreq',$data);
 
		  return $this->_sendRequestToRbl($apiPath,$xml);
		  
    }
	
	public function remitterRegistrationRemittance($data) {
		
		  $apiPath = __METHOD__;
		
		  $xml = $this->_prepareXML('remitterregistrationskipvalidationreq',$data);
		  App_Logger::log("REM-REG : API Request is : ". $xml, Zend_Log::INFO);
		  
		  $response= $this->_sendRequestToRbl($apiPath,$xml);
		  App_Logger::log("REM-REG : API Response is : ". $response, Zend_Log::INFO);
		  return $response;		  
	}
	
	public function remitterValidation($data) { 
		
		  $apiPath = __METHOD__;
		
		  $xml = $this->_prepareXML('remitterregistrationvalidatereq',$data);
		  
		  return $this->_sendRequestToRbl($apiPath,$xml);
		  
	}
	
	public function remitterResendOtp($data) { 
		
		  $apiPath = __METHOD__;
		
		  $xml = $this->_prepareXML('remitterregistrationresendotpreq',$data);
		  
		  return $this->_sendRequestToRbl($apiPath,$xml);
		  
	}
	
	public function remitterKycUpload($data) { 
		
		  $apiPath = __METHOD__;
		
		  $xml = $this->_prepareXML('remitterkycreq',$data);
		  
		  return $this->_sendRequestToRbl($apiPath,$xml);
		  
	}
	
	public function beneficiaryRegistration($data) {  
		
		  $apiPath = __METHOD__;
		  $xml = $this->_prepareXML('beneficiaryregistrationreq',$data);
		  return $this->_sendRequestToRbl($apiPath,$xml);
		  
	}
	
	public function beneficiaryValidation($data) {  
		
		  $apiPath = __METHOD__;
		  $xml = $this->_prepareXML('beneficiaryregistrationvalidatereq',$data);
		  return $this->_sendRequestToRbl($apiPath,$xml);
		  
	}
	
	public function beneficiaryUpdationRemittance($data) {  
		
		  $apiPath = __METHOD__;
		
		  $xml = $this->_prepareXML('beneficiaryregistrationupdrmreq',$data);
		  
		  return $this->_sendRequestToRbl($apiPath,$xml);
		  
	}
	
	public function beneficiaryUpdationImps($data) {  
		
		  $apiPath = __METHOD__;
		
		  $xml = $this->_prepareXML('beneficiaryregistrationupdimpsreq',$data);
		  
		  return $this->_sendRequestToRbl($apiPath,$xml);
		  
	}
	
	public function beneficiaryResendOtp($data) { 
		
		  $apiPath = __METHOD__;
		  $xml = $this->_prepareXML('beneficiaryresendotpreq',$data);
		  
		  return $this->_sendRequestToRbl($apiPath,$xml);
		  
	}
	
	public function transaction($data) {  
		
		  $apiPath = __METHOD__;
		  $xml = $this->_prepareXML('transactionreq',$data);
		  return $this->_sendRequestToRbl($apiPath,$xml);
		  
	}
	
	public function refundOtp($data) {  
		
		  $apiPath = __METHOD__;
		
		  $xml = $this->_prepareXML('refundotpreq',$data);
		  
		  return $this->_sendRequestToRbl($apiPath,$xml);
		  
	}
	
	public function refund($data) {  
		
		  $apiPath = __METHOD__;
		
		  $xml = $this->_prepareXML('refundreq',$data);
		  
		  return $this->_sendRequestToRbl($apiPath,$xml);
		  
	}
	
	public function remitterDetails($data) {  
		
		  $apiPath = __METHOD__;
		
		  $xml = $this->_prepareXML('remitterdetailsreq',$data);
		  
		  return $this->_sendRequestToRbl($apiPath,$xml);
		  
	}
	
	public function transactionDetatils($data) { 	
		
		  $apiPath = __METHOD__;
		
		  $xml = $this->_prepareXML('transactiondetailsreq',$data);
		  
		  return $this->_sendRequestToRbl($apiPath,$xml);
		  
	}
	
	public function remitterRegistrationPrint($data) { 	
		
		  $apiPath = __METHOD__;
		
		  $xml = $this->_prepareXML('remitterregistrationprintreq',$data);
		  
		  return $this->_sendRequestToRbl($apiPath,$xml);
		  
	}
	
	public function remitterReciverRegistrationPrint($data) { 
		
		  $apiPath = __METHOD__;
		
		  $xml = $this->_prepareXML('remitterrecieverregistrationprintreq',$data);
		  
		  return $this->_sendRequestToRbl($apiPath,$xml);
		  
	}
	
	public function transactionPrint($data) {  
		
		  $apiPath = __METHOD__;
		
		  $xml = $this->_prepareXML('transactionprintreq',$data);
		  
		  return $this->_sendRequestToRbl($apiPath,$xml);
		  
	}
	
	public function deleteBeneficiary($data) { 
		
		  $apiPath = __METHOD__;
		
		  $xml = $this->_prepareXML('beneficairydeletereq',$data);
		  
		  return $this->_sendRequestToRbl($apiPath,$xml);
		  
	}
	
	public function deleteBeneficiaryValidation ($data) { 	
		
		  $apiPath = __METHOD__;
		
		  $xml = $this->_prepareXML('beneficairydeletevalidationreq',$data);
		  
		  return $this->_sendRequestToRbl($apiPath,$xml);
		  
	}
	
	public function transactionReQuery($data) { 	
		
		  $apiPath = __METHOD__;
		
		  $xml = $this->_prepareXML('transactionrequeryreq',$data);
		  
		  return $this->_sendRequestToRbl($apiPath,$xml);
		  
	}
	
	public function remitterKycUploadFee($data) { 	
		
		  $apiPath = __METHOD__;
		
		  $xml = $this->_prepareXML('remitterkycuploadfeereq',$data);
		  
		  return $this->_sendRequestToRbl($apiPath,$xml);
		  
	}
	
	public function indemnityStatusForBcagent($data) { 
		
		  $apiPath = __METHOD__;
		
		  $xml = $this->_prepareXML('lostmobilegetbcagentstatusreq',$data);
		  
		  return $this->_sendRequestToRbl($apiPath,$xml);
		  
	}
	
	public function uploadIndemnityForm($data) { 
		
		  $apiPath = __METHOD__;
		
		  $xml = $this->_prepareXML('uploadindemnityreq',$data);
		  
		  return $this->_sendRequestToRbl($apiPath,$xml);
		  
	}
	
	public function uploadRemitterDocument($data) { 	
		
		  $apiPath = __METHOD__;
		
		  $xml = $this->_prepareXML('uploadremitterdocumentreq',$data);
		  
		  return $this->_sendRequestToRbl($apiPath,$xml);
		  
	}
	
	public function indemnityHistory($data) { 	
		
		  $apiPath = __METHOD__;
		
		  $xml = $this->_prepareXML('indemnityhistoryreq',$data);
		  
		  return $this->_sendRequestToRbl($apiPath,$xml);
		  
	}
	
	public function getServiceCharge($data) { 
		
		  $apiPath = __METHOD__;
		
		  $xml = $this->_prepareXML('getservicechargereq',$data);
		  
		  return $this->_sendRequestToRbl($apiPath,$xml);
		  
	}
	
	public function commonError($data) { 
		
		  $apiPath = __METHOD__;
		
		  $xml = $this->_prepareXML('remitterregistrationrmreq',$data);
		  
		  return $this->_sendRequestToRbl($apiPath,$xml);
		  
	}
	
	private function doLogin($session, $bcagent){
		error_log('Doing relogin automcatically as session expired by RBL');
		$rbiResponse = $this->channelPartnerLogin(array('username' => RBL_CHANNEL_PARTNER_LOGIN_USERNAME,
				'password' => RBL_CHANNEL_PARTNER_LOGIN_PASSWORD,
				'bcagent' => $bcagent));
			
		if(isset($rbiResponse['status']) && $rbiResponse['status']) {
			$session->rblSessionID = $rbiResponse['sessiontoken'];
		}
	}
	
}
