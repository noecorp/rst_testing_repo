<?php
class App_Rbl_Validator {
	
	
    public function __construct() {
	}
	
	// check data $array
	private function _inputCheck($data,$defaults) {

		if(count(array_diff_key($defaults,$data)) !== 0) {
			
			throw new Exception('invalid input.');
			
		}
		
		return true;
	}
	
	// common validator
	private function _validate($data,$defaults) {
		
		return $this->_inputCheck($data,$defaults);
		
	}
	
	public function channelPartnerLogin($data) {
		
		$defaults = array_flip(array('username','password','bcagent'));
		
		return $this->_validate($data,$defaults);
		
    }
	
	public function remitterRegistrationRemittance($data) {return true;}
	
	public function remitterValidation($data) {return true;}
	
	public function remitterResendOtp($data) {return true;}
	
	public function remitterKycUpload($data) {return true;}
	
	public function beneficiaryRegistration() {return true;}
	
	public function beneficiaryValidation() {return true;}
	
	public function beneficiaryUpdationRemittance() {return true;}
	
	public function beneficiaryUpdationImps() {return true;}
	
	public function beneficiaryResendOtp() {return true;}
	
	public function transaction() {return true;}
	
	public function refundOtp() {return true;}
	
	public function refund() {return true;}
	
	public function remitterDetails() {return true;}
	
	public function transactionDetatils() {return true;}
	
	public function remitterRegistrationPrint() {return true;}
	
	public function remitterReciverRegistrationPrint() {return true;}
	
	public function transactionPrint() {return true;}
	
	public function deleteBeneficiary() {return true;}
	
	public function deleteBeneficiaryValidation () {return true;}
	
	public function transactionReQuery() {return true;}
	
	public function remitterKycUploadFee() {return true;}
	
	public function indemnityStatusForBcagent() {return true;}
	
	public function uploadIndemnityForm() {return true;}
	
	public function uploadRemitterDocument() {return true;}
	
	public function indemnityHistory() {return true;}
	
	public function getServiceCharge() {return true; }
	
	public function commonError() { return true; }
	
}