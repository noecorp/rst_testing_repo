<?php
/**
 * Allows the agent to register
 *
 * @category backoffice
 * @package backoffice_controllers
 * @copyright company
 */

class SignupController extends App_Agent_Controller
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
        $user = Zend_Auth::getInstance()->getIdentity();
        $agentModel = new AgentUser();
        // use the withoutlogin layout
        if(!isset($user->id) || !($agentModel->isSuperAgent($user->id) || $agentModel->isDistributorAgent($user->id))) {
            $this->_helper->layout()->setLayout('withoutlogin');
        }
    }
    
    
    
    public function indexAction(){
         unset($this->session->agent_id);
         unset($this->session->step_one);
         unset($this->session->step_two);
         unset($this->session->step_three);
         unset($this->session->step_four);
         $user = Zend_Auth::getInstance()->getIdentity();
         if($user->user_type == SUPER_AGENT){
             $this->title = 'Distributer Signup, Phone Verification';
        }else{
            $this->title = 'Training Center BC Signup, Phone Verification';
        }
         
        // Agent phone entry form.
        $form = new AgentphoneForm();      
        
        if ($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())){
                $agentModel = new AgentUser();
                
                try {
                $res = $agentModel->checkPhone($form->getValue('phone'));
                }
                 catch (Exception $e ) {
                   App_Logger::log($e->getMessage(), Zend_Log::ERR);
                   $errMsg = $e->getMessage();
                   
                        $this->_helper->FlashMessenger(
                            array(
                                    'msg-error' => $errMsg,
                                 )
                            );
                }  
              
                if($res =='phone_dup'){
                 
                $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'Mobile number already registered with us',
                    )
                );
                }            
                else 
                {   $this->session->agent_id = $res;
                    $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'Please check SMS on your mobile to get verification code',
                    )
                );
                     $this->session->mobile1=$form->getValue('phone') ;
                    
                    //Generate random verification code and store it in a session and send it to mobile phone in  SMS
                    $alerts = new Alerts();
                    $verificationCode = $alerts->generateAuthCode();
                    //echo $verificationCode;
                    $this->session->ver_code = $verificationCode;
                    //$this->session->ver_code;
                    try{
                        
                        $info = array ('v_code'=>$verificationCode,'mobile1'=>$form->getValue('phone'));
                        
                        $sendConf = $alerts->sendVerificationCode($info, 'operation');
                         
                         
                    } catch (Exception $e ) {    
                                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                                $errMsg = $e->getMessage();
                                $this->_helper->FlashMessenger(
                                    array(
                                        'msg-error' => $errMsg,
                                    )
                                );
                    } 
//                    $this->_redirect('/signup/verification/');
                    $this->_redirect($this->formatURL('/signup/verification/'));
                    
                   
                }
                
            }
        }
        $this->view->form = $form;
        $this->view->title = $this->title;
    }
    
    //set verification code
     public function verificationAction()
    {
        $this->title = 'Mobile Verification Code';
        //echo $this->session->ver_code;
        // use the login layout
        
        $form = new VerificationForm();
        
        if ($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())){
               if( $this->session->ver_code == $form->getValue('code') )
               {
                  
                   $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'Mobile phone verified sucessfully. Please proceed.',
                    )
                );
//                   $this->_redirect('/signup/add');
                   $this->_redirect($this->formatURL('/signup/add'));
                  
               }
               else
               {
                
                   
                   $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'Incorrect verification code entered',
                    )
                );
               }
            }
            
            
        } 
        
        $this->view->form = $form;
         
    }
    
    
    public function addAction() {



        $this->title = 'Add Basic Details';

        $form = new AgentForm();
        $agentModel = new AgentUser();
        $res = '';
        $agent_id = '';
        $agent_id = $this->session->agent_id;

        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $formData = $this->_request->getPost();
                if (isset($formData['std']) && isset($formData['mobile2'])) {
                    $landline = $formData['std'] . '-' . $formData['mobile2'];
                } else {
                    $landline = '';
                }
                $data = array('title' => $formData['title'],
                    'email' => trim($formData['email']),
                    'status' => STATUS_UNBLOCKED,
                    'email_verification_status' => STATUS_PENDING,
                    'enroll_status' => STATUS_INCOMPLETE,
                    'mobile1' => $formData['mobile1'],
                    'mobile2' => $landline,
                    'ip' => Util::getIP(),
                    'first_name' => $formData['first_name'],
                    'middle_name' => $formData['middle_name'],
                    'last_name' => $formData['last_name'],
                    'centre_id' => $formData['centre_id'],
                    'terminal_id_tid_1' => $formData['terminal_id_tid_1'],
                    'terminal_id_tid_2' => $formData['terminal_id_tid_2'],
                    'terminal_id_tid_3' => $formData['terminal_id_tid_3'],
                    'reg_ops_id' => 0,
                    'reg_datetime' => new Zend_Db_Expr('NOW()'),
                     'institution_name' => $formData['institution_name']
                );
                $user = Zend_Auth::getInstance()->getIdentity();
                $objectRelation = new ObjectRelations();                
                $typeId = 0;
                if(isset($user->id) && $agentModel->isSuperAgent($user->id)) {
                    $data['user_type'] = Util::getAgentTypeValue(DISTRIBUTOR_AGENT);
                    $data['parent_id'] = $user->id;
                    $typeId = $objectRelation->getRelationTypeId(SUPER_TO_DISTRIBUTOR);
                } elseif(isset($user->id) && $agentModel->isDistributorAgent($user->id)) {
                    $data['user_type'] = Util::getAgentTypeValue(SUB_AGENT);
                    $data['parent_id'] = $user->id;
                    $typeId = $objectRelation->getRelationTypeId(DISTRIBUTOR_TO_AGENT);                    
                }
                $agn_info = array(
//                    a'afn' => $formData['afn'],
                    'email' => $formData['email'],
                    'auth_email' => $formData['auth_email'],
                    //'office'=>$formData['office'],
                    //'shop'=>$formData['shop'],
                    'date_created' => new Zend_Db_Expr('NOW()'),
                    'mobile1' => $formData['mobile1'],
                    'mobile2' => $landline,
                    'first_name' => $formData['first_name'],
                    'middle_name' => $formData['middle_name'],
                    'last_name' => $formData['last_name'],
                    'by_ops_id' => 0,
                    'status' => 'active',
                    'ip' => Util::getIP()
                );



                if ($agent_id > 0) {
                    $this->_helper->FlashMessenger(
                            array(
                                'msg-error' => 'Profile details cannot be altered here',
                            )
                    );
                } else {

                    try {
                        $res = $agentModel->signupAgent($data, $agn_info);
                        if($typeId > 0) {
                            $objectRelation->insert(array(
                                'from_object_id'    =>  $user->id,
                                'to_object_id'      =>  $res,
                                'object_relation_type_id'   => $typeId
                            ));
                        }
                    } catch (Exception $e) {
                        //echo '<pre>';print_r($e);exit;
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                        $signupMsg = $e->getMessage();
                    }


                    if ($res > 0) {


                        $this->_helper->FlashMessenger(
                                array(
                                    'msg-success' => "Basic profile was successfully added",
                                )
                        );
                        $this->session->agent_id = $res;
                        $this->session->step_one = 1;

//                        $this->_redirect('/signup/addeducation');
                        $this->_redirect($this->formatURL('/signup/addeducation'));
                    } else {
                        $this->_helper->FlashMessenger(
                                array(
                                    'msg-error' => $signupMsg,
                                )
                        );
                    }
                }
            }


            $this->view->error = TRUE;
        }

        $agent_id = $this->session->agent_id;
        if ($agent_id) {
            $agentsModel = new Agents();
            $row = $agentsModel->findById($agent_id);
            $form->populate($row->toArray());
            $alternatePhone = $row['mobile2'];
            $phoneArr = explode("-", $alternatePhone);
            $std = $phoneArr['0'];
            $phoneNum = $phoneArr['1'];

            $form->getElement('std')->setValue($std);
            $form->getElement('mobile2')->setValue($phoneNum);
            $this->view->item = $row;
        }


        $this->view->agent_id = $res;

        $this->view->form = $form;
        $form->getElement('mobile1')->setValue($this->session->mobile1);
    }
    
    //Education Details
     public function addeducationAction(){
       
          if (isset($this->session->step_one)){
              
          $request = $this->getRequest(); 
          $this->title = 'Add Education Details';
          
        $form = new AgenteduForm();
        $agentModel = new AgentUser();
        $agent_id = $this->session->agent_id;
             
        if ($this->getRequest()->isPost()) {
             
           
            if($form->isValid($this->getRequest()->getPost())){
                      
              $formData  = $this->_request->getPost();
                
    
    $formdata = array('education_level' => $formData['education_level'],
        'matric_school_name' => $formData['matric_school_name'],
        'intermediate_school_name' =>$formData['intermediate_school_name'],
        'graduation_degree' => $formData['graduation_degree'],
        'graduation_college' =>$formData['graduation_college'],
        'p_graduation_degree' => $formData['p_graduation_degree'],
        'p_graduation_college'=>$formData['p_graduation_college'],
        'other_degree'=>$formData['other_degree'],'other_college'=>$formData['other_college']  
    );
    
  
    
                
           
            $res = $agentModel->updateAgent($formdata,$agent_id);    
            
            if ($res ==0){
                $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'Details could not be added. Try again Later.',
                    )
                );
                 
                } else if($res==1){
                     $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'Education details successfully added',
                    )
                );
                     $this->session->step_two = 1;
                      $this->_redirect($this->formatURL('/signup/addidentification'));
                      
                      
                    
                     $this->sendMobActivationCode($data);
            }                                
          
                
            }
            
            $this->view->error = TRUE;
        } 
        $agent_id = $this->session->agent_id;
       if ($agent_id){
           $agentsModel = new Agents();
           $row = $agentsModel->findById($agent_id);
           
           $form->populate($row->toArray());
           $this->view->item = $row;
       }
       $row = $this->_request->getPost();
       $form->populate($row);
        $this->view->item = $row;
        
       
        $this->view->agent_id = $agent_id;
        $this->view->form = $form;
        
     }
     else {
         $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'Please add basic details first',
                    )
                );
//                      $this->_redirect('/signup/add');
                      $this->_redirect($this->formatURL('/signup/add'));
     }
         
       
     }
     //Identification
      public function addidentificationAction(){
          
         if (isset($this->session->step_two)){
          $request = $this->getRequest(); 
          $this->title = 'Add Identification Details';
          $agent_id = '';
          $agent_id = $this->session->agent_id;
          
          $config = App_DI_Container::get('ConfigObject');
          $objValidator = new Validator();
          $minAge = $config->system->agent->age->min;
          //$maxAge = $config->system->agent->age->max;
          $currDate = date('Y-m-d');
          $docModel = new Documents();
          $form = new AgentidForm();
          $agentModel = new AgentUser();
          $errMsg = '';
          $isError = FALSE;
          $uploadlimit = $config->operation->uploadfile->size;
        
        if ($this->getRequest()->isPost()) {
            
            if($form->isValid($this->getRequest()->getPost())){
                $formData  = $this->_request->getPost();
                
               
                $formData['date_of_birth'] = Util::returnDateFormatted($formData['date_of_birth'], "d-m-Y", "Y-m-d", "-");
                $formData['passport_expiry'] = Util::returnDateFormatted($formData['passport_expiry'], "d-m-Y", "Y-m-d", "-");

                $datetime1 = date_create($currDate);
                $datetime2 = date_create($formData['date_of_birth']);
                $interval = date_diff($datetime1, $datetime2);
                $age = $interval->format('%y');

                if ($errMsg == '' && ($formData['gender'] == 'male' || $formData['gender'] == 'female') && $age < $minAge) {
                    $errMsg = 'Minimum age should be 18 years';
                }
                
                

                if ($errMsg == '' && $formData['Identification_type'] == 'passport') {
                    try {
                        // exit('here');

                        $isPassportValidId = $objValidator->validatePassport($formData['Identification_number']);
                        /***** checking identification number duplicacy *****/
                        $passportParams = array('idNo'=>$formData['Identification_number'], 
                                                'agentId'=>$agent_id, 
                                                'idType'=>$formData['Identification_type']
                                               );
                        $isIdDuplicate = $agentModel->checkIdNumberDuplication($passportParams);
                        
                    } catch (Exception $e) {
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                        $errMsg = $e->getMessage();
                    }
                }

                if ($errMsg == '' && $formData['address_proof_type'] == 'passport') {
                    try {

                        $isPassportValidAdd = $objValidator->validatePassport($formData['address_proof_number']);                        
                        /***** checking address proof number duplicacy *****/
                        $addressParams = array('addressProofNo'=>$formData['address_proof_number'], 
                                                'agentId'=>$agent_id, 
                                                'addressProofType'=>$formData['address_proof_type']
                                               );
                        
                        $isIdDuplicate = $agentModel->checkAddressNumberDuplication($addressParams);
                        
                    } catch (Exception $e) {
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                        $errMsg = $e->getMessage();
                    }
                }
                if ($errMsg == '' && $formData['Identification_type'] == 'uid') {
                    try {
                        $isUidValid = $objValidator->validateUID($formData['Identification_number']);
                        /***** checking identification number duplicacy *****/
                        $uidParams = array('idNo'=>$formData['Identification_number'], 
                                           'agentId'=>$agent_id, 
                                           'idType'=>$formData['Identification_type']
                                          );
                        $isUidDuplicate = $agentModel->checkIdNumberDuplication($uidParams);
                    } catch (Exception $e) {
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                        $errMsg = $e->getMessage();
                    }
                }
                if ($errMsg == '' && $formData['Identification_type'] == 'pan') {
                    try {

                        $isPanValidId = $objValidator->validatePAN($formData['Identification_number']);
                        /***** checking identification number duplicacy *****/
                        $panParams = array('idNo'=>$formData['Identification_number'], 
                                           'agentId'=>$agent_id, 
                                           'idType'=>$formData['Identification_type']
                                          );
                        $isUidDuplicate = $agentModel->checkIdNumberDuplication($panParams);
                    } catch (Exception $e) {
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                        $errMsg = $e->getMessage();
                    }
                }
                
                if ($errMsg == '' && $formData['pan_number_status'] == STATUS_ALREADY && trim($formData['pan_number']) == '') {
                    $errMsg = 'Please specify PAN Number!';
                } else if ($formData['pan_number_status'] == ucfirst(STATUS_APPLIED)) {
                    $panNumber = $formData['pan_number_status'];
                } else {
                    try {
                        $isPanValid = $objValidator->validatePAN($formData['pan_number']);
                        $panNumber = $formData['pan_number'];
                        /***** checking PAN duplicacy *****/
                        $panParams = array('pan'=>$formData['pan_number'], 
                                           'agentId'=>$agent_id                                          
                                          );
                        $isPANDuplicate = $agentModel->checkPANDuplication($panParams);
                    } catch (Exception $e) {
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                        $errMsg = $e->getMessage();
                    }
                }
                    
                    // getting files name 
                    $idDocFile = isset($_FILES['id_doc_path']['name'])?$_FILES['id_doc_path']['name']:'';
                    $addrDocFile = isset($_FILES['address_doc_path']['name'])?$_FILES['address_doc_path']['name']:'';

                    if(trim($idDocFile)=='')
                        unset($_FILES['id_doc_path']);
                    if(trim($addrDocFile)=='')
                        unset($_FILES['address_doc_path']);
                    
                    
                 /* Uploading Document File on Server */  
                 if ($errMsg == '' && ($idDocFile!='' || $addrDocFile!='')) {
                     
                     
                      /*** renaming upload files as same name files can also be upload successfully ***/
                        $i=1;                
                        foreach($_FILES as $file_elem_name=>$file_info){
                            if(trim($file_info['name'])!=''){
                                $filenameArr = explode(".", $file_info['name']);   
                                $ext = pathinfo($file_info['name'], PATHINFO_EXTENSION);
                                $newFilename = $filenameArr[0].$i.'.'.$ext;
                                $_FILES[$file_elem_name]['name'] = $newFilename;
                                $i++;
                            }
                        }
                        
                        
                  //upload files
                  $upload = new Zend_File_Transfer_Adapter_Http();     
                  
                  //Add Validators for uploaded file's extesion , mime type and size
                  $upload->addValidator('Extension', false, array('jpg','jpeg','pdf','case' => false))
                         ->addValidator('FilesSize',false,array('min' => '5kB', 'max' => $uploadlimit));
                    //->addValidator('MimeType', false, array('application/octet-stream','image/gif', 'image/jpg','image/bmp','application/pdf'));
                    //echo "<pre>";print_r($upload->getFileInfo());
               
                    $upload->setDestination(UPLOAD_PATH .'/');
                  
                try {
                    //All validations correct then upload file
                    if($upload->isValid()){
                        // upload received file(s)
                        $upload->receive();
                      
                        
                 $uploadedData = $form->getValues();
               
                 //print_r($uploadedData); exit;
                 
                
                
                 $id_doc_path = $this->_getParam('id_doc_path');
                 $id_doc_type = $this->_getParam('Identification_type');
                 $add_doc_path = $this->_getParam('address_doc_path'); 
                 $add_doc_type = $this->_getParam('address_proof_type');
                 
               
                
                //$doc_type_value = $this->_getParam($doc_type);
               
               $nameId = $upload->getFileName('id_doc_path');
               $nameAdd = $upload->getFileName('address_doc_path');
                //echo '<pre>';
                  //  print_r($upload);

                # Returns the size and destination folder for 'doc_path' named file element 
                
                //$upload->setOption(array('useByteString' => false));
                
                 /*** identification doc upload case ***/
                if(!empty($nameId)){

                    $destId = $upload->getDestination('id_doc_path');
                    $sizeId = $upload->getFileSize('id_doc_path');

                    // get the file name and extension
//                    $extId = explode(".", $nameId);
                    $extId = pathinfo($nameId, PATHINFO_EXTENSION);
                    $checkAgentDocId = $docModel->checkAgentDoc($agent_id, $id_doc_type);
                    if ($checkAgentDocId > 0) {
                        $docModel->updateDocs($checkAgentDocId);
                    }

                    // add document details along with agent id to DB
                     $dataID = array('doc_agent_id' => $agent_id, 'by_ops_id' => $user->id, 'ip' => $agentModel->formatIpAddress(Util::getIP()),
                     'doc_type' => $agent_id_doc_type, 'file_name' => '', 'file_type' => $extId['1'], 'status' => STATUS_ACTIVE);

                     $resId = $docModel->saveAgentDocs($dataID);
                     $renameFileId = $resId . '.' . $extId;

                     // rename the file and update the record
                     $dataArrId = array('file_name' => $renameFileId);
                     $updateId = $docModel->renameDocs($resId, $dataArrId);

                     // Rename uploaded file using Zend Framework
                     $fullFilePathId = $destId . '/' . $renameFileId;
                     $filterFileRenameId = new Zend_Filter_File_Rename(array('target' => $fullFilePathId, 'overwrite' => true));
                     $filterFileRenameId->filter($nameId);

                } 
                /*** identification doc upload case over ***/


                /*** address doc upload case ***/
                if(!empty($nameAdd)){

                $destAdd = $upload->getDestination('address_doc_path');
                $sizeAdd = $upload->getFileSize('address_doc_path');

                # Returns the mimetype for the 'doc_path' form element
                //$mimeType = $upload->getMimeType($doc_path);
                // get the file name and extension
//                   $extAdd = explode(".", $nameAdd);
                   $extAdd = pathinfo($nameAdd, PATHINFO_EXTENSION);
                    $checkAgentDocAdd = $docModel->checkAgentDoc($agent_id, $add_doc_type);
                    if ($checkAgentDocAdd > 0) {
                        $docModel->updateDocs($checkAgentDocAdd);
                    }

                // add document details along with agent id to DB
                $dataAdd = array('doc_agent_id' => $agent_id, 'by_ops_id' => $user->id, 'ip' => $agentModel->formatIpAddress(Util::getIP()),
                    'doc_type' => $add_doc_type, 'file_name' => '', 'file_type' => $extAdd['1'],
                    'status' => STATUS_ACTIVE);


                $resAdd = $docModel->saveAgentDocs($dataAdd);

                if ($nameId == $nameAdd) { // if names are same, update same id
                    $renameFileAdd = $resId . '.' . $extAdd;
                } else {
                    $renameFileAdd = $resAdd . '.' . $extAdd;
                }

                // rename the file and update the record
                $dataArrAdd = array('file_name' => $renameFileAdd);
                $updateAdd = $docModel->renameDocs($resAdd, $dataArrAdd);

                // Rename uploaded file using Zend Framework
                $fullFilePathAdd = $destAdd . '/' . $renameFileAdd;
                //echo 'ID'.$fullFilePathId;
                //echo 'ADDRESS'.$fullFilePathAdd;

                $filterFileRenameAdd = new Zend_Filter_File_Rename(array('target' => $fullFilePathAdd, 'overwrite' => true));
                $filterFileRenameAdd->filter($nameAdd);
              } 
             /*** address doc upload case over ***/

               $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'File uploaded successfully',
                       
                          )
                        );
               
                
                    } else {
                         $this->_helper->FlashMessenger(
                            array(
                                    'msg-error' => 'Documents could not be uploaded. Allowed Formats are Jpeg,Jpg,Pdf only.',
                                 )
                                );
                         $isError = TRUE;
                    }
                    
                
                } catch (Zend_File_Transfer_Exception $e) {
                   App_Logger::log($e->getMessage(), Zend_Log::ERR);
                   //echo '----------------------';exit;
                  $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => $e->getMessage(),
                          )
                        );
                        $isError = TRUE;
                     
                }
                // end of upload files
             } 
                 
                 
                 /******** updating form details in db if no error is there *******/
                if(!$isError && $errMsg==''){ 
                      $formdata = array('date_of_birth' => $formData['date_of_birth'],
                                        'gender' =>$formData['gender'],
                                        'Identification_type' => $formData['Identification_type'],
                                        'Identification_number' =>$formData['Identification_number'],
                                        'passport_expiry'=>$formData['passport_expiry'] , 
                                        'address_proof_type' => $formData['address_proof_type'],
                                        'address_proof_number' =>$formData['address_proof_number'],
                                        'pan_number' => $panNumber
                                       );
                    try {  
                          $res = $agentModel->updateAgent($formdata,$agent_id);
                          $this->_helper->FlashMessenger(
                                                           array(
                                                                   'msg-success' => 'Agent Id details successfully added',
                                                                )
                                                          );
                            $this->session->step_three =1;
                            $this->_redirect($this->formatURL('/signup/addaddress'));
                        }
                        catch(Exception $e){
                            App_Logger::log($e->getMessage(), Zend_Log::ERR);
                            $updateMsg = $e->getMessage();
                            $this->_helper->FlashMessenger(
                            array(
                                  'msg-error' => $updateMsg,
                                 )
                            );
                            //$isError = TRUE;
                       }
                } 
                     
                 
                if($errMsg!='') {
                    $this->_helper->FlashMessenger(
                            array(
                                'msg-error' => $errMsg,
                            )
                    );
                }
       
         
            }                                
            
            $this->view->error = TRUE;
        } 
         $agent_id = $this->session->agent_id;
       if ($agent_id){
           $agentsModel = new Agents();
           $row = $agentsModel->findById($agent_id);
           $form->populate($row->toArray());
           if($row->pan_number==ucfirst(STATUS_APPLIED))
               $panStatus = ucfirst(STATUS_APPLIED);
           else
               $panStatus = STATUS_ALREADY;
           
           $form->getElement("pan_number_status")->setValue($panStatus);
            
           $this->view->item = $row;
           
       }
        $row = $this->_request->getPost();
        if(!empty($row)){
            $form->populate($row);
            $this->view->item = $row;
        }
        $this->view->form = $form;
        $this->view->agent_id = $agent_id;
        
          }
          else {
         $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'Please add education details first',
                    )
                );
//                      $this->_redirect('/signup/addeducation');
                      $this->_redirect($this->formatURL('/signup/addeducation'));
     }
     }
     
     
     //Address
      public function addaddressAction(){
          
        
          if (isset($this->session->step_three)){
          $request = $this->getRequest(); 
          $this->title = 'Add Address Details';
        $form = new AgentaddressForm();
        $agentModel = new AgentUser();
        $state = new CityList(); 
        $agent_id = $this->session->agent_id;
        $agentDetailsArr = $agentModel->findById($agent_id);
        $profilePhotoName = AGENT_PROFILE_PHOTO_PREFIX.'_'.$agentDetailsArr['agent_code'];
        $isError=FALSE;
        $config = App_DI_Container::get('ConfigObject');
        $uploadlimit = $config->operation->uploadfile->size;
        
        if ($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())){
                            
              $formData  = $this->_request->getPost();
             //Upload Agent photo
             $profilePhotoFile = isset($_FILES['profile_pic']['name'])?$_FILES['profile_pic']['name']:'';      
                  
                  if(trim($profilePhotoFile)==''){
                    unset($_FILES['profile_pic']);
                    
                  }
                  if ($profilePhotoFile!='') {
                      
                        //upload files
                        $uploadphoto = new Zend_File_Transfer_Adapter_Http();     
                       
                        
                        // Add Validators for uploaded file's extesion , mime type and size
                        $uploadphoto->addValidator('Extension', false, array('jpg', 'jpeg','pdf','png'=>false))
                                ->addValidator('FilesSize', false, array('min' => '5kB', 'max' => $uploadlimit));
                       
                        $uploadphoto->setDestination(UPLOAD_PATH_AGENT_PHOTO);
                        
                        try{
                              
                                
                            //All validations correct then upload file
                            if ($uploadphoto->isValid()) {
                                
                                $uploadphoto->receive();
                              //  echo '<pre>';print_r($uploadphoto);exit;
                                 $namePhoto = $uploadphoto->getFileName('profile_pic');
                                 // get the file name and extension
//                                     $extPhoto = explode(".", $namePhoto);
                                     $extPhoto = pathinfo($namePhoto, PATHINFO_EXTENSION);
                                     $renameFilePhoto= $profilePhotoName. '.' . $extPhoto;
                                     
                                     $destPhoto = $uploadphoto->getDestination('profile_pic');
                                     
                                 // Rename uploaded file using Zend Framework
                                     $fullFilePathPhoto = $destPhoto . '/' . $renameFilePhoto;
//                                     echo $fullFilePathPhoto;exit;
                                     $filterFileRenamePhoto = new Zend_Filter_File_Rename(array('target' => $fullFilePathPhoto, 'overwrite' => true));
                                     $filterFileRenamePhoto->filter($namePhoto);
                                 
                              }
                            else {
                                    $this->_helper->FlashMessenger(
                                        array(
                                                'msg-error' => 'Profile photo could not be uploaded. Allowed Formats are Jpeg,Jpg,Pdf only.',
                                             )
                                    );
                                    $isError = TRUE;
                                }
                            
                      }catch (Zend_File_Transfer_Exception $e) {
                            App_Logger::log($e->getMessage(), Zend_Log::ERR);
                            $this->_helper->FlashMessenger(
                                    array(
                                        'msg-error' => $e->getMessage(),
                                    )
                            );
                             $isError = TRUE;
                        }
                            
                  }
 
                  
                  
                  
                  // End of Upload agent photo     
    $stateName =  $state->getStateName($formData['res_state']);
    $esstateName =  $state->getStateName($formData['estab_state']);
    $formdata = array(
        'profile_photo' => $renameFilePhoto,
        'res_type' => $formData['res_type'],
        'res_address1' =>$formData['res_address1'],
        'res_address2' => $formData['res_address2'],
        'res_city' =>$formData['res_city'],
        'res_taluka' => $formData['res_taluka'],'res_district' => $formData['res_district'],
        'res_state' => $stateName,
        'res_country' => $formData['res_country'],'res_pincode' => $formData['res_pincode'],
        'estab_name' => $formData['estab_name'],
        'estab_address1' =>$formData['estab_address1'],
        'estab_address2' => $formData['estab_address2'],
        'estab_city' =>$formData['estab_city'],
        'estab_taluka' => $formData['estab_taluka'],
        'estab_district' => $formData['estab_district'],'estab_state' => $esstateName,
        'estab_country' => $formData['estab_country'],'estab_pincode' => $formData['estab_pincode'],
        );
    
      
   
             if(!$isError){           
            $res = $agentModel->updateAgent($formdata,$agent_id);                
            if ($res ==0){
                $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'Details could not be added. Try again Later.',
                    )
                );
                 
                } else if($res==1){
                     $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'Address details successfully added',
                    )
                );
                     $this->session->step_four = 1;
//                      $this->_redirect('/signup/addbank');
                      $this->_redirect($this->formatURL('/signup/addbank'));
                      
                     $this->sendMobActivationCode($data);
            }                                
            }
                
            }
            
            $this->view->error = TRUE;
        } 
       
        $agent_id = $this->session->agent_id;
        if ($this->session->step_four){
           $agentsModel = new Agents();
           $row = $agentsModel->findById($agent_id);
            $row['res_state'] = $state->getStateCode($row['res_state']); 
            $row['estab_state'] = $state->getStateCode($row['estab_state']); 
           $form->populate($row->toArray());
          
           
           
       }
       else {
            $row = $form->getValues();
            $row['res_state'] = $state->getStateCode($row['res_state']); 
            $row['estab_state'] = $state->getStateCode($row['estab_state']); 
           
       }
       
         
          
           $form->getElement('pin')->setValue($row['res_pincode']);
           $form->getElement('estab_pin')->setValue($row['estab_pincode']);
           
           
           //$addArray = $row->toArray();
           $city = $row['res_city'];
           $form->getElement('city')->setValue($city);
           $form->getElement('es_city')->setValue($row['estab_city']);
           $this->view->item = $row;
           $this->view->form = $form;
           $this->view->res = $agent_id;
      }
        else {
         $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'Please add Identification details first',
                    )
                );
//                      $this->_redirect('/signup/addidentification');
                      $this->_redirect($this->formatURL('/signup/addidentification'));
     }
         
     }
     
     
     //Bank
      public function addbankAction(){
          if (isset($this->session->step_four)){
          $request = $this->getRequest(); 
          $this->title = 'Add Bank Details';
           $agent_id = '';
        $form = new AgentbankForm();
        $agentModel = new AgentUser();
        
        if ($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())){
                            
              $formData  = $this->_request->getPost();
              
   
    $formdata = array(
        'bank_name' =>$formData['bank_name'],
        'bank_account_number' => $formData['bank_account_number'],
        'bank_id' =>'',
        'bank_location' => $formData['bank_location'],'bank_city' => $formData['bank_city'],'bank_ifsc_code' => $formData['bank_ifsc_code'],
        'branch_id' => $formData['branch_id'],'bank_area' => $formData['bank_area'],'bank_branch_id' =>''
        );
    
   
    
              $agent_id = $this->session->agent_id;
                      
            $res = $agentModel->updateAgent($formdata,$agent_id);                
            $updStatus = $agentModel->editAgent(array('enroll_status'=>STATUS_PENDING),$agent_id); 
            if ($res ==0){
                $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'Details could not be added. Try again.',
                    )
                );
                 
                } else if($res==1){
                     $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'Bank details successfully added.Profile complete.',
                    )
                );
                      $agentModel->editAgent(array('enroll_status'=>STATUS_PENDING), $agent_id);
//                      $this->_redirect('/signup/detailscomplete');
                      $this->_redirect($this->formatURL('/signup/detailscomplete'));
                      
                     $this->sendMobActivationCode($data);
            }                                
          
                
            }
            
            $this->view->error = TRUE;
        } 
        
        $agent_id = $this->session->agent_id;
        $row = $this->_request->getPost();
        if(!empty($row)){
        $form->populate($row);
        $form->getElement('ifsc')->setValue($row['bank_ifsc_code']);
        }
      
        $this->view->item = $row;
        $this->view->form = $form;
        $this->view->agent_id = $agent_id;
          }
        else {
         $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'Please add address details first',
                    )
                );
//                      $this->_redirect('/signup/addaddress');
                      $this->_redirect($this->formatURL('/signup/addaddress'));
     }
         
     }
     public function detailscompleteAction(){
        
         $this->view->msg = 'Congratulations! You have completed business Partner registration activity. Please handover application form and documents to TranServ representative. You will hear from us in next 7 working days.'; 
         $agentModel = new Agents();
         $agentuserModel = new AgentUser();
         $param = $agentModel->findById($this->session->agent_id);
         $m = new App\Messaging\MVC\Axis\Agent();

             try {
             
         $m->agentCreation(array(
                 'name'   => $param->name,                 
                 'email'        => $param->email,
                 'mobile1'        => $param->mobile1,
                 'agent_code'     => $agentuserModel->getAgentCode($this->session->agent_id)), 'operation');
         
         
         unset($this->session->agent_id);
         unset($this->session->step_one);
         unset($this->session->step_two);
         unset($this->session->step_three);
         unset($this->session->step_four);
         }
             catch (Exception $e ) {    
                                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                                $errMsg = $e->getMessage();
                                $this->_helper->FlashMessenger(
                                    array(
                                        'msg-error' => $errMsg,
                                    )
                                );
                    } 
       
     }
     
     
     
      
}
