<?php
/**
 * Cardholder actions
 *
 * @category backoffice
 * @package backoffice_controllers
 * @copyright company
 */

class Corp_Ratnakar_CardholderController extends App_Agent_Controller
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
    
    
    
    public function addAction(){
        
        $this->title = 'Add Cardholder';
        //$session = new Zend_Session_Namespace('App.Agent.Controller');
        $formData  = $this->_request->getPost();
        
        $user = Zend_Auth::getInstance()->getIdentity();        
        $session = new Zend_Session_Namespace('App.Agent.Controller'); 
        /*$minAge = $config->cardholder->age->min;
        $maxAge = $config->cardholder->age->max;
        $currDate = date('Y-m-d');*/
        
        $request = $this->getRequest();
        $chModel  = new Mvc_Axis_CardholderUser(); 
        $objValidation = new Validator();
        $objCardholders = new Corp_Ratnakar_Cardholders();
        $objMobile = new Mobile();
        $objEmail = new Email();
        $objCRN = new CRN();
        //$products  = new Products();
        $errorExists = false;
        
       
        // Get our form and validate it
        $form = new Corp_Ratnakar_AddCardholderForm(array(
                                                    'action' => $this->formatURL('/corp_ratnakar_cardholder/add'),
                                                    'method' => 'post',
                                                    'name'=>'frmAdd',
                                                    'id'=>'frmAdd'
                                                ));                      
        $this->view->form = $form;         
        
        
        $dateOfBirth = isset($formData['date_of_birth'])?$formData['date_of_birth']:'';
        $formData['date_of_birth'] = Util::returnDateFormatted($dateOfBirth, "d-m-Y", "Y-m-d", "-");
        $btnAuth = isset($formData['send_auth_code'])?$formData['send_auth_code']:'0';   
        $resendAuth = isset($formData['resend_auth_code'])?$formData['resend_auth_code']:'';   
        $btnAdd = isset($formData['btn_add'])?$formData['btn_add']:'';
        //$productList = $products->getAgentProducts($user->id);       
        //$form->getElement("product_id")->setMultiOptions($productList);
        
        if( $btnAuth== 1 ){  
            
          // exit('here');
            
            try{
                $objBAPC = new BindAgentProductCommission();
                $bankProdInfo = $objBAPC->getAgentProductAndBank($user->id);
                $bankId = isset($bankProdInfo['bank_id'])?$bankProdInfo['bank_id']:'';
                $productId = isset($bankProdInfo['product_id'])?$bankProdInfo['product_id']:'';
                
                $userData = array('mobile_country_code'=>$formData['mobile_country_code'], 
                                  'mobile1'=>$formData['mobile_number'],
                                  'mobile_number_old'=>$formData['mobile_number_old'],
                                  'product_name' => RATNAKAR_BANK_CORP_CARD,
                                  'product_id' => $productId);                               
               
                $objMsg = new App\Messaging\Corp\Ratnakar\Agent();
                if(isset($session->corp_cardholder_auth))
                    $resp = $objMsg->cardholderAuth($userData, $resend = TRUE);
                else
                    $resp = $objMsg->cardholderAuth($userData);
                
                $formData['date_of_birth'] = $dateOfBirth;
                $this->view->corp_cardholder_auth = $session->corp_cardholder_auth;                       
                $session->corp_cardholder_mobile_number = $formData['mobile_number'];                       
                
                $this->_helper->FlashMessenger( array('msg-success' => 'Authorization code has been sent on your mobile number.',) );
                $form->populate($formData);
                $form->getElement("send_auth_code")->setValue("0");                
                
                //$session->cardholder_auth = 1;
                //echo $session->cardholder_auth;
        }catch (Exception $e ) {  
                $errorExists = true;
                $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) ); 
                $form->populate($formData);
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
            }  
        }
        //echo $session->corp_cardholder_auth;
        
        // adding details in db
        if($btnAdd){
            
            if($form->isValid($this->getRequest()->getPost())){
                
                $aadhaarNo = isset($formData['aadhaar_no'])?trim($formData['aadhaar_no']):'';
                $pan = isset($formData['pan'])?trim($formData['pan']):'';
                $email = isset($formData['email'])?trim($formData['email']):'';
                $mobileNo = isset($formData['mobile_number'])?trim($formData['mobile_number']):'';
                $cardNo = isset($formData['card_number'])?trim($formData['card_number']):'';
                $afn = isset($formData['afn'])?trim($formData['afn']):'';
                
                
                $authValidated = isset($session->corp_validated_cardholder_auth)?$session->corp_validated_cardholder_auth:'0';
            
                if($authValidated!=1 && $session->corp_cardholder_auth != $formData['auth_code'] || ($session->corp_cardholder_mobile_number!=$formData['mobile_number']) ){ // matching the auth code
                     //$this->view->msg = $res;
                     $errorExists = true;
                     $this->_helper->FlashMessenger(
                        array(
                            'msg-error' => 'Invalid Authorization Code',
                        )
                    );
                    $formData['date_of_birth'] = Util::returnDateFormatted($formData['date_of_birth'], "Y-m-d", "d-m-Y", "-"); 
                    $this->view->formData = $formData;
                    $form->getElement("send_auth_code")->setValue("0");
                } else {                        
                
                /*** checking for validation and duplication ***/
                    
                try{
                       
                        // card number check
                        $cardNumCheck = $objCardholders->checkCardNumberDuplication($cardNo);
                        
                        // afn check
                        $afnCheck = $objCardholders->checkAFNDuplication($afn);
                        
                        // mobile number check
                        $mobCheck = $objMobile->checkCorpCardholderMobileDuplicate($mobileNo);

                        
                        // pan card number check
                        if($pan==''){
                            $isPanValid=true;
                        } else {
                                $isPanValid = $objValidation->validatePAN($pan);
                                if($isPanValid)
                                   $isPanValid = $objCardholders->checkPANDuplication($pan);
                               }
                               
                               
                               
                        // aadhaar card number check
                        if($aadhaarNo==''){
                            $isAadhaarValid=true;
                        } else {
                                $isAadhaarValid = $objValidation->validateUID($aadhaarNo);
                                if($isAadhaarValid)
                                   $isAadhaarValid = $objCardholders->checkAadhaarDuplication($aadhaarNo);
                               }

                        
                        // email check
                        $emailCheck = $objEmail->checkCorpCardholderEmailDuplicate($email);
                        
                               
                       // now getting the shmart crn
                          $crnFuncCallCounter=0;
                          $shmartCRN='';
                          while($shmartCRN=='' && $crnFuncCallCounter<5){
                               $shmartCRN = rand('012345','987654');   
                               $crnFuncCallCounter++;                        
                         }
                         
                         
                         if($shmartCRN==''){
                             throw new Exception('Shmart CRN not found');
                         } else { // check shmart crn duplicate
                             $isCRNValid = $objCRN->checkCorpCRNDuplicate($shmartCRN);
                         }

                }
                catch (Exception $e ) {
                        $formData['date_of_birth'] = Util::returnDateFormatted($formData['date_of_birth'], "Y-m-d", "d-m-Y", "-"); 
                        $errorExists = true;
                        $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) ); 
                        $form->populate($formData);
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    } 

                /*** checking for validation and duplication over here ***/    
                    
                   
                /*** adding cardholder details in db ***/
                if(!$errorExists){
                    try{
                        $objBAPC = new BindAgentProductCommission();
                        $bankProdInfo = $objBAPC->getAgentProductAndBank($user->id);
                        $bankId = isset($bankProdInfo['bank_id'])?$bankProdInfo['bank_id']:'';
                        $productId = isset($bankProdInfo['product_id'])?$bankProdInfo['product_id']:'';
                        // unicode will be from api function but taken temporarily here
                        $unicode = rand('123465','987456');                           

                        $cardholderInfo = array(
                                                 'shmart_crn'=>$shmartCRN,
                                                 'card_number'=>$formData['card_number'],
                                                 'afn'=>$formData['afn'],
                                                 'medi_assist_id'=>$formData['medi_assist_id'],
                                                 'employee_id'=>$formData['employee_id'],
                                                 'employer_name'=>$formData['employer_name'],
                                                 'first_name'=>$formData['first_name'],
                                                 'middle_name'=>$formData['middle_name'],
                                                 'last_name'=>$formData['last_name'],
                                                 'aadhaar_no'=>$formData['aadhaar_no'],
                                                 'pan'=>$formData['pan'],
                                                 'mobile_country_code'=>$formData['mobile_country_code'],
                                                 'mobile'=>$formData['mobile_number'],
                                                 'email'=>$formData['email'],
                                                 'gender'=>$formData['gender'],
                                                 'date_of_birth'=>$formData['date_of_birth'],
                                                 'bank_id'=>$bankId,
                                                 'product_id'=>$productId,
                                                 'unicode'=>$unicode,
                                                 'agent_id'=>$user->id,
						 'channel' => CHANNEL_AGENT
                                               );

                        $isCardholderAdded = $objCardholders->addCardholder($cardholderInfo);
                    }catch (Exception $e ) {
                        $formData['date_of_birth'] = Util::returnDateFormatted($formData['date_of_birth'], "Y-m-d", "d-m-Y", "-"); 
                        $errorExists = true;
                        $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) ); 
                        $form->populate($formData);
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    }
                }
                /*** adding cardholder details in db over here ***/
                    
          
                if(!$errorExists && $isCardholderAdded){
                    unset($session->corp_cardholder_auth);
                    unset($session->corp_cardholder_mobile_number);
                    unset($session->corp_validated_cardholder_auth);
                    //$form->populate();
                    
                     $this->_helper->FlashMessenger(
                        array(
                            'msg-success' => 'Cardholder added successfully',
                        )
                    );
               }
              }
            }
          } //  if form does not validate successfully 
          
     }
     
     /* searchAction() will search the cardholders 
      * param: medi assis id, employer name, card number, mobile, email,aadhaar no, pan, 
      */
     
     public function searchAction(){
        $this->title = 'Search Cardholders';

        $data['medi_assist_id'] = $this->_getParam('medi_assist_id');
        $data['employer_name'] = $this->_getParam('employer_name');
        $data['card_number'] = $this->_getParam('card_number');
        $data['mobile'] = $this->_getParam('mobile');
        $data['email'] = $this->_getParam('email');
        $data['aadhaar_no'] = $this->_getParam('aadhaar_no');
        $data['pan'] = $this->_getParam('pan');
        $data['employee_id'] = $this->_getParam('employee_id');
        $param = $data;
        $data['submit_form'] = $this->_getParam('submit_form');
        $data['csrfhash'] = $this->_getParam('csrfhash');
        $data['formName'] = $this->_getParam('formName');
        $objCardholders = new Corp_Ratnakar_Cardholders();
        
        $form = new Corp_Ratnakar_CardholderSearchForm(array('action' => $this->formatURL('/corp_ratnakar_cardholder/search'),
                                                            'method' => 'POST',
                                                            'name'=>'frmSearch',
                                                            'id'=>'frmSearch',
                                                       ));
        
     
       if ($data['submit_form'] != '') {
            
           if($form->isValid($data)){ 
        
           
            $result = $objCardholders->getCardholderSearch($param, $this->_getPage()); 
            $this->view->paginator = $objCardholders->paginateByArray($result, $this->_getPage(), $paginate = NULL);
            $this->view->submit_form = $data['submit_form'];
         }
       }

       $this->view->form = $form;
       $this->view->formData = $data;
       $form->populate($data);
     }
    
  
     
     /**
     * activeAction will active the cardholder
     * param: it will accept the cardholder id with search cardholder querystring to return back search page
     */
    public function activeAction(){
        $this->title = 'Active Cardholder';
        
        $form = new Corp_Ratnakar_StatusActiveCardholderForm();
        $objCardholders = new Corp_Ratnakar_Cardholders();
        $user = Zend_Auth::getInstance()->getIdentity();
        $data['id'] = $this->_getParam('id');
        $data['medi_assist_id'] = $this->_getParam('medi_assist_id');
        $data['employer_name'] = $this->_getParam('employer_name');
        $data['card_number'] = $this->_getParam('card_number');
        $data['mobile'] = $this->_getParam('mobile');
        $data['email'] = $this->_getParam('email');
        $data['aadhaar_no'] = $this->_getParam('aadhaar_no');
        $data['pan'] = $this->_getParam('pan');
        $data['employee_id'] = $this->_getParam('employee_id');
        $data['csrfhash'] = $this->_getParam('csrfhash');
        $data['formName'] = $this->_getParam('formName');
        $data['remarks'] = $this->_getParam('remarks');
        $rowArr = array();
       
        $queryString = 'medi_assist_id='.$data['medi_assist_id'].'&employer_name='.$data['employer_name'];
        $queryString .= '&card_number='.$data['card_number'].'&mobile='.$data['mobile'].'&email='.$data['email'];
        $queryString .= '&aadhaar_no='.$data['aadhaar_no'].'&pan='.$data['pan'].'&employee_id='.$data['employee_id'];
        $queryString .= '&submit_form=Search Cardholder'.'&csrfhash='.$data['csrfhash'].'&formName='.$data['formName'];
        $redictUrl = $this->formatURL('/corp_ratnakar_cardholder/search?'.$queryString);
       // $form->$_cancelLinkUrl = $redictUrl;
        $form->setCancelLink($redictUrl);
        $row = $objCardholders->getCardholderInfo(array('cardholder_id'=>$data['id']));
        if (empty($row)) {
                $this->_helper->FlashMessenger(
                    array(
                        'msg-warning' => sprintf('We cannot find Cardholder with id %s', $id),
                    )
                );
//                $this->_redirect('/agentsummary/');
                $this->_redirect($this->formatURL('/corp_ratnakar_cardholder/search'.$queryString));
            }
            
        $rowArr = $row->toArray();
        
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
       
                $logData = array(
                                 'id'=>$data['id'],
                                 'card_number'=>$rowArr['card_number'],
                                 'medi_assist_id'=>$rowArr['medi_assist_id'],
                                 'employee_id'=>$rowArr['employee_id'],
                                 'cardholder_name'=>$rowArr['cardholder_name'],
                                 'aadhaar_no'=>$rowArr['aadhaar_no'],
                                 'pan'=>$rowArr['pan'],
                                 'gender'=>$rowArr['gender'],
                                 'mobile'=>$rowArr['mobile'],
                                 'email'=>$rowArr['email'],
                                 'employer_name'=>$rowArr['employer_name'],
                                 
                                );
                
                $dataOld = array_merge($logData, array('status'=>$rowArr['cardholder_status']));
                $dataNew = array_merge($logData, array('status'=>STATUS_ACTIVE));
                
                //echo $data['id']; exit;
                $objCardholders->updateCardholderById($dataOld, $dataNew, $user->id, $data['remarks']);
                $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'Cardholder was successfully activated.',
                    )
                );
//                echo '/corp_ratnakar_cardholder/search'.$queryString;
//                exit;   
                $this->_redirect($this->formatURL('/corp_ratnakar_cardholder/search?'.$queryString));
            }
        }else{
            
            
            
            
            
            $row['id'] = $id;
            $form->populate($row->toArray());
            $this->view->item = $row;
        }
        $this->view->form = $form;
        //$this->view->queryString = $this->formatURL('/agentsummary/index'.$queryString);
    }
    
     public function inactiveAction(){
        $this->title = 'Inactive Cardholder';
        
        $form = new Corp_Ratnakar_StatusDeactiveCardholderForm();
        $objCardholders = new Corp_Ratnakar_Cardholders();
        $user = Zend_Auth::getInstance()->getIdentity();
        $data['id'] = $this->_getParam('id');
        $data['medi_assist_id'] = $this->_getParam('medi_assist_id');
        $data['employer_name'] = $this->_getParam('employer_name');
        $data['card_number'] = $this->_getParam('card_number');
        $data['mobile'] = $this->_getParam('mobile');
        $data['email'] = $this->_getParam('email');
        $data['aadhaar_no'] = $this->_getParam('aadhaar_no');
        $data['pan'] = $this->_getParam('pan');
        $data['employee_id'] = $this->_getParam('employee_id');
        $data['csrfhash'] = $this->_getParam('csrfhash');
        $data['formName'] = $this->_getParam('formName');
        $data['remarks'] = $this->_getParam('remarks');
       
        $rowArr = array();
       
        $queryString = 'medi_assist_id='.$data['medi_assist_id'].'&employer_name='.$data['employer_name'];
        $queryString .= '&card_number='.$data['card_number'].'&mobile='.$data['mobile'].'&email='.$data['email'];
        $queryString .= '&aadhaar_no='.$data['aadhaar_no'].'&pan='.$data['pan'].'&employee_id='.$data['employee_id'];
        $queryString .= '&submit_form=Search Cardholder'.'&csrfhash='.$data['csrfhash'].'&formName='.$data['formName'];
        $redictUrl = $this->formatURL('/corp_ratnakar_cardholder/search?'.$queryString);
       // $form->$_cancelLinkUrl = $redictUrl;
        $form->setCancelLink($redictUrl);
        $row = $objCardholders->getCardholderInfo(array('cardholder_id'=>$data['id']));
        if (empty($row)) {
                $this->_helper->FlashMessenger(
                    array(
                        'msg-warning' => sprintf('We cannot find Cardholder with id %s', $id),
                    )
                );
//                $this->_redirect('/agentsummary/');
                $this->_redirect($this->formatURL('/corp_ratnakar_cardholder/search'.$queryString));
        }
            
        $rowArr = $row->toArray();
        
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
       
                $logData = array(
                                 'id'=>$data['id'],
                                 'card_number'=>$rowArr['card_number'],
                                 'medi_assist_id'=>$rowArr['medi_assist_id'],
                                 'employee_id'=>$rowArr['employee_id'],
                                 'cardholder_name'=>$rowArr['cardholder_name'],
                                 'aadhaar_no'=>$rowArr['aadhaar_no'],
                                 'pan'=>$rowArr['pan'],
                                 'gender'=>$rowArr['gender'],
                                 'mobile'=>$rowArr['mobile'],
                                 'email'=>$rowArr['email'],
                                 'employer_name'=>$rowArr['employer_name'],
                                 
                                ); 
                
                $dataOld = array_merge($logData, array('status'=>$rowArr['cardholder_status']));
                $dataNew = array_merge($logData, array('status'=>STATUS_INACTIVE));
                    
                //echo $data['id']; exit;
                $objCardholders->updateCardholderById($dataOld, $dataNew, $user->id, $data['remarks']);
                $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'Cardholder was successfully deactivated.',
                    )
                );
//                echo '/corp_ratnakar_cardholder/search'.$queryString;
//                exit;   
                $this->_redirect($this->formatURL('/corp_ratnakar_cardholder/search?'.$queryString));
            }
        }else{
            
            $id = $this->_getParam('id');
            $row = $objCardholders->getCardholderInfo(array('cardholder_id'=>$id));
            
            if (empty($row)) {
                $this->_helper->FlashMessenger(
                    array(
                        'msg-warning' => sprintf('We cannot find Cardholder with id %s', $id),
                    )
                );
//                $this->_redirect('/agentsummary/');
                $this->_redirect($this->formatURL('/corp_ratnakar_cardholder/search'.$queryString));
            }
            
            $row['id'] = $id;
            $form->populate($row->toArray());
            $this->view->item = $row;
        }
        $this->view->form = $form;
        //$this->view->queryString = $this->formatURL('/agentsummary/index'.$queryString);
    }
    
    
}