<?php
class App_Rbl_MockApiResponse {
	
	
    public function __construct() {
	}
	
	
	
	public function channelPartnerLogin() {
		
			return array(
				'sessiontoken' => 'sessiontoken',
				'timeout' => 'timeout',
				'status' => 'status',
			);
		
    }
	
	public function remitterRegistrationRemittance($data) {
		
			return array(
				'bcagent' => 'bcagent',
				'remitterid' => '12345678',
				'status' => 'status'
			);
				  
	}
	
	public function remitterValidation($data) { 
		
			return array(
				'remitterid' => 'remitterid',
				'status' => 'status',
				'Enrollmentfee' => 'Enrollmentfee',
			);
				  
	}
	
	public function remitterResendOtp($data) { 
		
			return array(
				'status' => 'status',
			);	
				  
	}
	
	public function remitterKycUpload($data) { 
		
			return array(
				'status' => 'status',
			);	
				  
	}
	
	public function beneficiaryRegistration() { 
		
			return array(
				'bcagent' => 'bcagent',
				'remitterid' => '12345678',
				'beneficiaryid' => '123456789',
				'status' => 'status',
			);	
				  
	}
	
	public function beneficiaryValidation() { 
		
			return array(
				'beneficiaryid' => '123456789',
				'status' => '1',
			);	
				  
	}
	
	public function beneficiaryUpdationRemittance() { 
		
			return array(
				'bcagent' => 'bcagent',
				'remitterid' => 'remitterid',
				'beneficiaryid' => 'beneficiaryid',
				'status' => 'status',
			);	
				  
	}
	
	public function beneficiaryUpdationImps() { 
		
			return array(
				'bcagent' => 'bcagent',
				'remitterid' => 'remitterid',
				'beneficiaryid' => 'beneficiaryid',
				'status' => 'status',
			);	
				  
	}
	
	public function beneficiaryResendOtp() {
		
			return array(
				'status' => 'status',
			);	
				  
	}
	
	public function transaction() { 
		
			return array(
				'channelpartnerrefno' => 'channelpartnerrefno',
				'RBLtransactionid' => '123456',
				'status' => '1',
				'amount' => 'amount',            
				'servicecharge' => 'servicecharge',
				'grossamount' => 'grossamount',
				'kycstatus' => 'kycstatus', 
				'remarks' => 'remarks',
				'bankrefno' => 'bankrefno',
				'NPCIResponsecode' => 'NPCIResponsecode',
			);	
				  
	}
	
	public function refundOtp() { 
		
			return array(
				 'status' => 'status',
			);	
				  
	}
	
	public function refund() { 
		
			return array(
				'bcagent' => 'bcagent',
				'amount' => 'amount',
				'tamount' => 'tamount',
				'servicecharge' => 'servicecharge',
				'status' => 'status',
			);	
				  
	}
	
	public function remitterDetails() { 
		
			return array(
					'bcagent' => 'bcagent',
					'status' => 'status',
					array('remitterdetail' => array(
									'remitterid' => 'remitterid',
									'remittername' => 'remittername',
									'remitteraddress1' => 'remitteraddress1',
									'remitteraddress2' => 'remitteraddress2',
									'pincode' => 'pincode',
									'city' => 'city',
									'state' => 'state',
									'alternatenumber' => 'alternatenumber',
									'idproof' => 'idproof',
									'idproofnumber' => 'idproofnumber',
									'idproofissuedate' => 'idproofissuedate',
									'idproofexpirydate' => 'idproofexpirydate',
									'idproofissueplace' => 'idproofissueplace',
									'laddress' => 'laddress',
									'lpincode' => 'lpincode',
									'lcity' => 'lcity',
									'lstate' => 'lstate',
									'remitterstatus' => 'remitterstatus',
									'kycstatus' => 'kycstatus'),
						'beneficiarydetail' => array(
								'beneficiary' => array(
									'beneficiaryid' => 'beneficiaryid',  
									'beneficiaryname' => 'beneficiaryname',
									'beneficiarymobilenumber' => 'beneficiarymobilenumber',
									'beneficiaryemailid' => 'beneficiaryemailid',
									'relationshipid' => 'relationshipid',
									'bank' => 'bank',
									'state' => 'state',
									'city' => 'city',
									'branch' => 'branch',
									'address' => 'address',
									'ifscode' => 'ifscode',
									'accountnumber' => 'accountnumber',
									'mmid' => 'mmid',
									'beneficiarystatus' => 'beneficiarystatus',
									'impsstatus' => 'impsstatus'
							) 
						),
					)			
				);	
				  
	}
	
	public function transactionDetatils() {	
		
			return array(
				array('transaction' => 'transaction' ,
					'RBLtransactionid' => 'RBLtransactionid',
					'transdate' => 'transdate',
					'remittername' => 'remittername',
					'remittermblordfid' => 'remittermblordfid',
					'beneficiaryname' => 'beneficiaryname',
					'beneficiarymblno' => 'beneficiarymblno',
					'ifsccode' => 'ifsccode',
					'accountno' => 'accountno',
					'mmid' => 'mmid',
					'tamount' => 'tamount',
					'transstatus' => 'transstatus',
					'status' => 'status',)
				);
	}
	
	public function remitterRegistrationPrint() {	
		
			return array(
				'bcagentname' => 'bcagentname',
				'remitterid' => 'remitterid',
				'remittername' => 'remittername',
				'remittermobilenumber ' => 'remittermobilenumber ',
				'remitteraddress' => 'remitteraddress',
				'state' => 'state',
				'city' => 'city',
				'pincode' => 'pincode',
				'laddress' => 'laddress',
				'lpincode' => 'lpincode',
				'lcity' => 'lcity',
				'lstate' => 'lstate',
				'remitterregdt' => 'remitterregdt',
				'status' => 'status',
			);	
				  
	}
	
	public function remitterReciverRegistrationPrint() {
		
			return array(
				'bcagent' => 'bcagent',
				'bcagentname' => 'bcagentname',
				'remitterid' => 'remitterid',
				'remittername' => 'remittername',
				'remittermobilenumber ' => 'remittermobilenumber ',
				'remitteraddress1' => 'remitteraddress1',
				'remitteraddress2' => 'remitteraddress2',
				'beneficiaryname' => 'beneficiaryname',
				'relationship ' => 'relationship ',
				'relationshiptype' => 'relationshiptype',
				'bank' => 'bank',
				'ifscode' => 'ifscode',
				'accountnumber' => 'accountnumber',
				'branch' => 'branch',
				'status' => 'status',
			);	
				  
	}
	
	public function transactionPrint() { 
		
			return array(
				'bcagent' => 'bcagent',
				'bcagentname' => 'bcagentname',
				'transactiondt' => 'transactiondt',
				'transactionid' => 'transactionid',
				'amount' => 'amount',
				'servicechrg' => 'servicechrg',
				'tamount' => 'tamount',
				'paymentstatus' => 'paymentstatus',
				'remittername' => 'remittername',
				'remittermblno' => 'remittermblno',
				'beneficiaryname' => 'beneficiaryname',
				'relationship' => 'relationship',
				'relationshiptype' => 'relationshiptype',
				'bank' => 'bank',
				'ifsccode' => 'ifsccode',
				'accountnumber' => 'accountnumber',
				'branch' => 'branch',
				'address1' => 'address1',
				'address2' => 'address2',
				'idproof' => 'idproof',
				'idproofnumber' => 'idproofnumber',
				'idproofissuedate' => 'idproofissuedate',
				'idproofexpdate' => 'idproofexpdate',
				'status' => 'status',
			);	
				  
	}
	
	public function deleteBeneficiary() {
		
			return array(
				  'status' => 'status',
			   'description' => 'description',  
			);	
				  
	}
	
	public function deleteBeneficiaryValidation () {	
		
			return array(
				'beneficiaryid' => 'beneficiaryid',
				'status' => 'status',
			);	
				  
	}
	
	public function transactionReQuery() {	
		
			return array(
				'bcagent' => 'bcagent',
				'bcagentname' => 'bcagentname',
				'transactiondt' => 'transactiondt',
				'transactionid' => 'transactionid',
				'amount' => 'amount',
				'servicechrg' => 'servicechrg',
				'tamount' => 'tamount',
				'paymentstatus' => 'paymentstatus',
				'remittername' => 'remittername',
				'remittermblno' => 'remittermblno',
				'beneficiaryname' => 'beneficiaryname',
				'relationship' => 'relationship',
				'relationshiptype' => 'relationshiptype',
				'bank' => 'bank',
				'ifsccode' => 'ifsccode',
				'accountnumber' => 'accountnumber',
				'branch' => 'branch',
				'address1' => 'address1',
				'address2' => 'address2',
				'idproof' => 'idproof',
				'idproofnumber' => 'idproofnumber',
				'idproofissuedate' => 'idproofissuedate',
				'idproofexpdate' => 'idproofexpdate',
				'status' => 'status',
			);	
				  
	}
	
	public function remitterKycUploadFee() {	
		
			return array(
			   'status' => 'status',
			   'enrollmentfee' => 'enrollmentfee',  
			);	
				  
	}
	
	public function indemnityStatusForBcagent() {
		
			return array(
				 'status' => 'status',
			);	
				  
	}
	
	public function uploadIndemnityForm() {
		
			return array(
			  'status' => 'status',
			);	
				  
	}
	
	public function uploadRemitterDocument() {	
		
			return array(
				 'status' => 'status',
			);	
				  
	}
	
	public function indemnityHistory() {	
		
			return array(
			   'status' => 'status',
			   'indemnity' =>  array(
					'oldmobilenumber ' => ' oldmobilenumber ',
					'newmobilenumber' => 'newmobilenumber',
					'indemnitystatus ' => ' indemnitystatus ',
					'uploadeddate ' => 'uploadeddate '
				)
			);	
				  
	}
	
	public function getServiceCharge() {
		
			return array(
				'servicecharge' => 'servicecharge',
			);	
				  
	}
	
	public function commonError() {
		
			return array(
				'status' => 'status',
			   'description' => 'description',  
			);	
				  
	}
	
}