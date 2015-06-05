<?php
/**
 * Cardholder actions
 *
 * @category backoffice
 * @package backoffice_controllers
 * @copyright company
 */

class Corp_Boi_CustomerController extends App_Agent_Controller
{
    /**
     * Overrides Zend_Controller_Action::init()
     *
     * @access public
     * @return void
     */
   public function init(){
        // init the parent
        parent::init();
        // initiating the session
        $this->session = new Zend_Session_Namespace("App.Agent.Controller");
        }
        
     public function indexAction() {
        
    }
   public function adddetailsAction()
    {      
    	$this->title = 'Add Cardholder - Capture Account Opening Form (AOF)';
        $m = new App\Messaging\Corp\Boi\Agent();
        $formData  = $this->_request->getPost();
        $user = Zend_Auth::getInstance()->getIdentity();        
        $session = new Zend_Session_Namespace('App.Agent.Controller'); 
        unset($session->customer_id);
        $valid = true;
        
        $objCustomerModel = new Corp_Boi_Customers();
        $objCustomerProduct = new Corp_Boi_CustomerProduct();
        $masterPurseModel = new MasterPurse();
        $custPurseModel = new Corp_Boi_CustomerPurse();
        $products  = new Products();
        $bankModel  = new Banks();
        $objBaseTxn = new BaseTxn();
        $agentModel = new AgentUser();	
        $agentDetailModel = new Agents();
        
        
        // Get our form and validate it
        $form = new Corp_Boi_AddCustomerDetailsForm();  
        $customerLogModel = new Corp_Boi_CustomersLog();
        $this->view->form = $form;
        $bank = App_DI_Definition_Bank::getInstance(BANK_BOI);
        $product = App_DI_Definition_BankProduct::getInstance(BANK_BOI_NDSC);
        $bankUnicode = $product->bank->unicode;
        $productUnicode = $product->product->unicode;
        $productList = $products->getAgentProducts($user->id, PROGRAM_TYPE_CORP ,$productUnicode);       
        $form->getElement("product_id")->setMultiOptions($productList);
        $afn_no = new AfnNumber();
        
        if(isset($formData['ref_num'])) {
            $afn_no->setNSDCRefNum($formData['ref_num']);
        } else {
             if ($afn_no->generateNSDCRefNum()) {
                            $paramsAfnNo = $afn_no->getNSDCRefNum(); //Get Txncode
                $form->getElement("ref_num")->setValue($paramsAfnNo);
            }
        }
        
        /*if ($afn_no->generateTxncode()) {
        		$paramsAfnNo = $afn_no->getTxncode(); //Get Txncode
        		$randomString = substr(str_shuffle($paramsAfnNo), 0, 5);
            $form->getElement("sol_id")->setValue($randomString);
        }*/
        $parantInfo = $agentModel->getParentInfo($user->id);                    
        $agentDetails = $agentDetailModel->findAgentByAgentId($user->id);
        //if(count($parantInfo)){
        	$parant = $agentDetailModel->findById($parantInfo['id']);                    
        	$form->getElement("training_center_id")->setValue($agentDetails->centre_id);
        	$form->getElement("traning_center_name")->setValue($agentDetails->institution_name);
        	$form->getElement("training_partner_name")->setValue($parant->institution_name);
        	$form->getElement("sol_id")->setValue($agentDetails->branch_id);
        //}
                
         
      if(isset($formData['marital_status'])) {
          $form->getElement("marital_status")->setValue($formData['marital_status']);
      } else {
          $form->getElement("marital_status")->setValue("N");
      }
      if(isset($formData['nomination_flg'])) {
          $form->getElement("nomination_flg")->setValue($formData['nomination_flg']);
      } else {
          $form->getElement("nomination_flg")->setValue("N");
      }
            // getting current ip to store in db
            $ip = $objCustomerModel->formatIpAddress(Util::getIP());
            if(empty($agentDetails->branch_id) || $agentDetails->branch_id < 1) {
                   $this->_helper->FlashMessenger(
                                    array(
                                        'msg-error' => 'Your Branch Id is Invalid. Please contact Shmart Administrator.'
                                    )
                            );
            } elseif ($this->getRequest()->isPost()) {
                 
                if($form->isValid($this->getRequest()->getPost())){
               
		    $formData['date_of_birth'] = Util::returnDateFormatted($formData['date_of_birth'] , "d-m-Y", "Y-m-d", "-");
                    $formData['nominee_dob'] = Util::returnDateFormatted($formData['nominee_dob'] , "d-m-Y", "Y-m-d", "-");
                    
                    list($year, $month, $day) = sscanf($formData['date_of_birth'], '%d-%d-%d');
                    list($nyear, $nmonth, $nday) = sscanf($formData['nominee_dob'], '%d-%d-%d');
                    
                    if (!checkdate($month, $day, $year)) {
                        $this->_helper->FlashMessenger(
                        array(
                            'msg-error' => 'Invalid Date of Birth',
                        ));
                        $valid = false;
                    }
                    
                    if(!empty ($formData['nominee_dob']))
                    {
                        if (!checkdate($nmonth, $nday, $nyear)) {
                            $this->_helper->FlashMessenger(
                                array(
                                    'msg-error' => 'Invalid Nominee Date of Birth',
                                ));
                            $valid = false;
                        }                        
                    }
                    
                    if($valid == false)
                    {
                        $formData['date_of_birth'] = Util::returnDateFormatted($formData['date_of_birth'] , "Y-m-d", "d-m-Y", "-");
                        $formData['nominee_dob'] = Util::returnDateFormatted($formData['nominee_dob'] , "Y-m-d", "d-m-Y", "-");
                    }
                    else
                    {
                    $customerData = array(
                    'product_id' => $formData['product_id'],
                    'sol_id' => $agentDetails->branch_id, //$formData['sol_id'],
                    'title' => $formData['title'],
                    'first_name' => $formData['first_name'],
                    'middle_name' => $formData['middle_name'],
                    'last_name' => $formData['last_name'],
                    'date_of_birth' => $formData['date_of_birth'],
                    'gender' => $formData['gender'],
                    'occupation' => $formData['occupation'],
                    'mobile' => $formData['mobile'],
                    'landline' => $formData['landline'],
                    'email' => $formData['email'],
                    'aadhaar_no' => $formData['aadhaar_no'],
                    'pan' => $formData['pan'],
                    'uid_no' => $formData['uid_no'],
                    'country_code' => $formData['country_code'],
                    'address_line1' => $formData['address_line1'],
                    'address_line2' => $formData['address_line2'],
                    'city' => $formData['city'],
                    'state' => $formData['state'],
                    'pincode' => $formData['pincode'],
                    'comm_country_code' => $formData['comm_country_code'],
                    'comm_address_line1' => $formData['comm_address_line1'],
                    'comm_address_line2' => $formData['comm_address_line2'],
                    'comm_city' => $formData['comm_city'],
                    'comm_state' => $formData['comm_state'],
                    'comm_pin' => $formData['comm_pin'],
                    'marital_status' => $formData['marital_status'],
                    'ref_num' => $formData['ref_num'],
//                    'aof_ref_num' => $formData['aof_ref_num'],
                    'cust_comm_code' => $formData['cust_comm_code'],
                    'nsdc_enrollment_no' => $formData['nsdc_enrollment_no'],
                    'account_type_id' => $formData['account_type_id'],
                    'nomination_flg' => $formData['nomination_flg'],
                    'nominee_name' => $formData['nominee_name'],
                    'nominee_relationship' => $formData['nominee_relationship'],
                    'nominee_dob' => $formData['nominee_dob'],
                    'nominee_add_line1' => $formData['nominee_add_line1'],
                    'nominee_add_line2' => $formData['nominee_add_line2'],
                    'nominee_city_cd' => $formData['nominee_city_cd'],
                    'nominee_minor_flag' => $formData['nominee_minor_flag'],
                    'nominee_minor_guradian_cd' => $formData['nominee_minor_guradian_cd'],
                    'by_agent_id' => $user->id,
                    'ip' => $ip,
                    'date_created' => date('Y-m-d H:i:s'),
                    'status' => STATUS_PENDING,
                    'status_bank' => STATUS_PENDING,
                    'status_ops' => STATUS_PENDING,
                    'status_ecs' => STATUS_PENDING,
                    'debit_mandate_amount' => $formData['debit_mandate_amount'],
                    'minor_flg' => $formData['minor_flg'],
                    'minor_guardian_name' => $formData['minor_guardian_name'],
                    'training_center_id' => $agentDetails->centre_id, //$formData['training_center_id'],
                    'traning_center_name' => $agentDetails->institution_name, //$formData['traning_center_name'],
                    'training_partner_name' => $parant->institution_name, //$formData['training_partner_name'],
                );


                try{  
                $validateArr = array(
                     'tablename' => DbTable::TABLE_BOI_CORP_CARDHOLDER,
                     'product_id' => $formData['product_id'],
                     'col_value' => $formData['pan'],
                     'col_name' => 'pan',
                     'col' => 'PAN',
                );
                /* Validating PAN Card */        
                $objValidator = new Validator();
                $objValidator->validatePAN($formData['pan']);
               if(trim($formData['pan']) != ''){
                $objValidator->checkColDuplicacy($validateArr);
                }
                $aadhar_num = $formData['aadhaar_no'];
                if(trim($aadhar_num) != '')
                {
                   $objValidator->validateAadhar($aadhar_num);
                   $validateArr['col_value'] = $formData['aadhaar_no'];
                   $validateArr['col_name'] = 'aadhaar_no';
                   $validateArr['col'] = 'Aadhaar No.';
                   $objValidator->checkColDuplicacy($validateArr);
                }

                /*Validating Date of Birth*/
                //Validator::isMinor($formData['date_of_birth']);
                $ismidminor = Validator::isMidMinor($formData['date_of_birth']); 
                if($ismidminor)
                {
                    $customerData['minor_flg'] = 'Y';
                }
                else
                {
                    $customerData['minor_flg'] = 'N';
                }
                if($formData['nomination_flg'] == 'Y'){
                    
                if(!$objValidator->validateYear($formData['nominee_dob'],18)) {
                    
                    $formData['nominee_minor_flag'] = 'Y';
                    if($formData['minor_guardian_name'] == '' || $formData['nominee_minor_guradian_cd'] == '') {
                        throw new Exception("Please enter Nominee Minor Guardian Details");
                    }
                }
                else
                {
                   $formData['nominee_minor_flag'] = 'N';
                   $customerData['nominee_minor_flag'] = 'N';
                   $customerData['minor_guardian_name'] ='';
                   $customerData['nominee_minor_guradian_cd'] ='';
                    
                }
                }
                else{
                   $customerData['nominee_name'] = '';
                   $customerData['nominee_relationship'] = '';
                   $customerData['nominee_dob'] = '';
                   $customerData['nominee_add_line1'] = '';
                   $customerData['nominee_add_line2'] = '';
                   $customerData['nominee_city_cd'] = '';
                   $customerData['nominee_minor_flag'] = 'N';
                   $customerData['minor_guardian_name'] ='';
                   $customerData['nominee_minor_guradian_cd'] ='';
                }
                 
            $duplicate = $objCustomerModel->isDuplicate($customerData);
            if($duplicate['cnt'] == 0) {
                
                
                
                $customerAddResp = $objCustomerModel->save($customerData);
                
                $afn_no->setNSDCRefNumUsedStatus(); //Mark Txncode as used

                $objCustomerModel->validateSOLID($customerAddResp, $user->id);

                $data = array('product_customer_id' => $customerAddResp,'by_type' => BY_MAKER,'by_id' => $user->id, 
                        'status_old' => STATUS_PENDING,'status_new' => STATUS_PENDING,
                        'status_ops_old' => STATUS_PENDING,'status_ops_new' => STATUS_PENDING,
                        'status_bank_old' => STATUS_PENDING,'status_bank_new' => STATUS_PENDING,
                        'status_ecs_old' => STATUS_PENDING,'status_ecs_new' => STATUS_PENDING,
                        'remarks' => 'Customer Registration');

                        $customerLogModel->save($data);

                    $productDetail = $products->getProductInfo($formData['product_id']);
                    $a = new CustomerMaster();
                    $customerMasterId = $a->generateCustomerId(array('bank_id' => $productDetail['bank_id']));
                    $custMasterDetails = $a->findById($customerMasterId);
                    $custMasterDetail = Util::toArray($custMasterDetails);
                    // adding data in rat_customer_master table

                    $ratCustomerMasterData = array(
                        'customer_master_id' => $customerMasterId,
                        'shmart_crn' => $custMasterDetail['shmart_crn'],
                        'first_name' => $formData['first_name'],
                        'middle_name' => $formData['middle_name'],
                        'last_name' => $formData['last_name'],
                        'aadhaar_no' => $formData['aadhaar_no'],
                        'pan' => $formData['pan'],
                        'mobile_country_code' => isset($formData['mobile_country_code']) ? $formData['mobile_country_code'] : '', 
                        'mobile' => $formData['mobile'],
                        'email' => $formData['email'],
                        'gender' => $formData['gender'],
                        'date_of_birth' => $formData['date_of_birth'],
                        'status' => STATUS_PENDING,
                    );
                    $ratCustomerId = $objCustomerModel->addBoiCustomerMaster($ratCustomerMasterData);
                    //insert into customer purse
                    $purseDetails = $masterPurseModel->getPurseDetailsbyBankIdProductId($formData['product_id'], $productDetail['bank_id']);
                    foreach ($purseDetails as $purseDetail) {
                        $purseArr = array(
                            'customer_master_id' => $customerMasterId,
                            'boi_customer_id' => $ratCustomerId,
                            'product_id' => $formData['product_id'],
                            'purse_master_id' => $purseDetail['id'],
                            'bank_id' => $productDetail['bank_id'],
                            'date_updated' => new Zend_Db_Expr('NOW()')
                        );

                        $purseParam = array('boi_customer_id' => $ratCustomerId, 'purse_master_id' => $purseDetail['id']);
                        $purseDetails1 = $custPurseModel->getCustPurseDetails($purseParam);
                        if (empty($purseDetails1)) { // If purse entry not found
                            $custPurseModel->save($purseArr);
                        }
                    }
                    // Get Customer product details
    //                //Update customer product
    //                  $prodUpdateArr = array(
    //                        'boi_customer_id' => $ratCustomerId,
    //                    );
    //                 $custProductModel->updateCustProduct($prodUpdateArr,"product_customer_id = $id");

                    // update the status to STATUS_ACTIVE in cardholders
                    $updateArr = array( 
                        'customer_master_id' => $customerMasterId, 
                        'boi_customer_id' => $ratCustomerId
                            );
                    $objCustomerModel->update( $updateArr, "id= $customerAddResp");

                      $bankDetail = $bankModel->getBankbyUnicode($bankUnicode);
                      $custProduct = array(
                          'product_customer_id' => $customerAddResp,
                          'boi_customer_id' => $ratCustomerId,
                          'product_id' => $formData['product_id'],
                          'program_type' => PROGRAM_TYPE_CORP,
                          'bank_id' => $bankDetail['id'],
                          'by_agent_id' => $user->id,
                          'date_created' => new Zend_Db_Expr('NOW()')
                      );
                      $objCustomerProduct->saveCustProduct($custProduct);
                } 

                  $this->_helper->FlashMessenger(
                                    array(
                                        'msg-success' => 'Please provide the Application Reference No '.$formData['ref_num'].' at the top of the Physical Application Form.<br><br>The Physical Application Form has to be handover to the linked bank branch in the next 5 working days.'
                                    )
                            );
                 $this->_redirect($this->formatURL('/corp_boi_customer/complete'));
                  
                                
                         }catch (Exception $e ) { 
                                                        $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) ); 
                                                        $formData['date_of_birth'] = Util::returnDateFormatted($formData['date_of_birth'] , "Y-m-d", "d-m-Y", "-");
                                                        $formData['nominee_dob'] = Util::returnDateFormatted($formData['nominee_dob'] , "Y-m-d", "d-m-Y", "-");
                    
                                                        $form->populate($formData);
                                                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                                                    }                 
         
            
                }
            }
            
    }  
    
    
            $form->populate($formData);
            
 } // adddetails action closes here
        
        
        /* registrationcompleteAction is responsible for handling unset the required session details of remitter       
        */
        public function completeAction(){
            
         $this->title = 'Enroll Customer - Complete';
         
        }
      

        public function opsrejectedAction() {
        $this->title = 'Rejected Customers List';
        $customerModel = new Corp_Boi_Customers();
        $page = $this->_getParam('page');
        $this->view->paginator = $customerModel->showOpsrejectedCustomerDetails($page);
    }
   
     public function viewAction() {

        $this->title = 'Customer Details';
        $cardholdersModel = new Corp_Boi_Customers();
        $id = $this->_getParam('id');
        if (!is_numeric($id)) {
            $this->_helper->FlashMessenger(
                    array(
                        'error' => 'The user id you provided is invalid',
                    )
            );

            $this->_redirect($this->formatURL('/corp_boi_customer/opsrejected/'));
        }

        $row = $cardholdersModel->findById($id);
       
        $row->gender = ucfirst($row->gender);
        $row->date_of_birth = Util::returnDateFormatted($row->date_of_birth, "Y-m-d", "d-m-Y", "-");
        $row->date_failed = Util::returnDateFormatted($row->date_failed, "Y-m-d", "d-m-Y", "-");
        //echo '<pre>';print_r($row);exit;
        if (empty($row)) {
            $this->_helper->FlashMessenger(
                    array(
                        'error' => 'User with Id does not exist',
                    )
            );

            $this->_redirect($this->formatURL('/corp_boi_customer/opsrejected/'));
        }
       
        
        //Select Cardholder's Purse
        $this->view->cardholderPurses = array();
        if (isset($row->boi_customer_id) && $row->boi_customer_id > 0) {
            $cardHolder = new Corp_Boi_Customers();
            $this->view->cardholderPurses = $cardHolder->getCardholderPurses($row->boi_customer_id);
        }

       
         // Get status and comments
        $this->view->cardholderStatus = array();
            $cardHolderObj = new Corp_Boi_CustomersLog();
            $this->view->cardholderStatus = $cardHolderObj->getCardholderStatus($row->id,$byType = BY_MAKER);
            
        
        
        $this->view->backlink = Zend_Controller_Front::getInstance()->getBaseUrl() . '/corp_boi_customer/opsrejected';
        $this->view->item = $row;
    }
    
   public function editAction()
    {      
          
        $this->title = 'Edit Customer Details';
        $m = new App\Messaging\Corp\Boi\Agent();
        $formData  = $this->_request->getPost();
        $user = Zend_Auth::getInstance()->getIdentity();        
        $id = $this->_getParam('id');
        $objCustomerModel = new Corp_Boi_Customers();
        $objCustomerDetailModel = new Corp_Boi_CustomerDetail();
        $custDetails = $objCustomerModel->findById($id);
        $custDetails = Util::toArray($custDetails);
        // Get our form and validate it
        $form = new Corp_Boi_EditCustomerDetailsForm();  
        $customerLogModel = new Corp_Boi_CustomersLog();
        $this->view->form = $form;
        $product = App_DI_Definition_BankProduct::getInstance(BANK_BOI_NDSC);
        $productUnicode = $product->product->unicode;
        $productModel = new Products();
        $productInfo = $productModel->getProductInfoByUnicode($productUnicode);
            // getting current ip to store in db
            $ip = $objCustomerModel->formatIpAddress(Util::getIP());
            
            if ($this->getRequest()->isPost()) {
                 
                if($form->isValid($this->getRequest()->getPost())){
               
                    
                    $formData['date_of_birth'] = Util::returnDateFormatted($formData['date_of_birth'] , "d-m-Y", "Y-m-d", "-");
                    $formData['nominee_dob'] = Util::returnDateFormatted($formData['nominee_dob'] , "d-m-Y", "Y-m-d", "-");
                    $customerData = array(
                    'title' => $formData['title'],
                    'first_name' => $formData['first_name'],
                    'middle_name' => $formData['middle_name'],
                    'last_name' => $formData['last_name'],
                    'date_of_birth' => $formData['date_of_birth'],
                    'gender' => $formData['gender'],
                    'occupation' => $formData['occupation'],
                    'mobile' => $formData['mobile'],
                    'landline' => $formData['landline'],
                    'email' => $formData['email'],
                    'aadhaar_no' => $formData['aadhaar_no'],
                    'pan' => $formData['pan'],
                    'uid_no' => $formData['uid_no'],
                    'address_line1' => $formData['address_line1'],
                    'address_line2' => $formData['address_line2'],
                    'city' => $formData['city'],
                    'state' => $formData['state'],
                    'pincode' => $formData['pincode'],
                    'comm_address_line1' => $formData['comm_address_line1'],
                    'comm_address_line2' => $formData['comm_address_line2'],
                    'comm_city' => $formData['comm_city'],
                    'comm_state' => $formData['comm_state'],
                    'comm_pin' => $formData['comm_pin'],
                    'marital_status' => $formData['marital_status'],
                    'ref_num' => $formData['ref_num'],
//                    'aof_ref_num' => $formData['aof_ref_num'],
//                    'cust_comm_code' => $formData['cust_comm_code'],
                    'nsdc_enrollment_no' => $formData['nsdc_enrollment_no'],
                    'account_type_id' => $formData['account_type_id'],
                    'nomination_flg' => $formData['nomination_flg'],
                    'nominee_name' => $formData['nominee_name'],
                    'nominee_relationship' => $formData['nominee_relationship'],
                    'nominee_dob' => $formData['nominee_dob'],
                    'nominee_add_line1' => $formData['nominee_add_line1'],
                    'nominee_add_line2' => $formData['nominee_add_line2'],
                    'nominee_city_cd' => $formData['nominee_city_cd'],
                    'nominee_minor_flag' => $formData['nominee_minor_flag'],
                    'nominee_minor_guradian_cd' => $formData['nominee_minor_guradian_cd'],
                    'by_agent_id' => $user->id,
                    'ip' => $ip,
                    'debit_mandate_amount' => $formData['debit_mandate_amount'],
                        'minor_flg' => $formData['minor_flg'],
                        'minor_guardian_name' => $formData['minor_guardian_name'],
                    'date_created' => date('Y-m-d H:i:s'),
                    'status' => STATUS_PENDING,
                    'status_bank' => STATUS_PENDING,
                    'status_ops' => STATUS_PENDING,
                    'status_ecs' => STATUS_PENDING,
                );


                try{  
               
            $validateArr = array(
                     'tablename' => DbTable::TABLE_BOI_CORP_CARDHOLDER,
                     'product_id' => $productInfo->id,
                     'col_value' => $formData['pan'],
                     'col_name' => 'pan',
                     'col' => 'PAN',
                );
                /* Validating PAN Card */        
                $objValidator = new Validator();
                $objValidator->validatePAN($formData['pan']);
               if(trim($formData['pan']) != '' && $formData['pan'] != $custDetails['pan']){
                $objValidator->checkColDuplicacy($validateArr);
                }
                $aadhar_num = $formData['aadhaar_no'];
                if(trim($aadhar_num) != '')
                {
                   $objValidator->validateAadhar($aadhar_num);
                   $validateArr['col_value'] = $formData['aadhaar_no'];
                   $validateArr['col_name'] = 'aadhaar_no';
                   $validateArr['col'] = 'Aadhaar No.';
                   if($formData['aadhaar_no'] != $custDetails['aadhaar_no']){
                   $objValidator->checkColDuplicacy($validateArr);
                   }
                }

            
            $ismidminor = Validator::isMidMinor($formData['date_of_birth']); 
            if($ismidminor)
            {
                $customerData['minor_flg'] = 'Y';
            }
            else
            {
                $customerData['minor_flg'] = 'N';
            }
            if($formData['nomination_flg'] == 'Y'){
                    
                if(!$objValidator->validateYear($formData['nominee_dob'],18)) {
                    
                    $formData['nominee_minor_flag'] = 'Y';
                    if($formData['minor_guardian_name'] == '' || $formData['nominee_minor_guradian_cd'] == '') {
                        throw new Exception("Please enter Nominee Minor Guardian Details");
                    }
                }
                else
                {
                   $formData['nominee_minor_flag'] = 'N';
                   $customerData['nominee_minor_flag'] = 'N';
                   $customerData['minor_guardian_name'] ='';
                   $customerData['nominee_minor_guradian_cd'] ='';
                    
                }
                }
                else{
                   $customerData['nominee_name'] = '';
                   $customerData['nominee_relationship'] = '';
                   $customerData['nominee_dob'] = '';
                   $customerData['nominee_add_line1'] = '';
                   $customerData['nominee_add_line2'] = '';
                   $customerData['nominee_city_cd'] = '';
                   $customerData['nominee_minor_flag'] = 'N';
                   $customerData['minor_guardian_name'] ='';
                   $customerData['nominee_minor_guradian_cd'] ='';
                }
                 
             
            // updating customer details in db.
                    unset($custDetails['id']);
                    $custDetails['product_customer_id'] = $id;
            // Saving in details table    
            $objCustomerModel->update($customerData,"id = $id");
            $objCustomerDetailModel->save($custDetails);
            
            
            
            
            // updating status 
            // 
              $data = array('product_customer_id' => $id,'by_type' => BY_MAKER,'by_id' => $user->id, 
                    'status_ops_old' => STATUS_REJECTED,'status_ops_new' => STATUS_PENDING,'ip' => $ip,'comments' => $formData['comments']
                    );
               
                  
                    $customerLogModel->save($data);
         


           
                  
                  $this->_helper->FlashMessenger(
                                    array(
                                        'msg-success' => 'Customer details edited successfully',
                                    )
                            );
                 $this->_redirect($this->formatURL('/corp_boi_customer/opsrejected'));
                 $form->populate($formData);
                  
                                
                         }catch (Exception $e ) { 
                                                        $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) ); 
                                                        $form->populate($formData);
                                                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                                                    }                 
         
            
            
            }
            
    }  
   
    $custDetails['date_of_birth'] = Util::returnDateFormatted($custDetails['date_of_birth'],"Y-m-d","d-m-Y", "-");
    $custDetails['nominee_dob'] = Util::returnDateFormatted($custDetails['nominee_dob'],"Y-m-d","d-m-Y", "-");
            
            $form->populate($custDetails);
            
 } // edit etails action closes here
        
        
        /* registrationcompleteAction is responsible for handling unset the required session details of remitter       
        */
        public function editcompleteAction(){
            
         $this->title = 'Edit Customer - Complete';
         
        }
   
}
