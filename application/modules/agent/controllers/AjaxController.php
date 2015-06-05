<?php
/**
 * Default entry point in the application
 *
 * @package frontend_controllers
 * @copyright company
 */

class AjaxController extends App_Agent_Controller
{
    /**
     * Overrides Zend_Controller_Action::init()
     *
     * @access public
     * @return void
     */
    public function init()
    {
        
        // init the parent
        parent::init();
        
        //$this->_addCommand(new App_Command_SendEmail());
        $this->_helper->layout()->setLayout('ajax');
        //$this->_helper->layout()->setViewRender('ajax');
    }
    
    public function mobiledupAction()
    {
        
        $mobile = $this->_getParam("q",0);
        $tablename = $this->_getParam("tablename",'');
        
        if(strlen($mobile) == Mobile::$length) {
             
            //Checking Validation
            $mobileModel = new Mobile();
            
            try {
                
                print $mobileModel->checkDuplicate($mobile, $tablename);
                
            } catch (Exception $e ) {
                //var_dump($e);exit;
               // print $e->getMessage();
                print 'Mobile number exists';
                
            }
            
        } else {
            print 'Invalid Mobile Number';
        }
        
        exit;
        
    }
    
    
    public function getCityAction()
    {
        
        $stateCode = $this->_getParam("q",0);
        //$arrCity =  Util::getCity($state);
        $citylist = new CityList();
        $arrCity = $citylist->getCityByStateCode($stateCode);
        $strReturn = '<option value="">Select City</option>';
        foreach ($arrCity as $city)
        {
            $strReturn .= '<option value="'.$city.'">'.$city.'</option>';
        }
        print $strReturn;

        
        exit;
        
    }
    
    public function getPincodeAction()
    {
        
        $cityName = $this->_getParam("q",0);
        //$cityCode = $this->_getParam("q",0);
        //$arrCity =  Util::getCity($state);
        $citylist = new CityList();
        $cityCode = $citylist->getCityCode($cityName);
        $arrCity = $citylist->getPincodeList($cityCode);
        $strReturn = '<option value="">Select Pincode</option>';
        foreach ($arrCity as $pincode)
        {
            $strReturn .= '<option value="'.$pincode.'">'.$pincode.'</option>';
        }
        print $strReturn;

        
        exit;
        
    }
    
    
     
    public function emaildupAction()
    {        
        $email = $this->_getParam("q",'');
        $tablename = $this->_getParam("tablename",'');
        
        if(strlen($email) < Email::$minLength || strlen($email) > Email::$maxLength) {
             
             print 'Invalid Email Address';           
             exit;
            
        } else {
                
                //Checking Validation
                $emailModel = new Email();

                try {

                    print $emailModel->checkDuplicate($email, $tablename);
                    exit;
                } catch (Exception $e ) {
                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    print $e->getMessage();
                    exit;
                }
        }       
    }
    
    
    public function sendDownloadLinkAction()
    {
        
        
        
        $mob = $this->_getParam("mob",'');
        if($mob== '') {
            print 'Mobile number not provided';
            exit;
        }
      
           try {
               //Handling missing country code
               if(strpos($mob, '+') === false) {
                 $mob = '+'.trim($mob);
               }
               //exit($mob);
        $mvc = new App_Api_MVC_Transactions();
        $flg = $mvc->sendDownloadLink($mob);
        } catch (Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            //print $e->getMessage();exit;
        }
        //echo "<pre>";print_r($flg);
        if($flg) {
            echo "<br />Download link sent successfully<br /><br />";
        } else {
            print 'ERROR:' .$mvc->getError() . '<br />';
            echo "<br />Download link could not be sent<br />";

        }

            //echo $ecs->getError();

        
        $this->_helper->viewRenderer->setNoRender(true);
        //$this->view->render(false);
        //$viewRenderer = Zend_Controller_Action_HelperBroker::getExistingHelper('viewRenderer');
        //$viewRenderer->setNeverRender(true);
        
        exit;
        
        //$this->_helper->layout()->disableLayout();

    }
    
    
     public function arndupAction(){
        
        $arn = $this->_getParam("q",0);
        $tableName = $this->_getParam("tablename",'');         
             
        //Checking Validation
        $objValid = new Validator();
            
            try {                
                print $objValid->checkARNDuplicate(array('tablename'=>$tableName, 'arn'=>$arn));                
                exit;
                
            } catch (Exception $e ) {
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                print $e->getMessage();
                exit;
            }
      }
   public function resendAuthcodeAction(){
        $user = Zend_Auth::getInstance()->getIdentity();
        $userModel = new AgentUser();
        $dataArr = $userModel->findDetails($user->agent_code,DbTable::TABLE_AGENTS);
        print_r($dataArr);
        exit;
        $alert = new Alerts();
        $alert->sendAuthCode($dataArr,'agent');
        echo 'Mail Sent';
        exit;

    }
    
     public function getIfscAction(){
        $bank = $this->_getParam("q",0);
        $ifsc = $this->_getParam("i",0);
        $type = $this->_getParam("t","");
      //$arrCity =  Util::getCity($state);
      	if(empty($bank)){
      		echo $strReturn = '<option value="">Select IFSC Code</option>';
      		exit;
      	}
        $ifsclist = new BanksIFSC();
			  $arrIFSC = $ifsclist->getIFSC($bank,$ifsc,$type);
        $strReturn = '<option value="">Select IFSC Code</option>';
        foreach ($arrIFSC as $ifsc)
        {
            $strReturn .= '<option value="'.$ifsc.'">'.$ifsc.'</option>';
        }
        print $strReturn;

        
        exit;
    }
    
    public function getBankdetailsAction(){
        $ifsc = $this->_getParam("q",0);  
        $type = $this->_getParam("t","");
      //$arrCity =  Util::getCity($state);
        $details = new BanksIFSC();
       
        $arrDetails = $details->getDetailsByIFSC($ifsc,$type);
        if($arrDetails=="^^^^"){
        	echo "1"; exit;
        }
        print $arrDetails;

        
        exit;
    }
    /* getRemitterRegistrationFeeAction() will return the remittance fee againt agent product assigned
     * it will accept the product id 
     */
     public function getRemitterRegistrationFeeAction(){
        $productId = $this->_getParam("q",0);  
        $agentId = $this->_getParam("agent_id",0);  
        //print $productId.'---'.$agentId; exit;
      
      if($productId>0 && $agentId>0){
            $objFeePlan = new FeePlan();
            $arrDetails = $objFeePlan->getRemitterRegistrationFee($productId, $agentId);
            $fee = isset($arrDetails['txn_flat'])?$arrDetails['txn_flat']:0;
            print $fee;
            exit;
      }
    }
    public function getStateAction(){
        $bank = $this->_getParam("q",0);
        //$arrCity =  Util::getCity($state);
        $ifsclist = new BanksIFSC();
			  $arrIFSC = $ifsclist->getStateByName($bank);
        $strReturn = '<option value="">Select State</option>';
        foreach ($arrIFSC as $state)
        {
            $strReturn .= '<option value="'.$state.'">'.$state.'</option>';
        }
        print $strReturn;

        
        exit;
    }
    
    public function getCitiesAction(){
        $bank = $this->_getParam("q",0);
        $state = $this->_getParam("r",0);  
				$ifsclist = new BanksIFSC();
				$arrCity = $ifsclist->getCityByName($bank,$state);
			  $strReturn = '<option value="">Select City</option>';
        foreach ($arrCity as $city)
        {
            $strReturn .= '<option value="'.$city.'">'.$city.'</option>';
        }
        print $strReturn;

        
        exit;
       
    }
    public function getBranchesAction(){
        $bank = $this->_getParam("q",0);
        $city = $this->_getParam("i",0);  
        $BanksIFSC = new BanksIFSC();
				$arrBranches = $BanksIFSC->getBranchesByName($bank,$city);
			  $strReturn = '<option value="">Select Branch</option>';
        foreach ($arrBranches as $branch)
        {
            $strReturn .= '<option value="'.$branch.'">'.$branch.'</option>';
        }
        print $strReturn;

        
        exit;
    }
    public function getBranchaddAction(){
        $bank = $this->_getParam("q",0);
        $branch = $this->_getParam("i",0);  
	$type = $this->_getParam("t",'');  	
        $details = new BanksIFSC();
       
        $arrDetails = $details->getBranchAddress($bank,$branch,$type);
        
        print $arrDetails;

        
        exit;
    }
     public function getbasicIfscAction(){
        $bank = $this->_getParam("q",0);  
      
      //$arrCity =  Util::getCity($state);
        $ifsclist = new BanksIFSC();
       
        $arrIFSC = $ifsclist->getIFSC($bank);
        $strReturn = '<option value="">Select IFSC</option>';
        foreach ($arrIFSC as $ifsc)
        {
            $strReturn .= '<option value="'.$ifsc.'">'.$ifsc.'</option>';
        }
        print $strReturn;

        
        exit;
    }
    
    public function getbasicBankdetailsAction(){
        $ifsc = $this->_getParam("q",0);  
      
      //$arrCity =  Util::getCity($state);
        $details = new BanksIFSC();
       
        $arrDetails = $details->getDetailsByIFSC($ifsc);
        
        print $arrDetails;

        
        exit;
    }
    
    public function getStatecityAction(){
        $pinCode = $this->_getParam("q",0);  
      
      	$citylist = new CityList();
        $strReturn = $citylist->getCityByPincode($pinCode);
        if($strReturn==""){
        	echo "1"; exit;
        }
        echo $strReturn;
        exit;
    }   
       public function getAgentsunderdistAction()
    {
        $objAU = new Agents();
        $str = STATUS_UNBLOCKED."', '".STATUS_BLOCKED."', '".STATUS_LOCKED;
        $agentsArr = $objAU->getBCListUnderDistributor(array('status'=> $str, 'enroll_status'=>ENROLL_APPROVED_STATUS, 'product_id' => $this->_getParam("p",FALSE),'agent_id' => $this->_getParam("q",FALSE) ));
      
        $agentsArr = Util::toArray($agentsArr);
        $strReturn = '<option value="">Select BC</option><option value="all">All</option>'; 
        foreach ($agentsArr as $key )
        {
            $strReturn .= '<option value="'.$key['id'].'">'.$key['agent_name'].'</option>';
        }
        print $strReturn;

        exit;
        
    }
    
    public function getFundtransferAction()
    {
        $user = Zend_Auth::getInstance()->getIdentity();  
        $agentId = $this->_getParam("aid",0);
		$flag = $this->_getParam("flag",0);
$session = new Zend_Session_Namespace("App.Agent.Controller");
    
        if($user->id !=$agentId){
          
            echo "Invalid details provided"; exit;
        }
        $beniId = $this->_getParam("bid",0);
  if(intval($beniId) != $beniId) {
            echo "Invalid details provided"; exit;            
        }        if(intval($amount) != $amount) {
            echo "Invalid details provided"; exit;            
        }           $amount = $this->_getParam("amount",0);
	if($beniId == 0){
		$beniId=$session->bid;
	}
        $remittancerequest = new Remit_Ratnakar_Remittancerequest();
        $msg = $remittancerequest->instatntTransfer(array('amount'=>$amount,'beneID'=>$beniId,'flag' => $flag));
        echo $msg;
 
        exit;
    }
    
    public function getUniversalbanksAction(){
        $bank = $this->_getParam("q",0);
        $ifsclist = new BanksIFSC();
	$strReturn = $ifsclist->getUniverSalBankDetails($bank);
        if($strReturn=="^^^^^"){
            $strReturn="";
            $arrIFSC = $ifsclist->getStateByName($bank);
            $strReturn = '<option value="">Select State</option>';
            foreach ($arrIFSC as $state)
            {
                $strReturn .= '<option value="'.$state.'">'.$state.'</option>';
            }
        }
        
        print $strReturn;

        
        exit;
    }
	
	public function getRblotpvalidateAction() {
		$otp = $this->_getParam("otp",0);
		$id = $this->_getParam("id",0);
		$rblApiObject = new App_Rbl_Api();
		 $remitters = new Remit_Ratnakar_Remitter();
		$beneficiary = new Remit_Ratnakar_Beneficiary();
		$beneficiariesList = $beneficiary->getBeneficiaryDetails($id);
		$session = new Zend_Session_Namespace("App.Agent.Controller");
		$remitter_data = $remitters->getRemitterById($beneficiariesList['remitter_id']); 
		if(!$otp) {
			$response = $rblApiObject->beneficiaryResendOtp(array('header' => array('sessiontoken' => $session->rblSessionID),
			'remitterid' => $remitter_data['remitterid'], 
			'beneficiaryid' => $beneficiariesList['beneficiary_id']));
			if($response['status']) {
				echo 0;
			}
		} else {
			$data = array('header' => array('sessiontoken' => $session->rblSessionID), 'beneficiaryid' => $beneficiariesList['beneficiary_id'],'verficationcode' => $otp);
			$response = $rblApiObject->beneficiaryValidation($data);
			if($response['status']) {
				$beneficiary->updateBeneficiaryDetails(array('rat_status' => 1,'status' => STATUS_ACTIVE),$id);
				echo $id;
			} else {
				$response = $rblApiObject->beneficiaryResendOtp(array('header' => array('sessiontoken' => $session->rblSessionID),
			'remitterid' => $remitter_data['remitterid'], 
			'beneficiaryid' => $beneficiariesList['beneficiary_id']));
			if($response['status']) {
				echo 0;
			}
			}
		}
		exit;
		
	}
    public function getRblregistercheckAction(){
        $id = $this->_getParam("id",0);
		$beneficiary = new Remit_Ratnakar_Beneficiary();
        $remitters = new Remit_Ratnakar_Remitter();
		$beneficiariesList = $beneficiary->getBeneficiaryDetails($id);
		$user = Zend_Auth::getInstance()->getIdentity();
		$session = new Zend_Session_Namespace("App.Agent.Controller");
		$remitter_data = $remitters->getRemitterById($beneficiariesList['remitter_id']);
		$rblApiObject = new App_Rbl_Api();
		
		error_log('In Ajax');
		error_log($beneficiariesList['remitter_id']);
		error_log($remitter_data['remitterid']);
		if(!$beneficiariesList['beneficiary_id']) {
			
			$data = array('header' => array('sessiontoken' => $session->rblSessionID),
		//								'bcagent' => 'TRA1000189',
										'bcagent' => $user->bcagent,
										'remitterid' =>$remitter_data['remitterid'],
										'beneficiaryname' => $beneficiariesList['name'],
										'beneficiarymobilenumber' => $beneficiariesList['mobile'],
										'beneficiaryemailid' => $beneficiariesList['email'],
										'relationshipid' => 2,
										'ifscode' => $beneficiariesList['ifsc_code'],
										'accountnumber' => $beneficiariesList['bank_account_number'],
										'mmid' => '',
										'flag' => 2);
		        $response = $rblApiObject->beneficiaryRegistration($data);
				if($response['status']) {
					$beneficiary->updateBeneficiaryDetails(array('beneficiary_id' => $response['beneficiaryid']),$id);
					echo true;
				} else {
					echo isset($response['description'])? $response['description'] : $response;
				}
								
				
		} else {
			$response = $rblApiObject->beneficiaryResendOtp(array('header' => array('sessiontoken' => $session->rblSessionID),
			'remitterid' => $remitter_data['remitterid'], 
			'beneficiaryid' => $beneficiariesList['beneficiary_id']));
			if($response['status']) {
				echo 0;
			} else {
				echo isset($response['description'])? $response['description'] :  $response;
			}
		}
      

        exit;
    }
	
	public function getCreatebeneAction() {
		
		$formdata = $this->_request->getPost();
        $user = Zend_Auth::getInstance()->getIdentity();
        $form = new Remit_Ratnakar_AddBeneficiaryDetailsForm();
        $beneficiary = new Remit_Ratnakar_Beneficiary();
        $remitters = new Remit_Ratnakar_Remitter();
		$session = new Zend_Session_Namespace("App.Agent.Controller");
        $remitter_id = $session->remitter_id;
		if(!$remitter_id) {
			echo json_encode(array('message' => "Please logout and re-login",'error'=> 1,'reason' => 'Local - remitter id not valid')); 
			exit;		
		}
        $remitterdetail = $remitters->getRemitterById($remitter_id);
		if(!$remitterdetail->remitterid) {
			echo json_encode(array('message' => "Please logout and re-login",'error'=> 1,'reason' => 'RBL - remitter id not valid')); 
			exit;		
		}

        $m = new App\Messaging\Remit\Ratnakar\Agent();
        $beneCount = $remitters->getRemitterbeneficiariesCount($remitter_id);
        if($beneCount  >=  RATNAKAR_MAX_BENFICIARY_COUNT){
            echo json_encode(array('message' => "Remitter has reached the maximum no. of beneficiaries allowed",'error'=> 1)); 
			exit;                                
        }
		if ($this->getRequest()->isPost()) {
				$data = array();
				$data['name'] = $formdata['beni_name'];
				$data['nick_name'] = $formdata['beni_name'];
				$data['bank_name'] = $formdata['beni_bank_name'];
				$data['ifsc_code'] = strtoupper(trim($formdata['beni_ifsc_code']));
				$data['bank_account_number'] = $formdata['beni_bank_account_number'];
				$data['branch_address'] = $formdata['beni_branch_address'];
				$data['branch_city'] = $formdata['beni_branch_city'];
				$data['branch_name'] = $formdata['beni_branch_name'];
				$data['bank_account_type'] = $formdata['beni_bank_account_type'];
				$data['by_agent_id'] = $user->id;
				$data['by_ops_id'] = TXN_OPS_ID;
				$data['remitter_id'] = $remitter_id;
				$data['date_created'] = new Zend_Db_Expr('NOW()');
				try {

                    $benId = $beneficiary->checkbeneficiary($data);
                    if($benId==0){
                        $beneficiary->addbeneficiary($data);
                        $res = $beneficiary->getAdapter()->lastInsertId();
                    }else{
                    	$res = $benId;
                    }
					

						if ($res > 0) {
							$dataToAPI = array('header' => array('sessiontoken' => $session->rblSessionID),
							//'bcagent' =>'TRA1000189',
							'bcagent' =>$user->bcagent,
							'remitterid' =>$remitterdetail->remitterid,
							'beneficiaryname' => $data['name'],
							'beneficiarymobilenumber' => '',
							'beneficiaryemailid' => '',
							'relationshipid' => 2,
							'ifscode' => $data['ifsc_code'],
							'accountnumber' => $data['bank_account_number'],
							'mmid' => '',
							'flag' => 2);
							$rblApiObject = new App_Rbl_Api();
							$apiResponse = $rblApiObject->beneficiaryRegistration($dataToAPI);
							App_Logger::log("BEN ADD RES VALUE -- ".$res, Zend_Log::INFO);

								if(!$apiResponse['status']) {
									echo json_encode(array('message' => $apiResponse['description'],'error' => 1));
									exit;
								} else {
									$data1 = array();
									$data1['name'] = $formdata['beni_name'];									$data1['nick_name'] = $formdata['beni_name'];									
									$data1['beneficiary_id'] = $apiResponse['beneficiaryid'];
									$session->beneficiaryid = $data1['beneficiary_id'];
									$session->local_beneficiaryid = $res;
									$res = $beneficiary->updateBeneficiaryDetails($data1,$res);
									echo json_encode(array('message' => 'Authorization code has been sent on remitter mobile number.','error' => 0));
									exit;
								}
						} 
					
				} catch (Exception $e) {
					echo $errMsg = $e->getMessage();
					exit;
					//App_Logger::log($e->getMessage(), Zend_Log::ERR);
				}
		} else {
			echo 'Invalid request';
			exit;
		}
	}
	
	public function getValidatebeneAction() {
	$formdata = $this->_request->getPost();
	$session = new Zend_Session_Namespace("App.Agent.Controller");
	$remitter_id = $session->remitter_id;
	$remitters = new Remit_Ratnakar_Remitter();
	$remitterdetail = $remitters->getRemitterById($remitter_id);

	if(!$remitter_id) {
		echo json_encode(array('message' => "Please logout and re-login",'error'=> 1,'reason' => 'remitter id not valid')); 
		exit;		
	}
	if(!$remitterdetail->remitterid) {
		echo json_encode(array('meesage' => "Please logout and re-login",'error'=> 1,'reason' => 'remitter id not valid')); 
		exit;		
	}
		
	if( isset($formdata['ben_auth_code']) && !empty($formdata['ben_auth_code'])){
		$rblApiObject = new App_Rbl_Api();
		$optResponse = $rblApiObject->beneficiaryValidation(array('header' => array('sessiontoken' => $session->rblSessionID), 
																  'beneficiaryid' => $session->beneficiaryid,
																  'verficationcode' => $formdata['ben_auth_code']));
		
		if(isset($optResponse['status']) && ($optResponse['status']==1)) {

			
			try {
				$beneficiary = new Remit_Ratnakar_Beneficiary;
				//$beneficiary->updateBeneficiaryDetails(array('rat_status' => 1),$session->local_beneficiaryid);
                                $beneficiary->updateBeneficiaryDetails(array('rat_status' => 1,'status' => STATUS_ACTIVE),$session->local_beneficiaryid);
				$userArr = array(
					'mobile1' => $remitterdetail['mobile'],
					'status' => FLAG_SUCCESS,
					'nick_name' => $formdata['beni_name'],
					'product_name' => RATNAKAR_REMITTANCE
				);
	
				 unset($session->beneficiaryid);
				 unset($session->local_beneficiaryid);	
			 
				echo json_encode(array('message' => "Beneficiary details have been successfully added",'error' => 0));
				exit;									
				
			} catch(Exception $e) { 
			
				echo json_encode(array('message' => $e->getMessage(),'error' => 1)); 
				exit;
				
			}
			
		} else {
		 $this->_helper->FlashMessenger(array('msg-error' => "Invalid Authorization Code ",)
                                    );
		}
		
	} else {
		$rblApiObject = new App_Rbl_Api();
		$rblApiObject->beneficiaryResendOtp(array('header' => array('sessiontoken' => $session->rblSessionID),
											'remitterid' => $remitterdetail->remitterid, 
											'beneficiaryid' => $session->beneficiaryid));
		$this->_helper->FlashMessenger( array('message' => 'Authorization code has been re-sent on remitter mobile number.','error' => 0));
		exit;
	
	}
	
		}
                
                
        public function getRemitterinfoAction(){
       
        $mobile = $this->_getParam("q",0);	
        $remitters = new Remit_Ratnakar_Remitter();
      
        $remitterdetail = $remitters->remitterExists(
                array('mobile'=>$mobile) );
       
        if(!empty($remitterdetail)){
            echo 'false';
        }else{
            echo 'true';  
        }
        
        exit;
    }
    
    public function getLoadfeeAction(){
        
        $user = Zend_Auth::getInstance()->getIdentity();
        $amount = $this->_getParam("amt",0);
        $productID = $this->_getParam("proid",0);
       // $remitters = new Remit_Ratnakar_Remitter();
        $agentID = $user->id; 
        $typecode = TXNTYPE_CARD_RELOAD;
        $feeplan = new FeePlan();
        $feeArr = $feeplan->getRemitterFee($productID, $agentID);
        $cardloadFee = 0;
        $cardloadServiceTax = 0;
        if (!empty($feeArr)) {
            
            $feeAmount = '0.00';
                        //Fees Check
                        
                        foreach ($feeArr as $val) {
                            if ($val['typecode'] == TXNTYPE_CARD_RELOAD) {
                                $val['amount'] = $amount;
                                $val['return_type'] = TYPE_FEE;
                                $feeAmount = Util::calculateRoundedFee($val);
                              //  App_Logger::log($fee, Zend_Log::ERR);
                                break;
                            }
                        }
                $feeComponent = Util::getFeeComponents($feeAmount); 
                $cardloadFee = isset($feeComponent['partialFee']) ? $feeComponent['partialFee'] : 0;
                $cardloadServiceTax = isset($feeComponent['serviceTax']) ? $feeComponent['serviceTax'] : 0;
                
        }
        
        $feechargeAmt = sprintf ("%.2f", ($cardloadFee + $cardloadServiceTax));
       // $remitterdetail = $remitters->remitterExists(
      //          array('mobile'=>$mobile) );
       $message = "<div style='padding: 7px 20px; font-size: 13px; background-color: #fff; font-weight: bold;'>Fee Charge Amount: <span class='WebRupee'></span>".$feechargeAmt."</div>";
        echo $message;
        
        exit;
    }
}
