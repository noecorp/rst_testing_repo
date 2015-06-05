 <?php
/**
 * Allow the admins to manage agents actions in ops etc.
 *
 * @category backoffice
 * @package backoffice_controllers
 * @copyright company
 */

class AgentsController extends App_Operation_Controller
{
    /**
     * Overrides Zend_Controller_Action::init()
     *
     * @access public
     * @return void
     */
    
    private $session;
    
    public function init(){
        // init the parent
        parent::init();
        
        $this->session = new Zend_Session_Namespace("App.Operation.Controller");
    }
    
    public function indexAction() {
         unset($this->session->agent_id);
         unset($this->session->step_one);
         unset($this->session->step_two);
         unset($this->session->step_three);
         unset($this->session->step_four);
    }

  
    public function addAction(){
        
       
        
        $this->title = 'Add Agent Basic Details';
        $user = Zend_Auth::getInstance()->getIdentity();
        $form = new AgentForm();
        $agentModel = new AgentUser();
        $res = '';
        $agent_id = '';
        $agent_id = $this->session->agent_id ;
        
        if ($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())){
               $formData  = $this->_request->getPost();
         
            $landline = $formData['std'].'-'.$formData['mobile2'];      
             $data = array( 'title' => $formData['title'], 
                           'email'=>$formData['email'],                           
                           'status'=>STATUS_UNBLOCKED,
                           'email_verification_status' => STATUS_PENDING,
                           'enroll_status' => STATUS_INCOMPLETE,
                           'mobile1'=>$formData['mobile1'],
                           'mobile2'=>$landline,
                           'first_name'=>$formData['first_name'], 
                           'middle_name'=>$formData['middle_name'],
                           'last_name'=>$formData['last_name'],
                           'reg_ops_id' => $user->id,
                           'ip' => $agentModel->formatIpAddress(Util::getIP()),
                           'reg_datetime' => new Zend_Db_Expr('NOW()')
                          );    
             
             $agn_info = array(
                               'afn'=>$formData['afn'],
                              // 'office'=>$formData['office'],
                               //'shop'=>$formData['shop'],
                               'email'=> $formData['email'],
                               'auth_email'=> $formData['auth_email'],
                               'date_created'=>  new Zend_Db_Expr('NOW()') ,
                               'mobile1'=>$formData['mobile1'],
                               'mobile2'=>$landline,
                               'first_name'=>$formData['first_name'], 
                               'middle_name'=>$formData['middle_name'],
                               'last_name'=>$formData['last_name'],
                               'by_ops_id' =>$user->id,
                               'status' => STATUS_ACTIVE,
                               'ip' => $agentModel->formatIpAddress(Util::getIP())
                     );

            
           
            
            if ( $agent_id >0){
                $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'Profile details cannot be altered here',
                    )
                );
                 
                } 
             else {

               
             try {           
            $res = $agentModel->signupAgent($data,$agn_info);  
            }
            catch(Exception $e){
                     App_Logger::log($e->getMessage(), Zend_Log::ERR);
                     $signupMsg = $e->getMessage();
                }

            
                
                if($res>0){
                     $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => "Agent basic details were successfully added",
                    )
                );
                   $this->session->agent_id = $res;
                   $this->session->step_one = 1;
                   //$this->sendMobActivationCode($data);
                   $this->_redirect($this->formatURL('/agents/addeducation/'));                     
            }   
            else {
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
       if ($agent_id){
           $agentsModel = new Agents();
           $row = $agentsModel->findById($agent_id);
           $form->populate($row->toArray());
       }
       else{
           $row = $form->getValues();
           $form->populate($row);
       }
           
           
           $alternatePhone = $row['mobile2'];
           $phoneArr = explode("-",$alternatePhone);
           if(count($phoneArr) > 1){
           
           $std = $phoneArr['0'];
           $phoneNum = $phoneArr['1'];
           
           $form->getElement('std')->setValue($std);
           $form->getElement('mobile2')->setValue($phoneNum);
           
           }
           $this->view->item = $row;
       
        
       
        $this->view->agent_id = $res;

        $this->view->form = $form;
        
        $form->getElement('mobile1')->setValue($this->session->mobile1);
        
        
        
        
    }
    
    //Education Details
      
     public function addeducationAction(){
        
          if (isset($this->session->step_one)){
          $request = $this->getRequest(); 
          $this->title = 'Add Agent Education Details';
          
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
                        'msg-error' => 'Education details could not be added. Try again Later.',
                    )
                );
                 
                } else if($res==1){
                     $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'Agent education details successfully added',
                    )
                );
                     $this->session->step_two = 1;
                      $this->_redirect($this->formatURL('/agents/addidentification/'));
                      
                      
                    
                     //$this->sendMobActivationCode($data);
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
                      $this->_redirect($this->formatURL('/agents/add/'));
     }
         
       
     }

     
     

//Identification
      public function addidentificationAction(){
          
           if (isset($this->session->step_two)){
          $request = $this->getRequest(); 
          $this->title = 'Add Agent Identification Details';
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

                if ($errMsg == '' && $age < $minAge) {
                    $errMsg = 'Minimum age should be 18 years.';
                }

                if ($errMsg == '' &&  $formData['Identification_type'] == 'passport') {
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
                  // $uploadInfo = $upload->getFileInfo();
                       
                  // Add Validators for uploaded file's extesion , mime type and size
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
                        $extId = explode(".", $nameId);

                        $checkAgentDocId = $docModel->checkAgentDoc($agent_id, $id_doc_type);
                        if ($checkAgentDocId > 0) {
                            $docModel->updateDocs($checkAgentDocId);
                        }

                        // add document details along with agent id to DB
                         $dataID = array('doc_agent_id' => $agent_id, 'by_ops_id' => $user->id, 'ip' => $agentModel->formatIpAddress(Util::getIP()),
                         'doc_type' => $agent_id_doc_type, 'file_name' => '', 'file_type' => $extId['1'], 'status' => STATUS_ACTIVE);

                         $resId = $docModel->saveAgentDocs($dataID);
                         $renameFileId = $resId . '.' . $extId['1'];

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
                       $extAdd = explode(".", $nameAdd);

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
                        $renameFileAdd = $resId . '.' . $extAdd['1'];
                    } else {
                        $renameFileAdd = $resAdd . '.' . $extAdd['1'];
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
               
                
                    }
                    else {
                        $this->_helper->FlashMessenger(
                                                        array(
                                                                'msg-error' => 'Documents could not be uploaded. Allowed Formats are Jpeg,Jpg,Pdf only.',
                                                             )
                                                      );
                        $isError = TRUE;
                    }
                
                } catch (Zend_File_Transfer_Exception $e) {
                  App_Logger::log($e->getMessage(), Zend_Log::ERR);
                  $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => $e->getMessage(),
                          )
                        );
                    $isError = TRUE;
                     
                }
             
              }
              
                // end of upload files
              
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
                            $this->_redirect($this->formatURL('/agents/addaddress'));
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
                        'msg-error' => 'Please add education details first.',
                    )
                );
                      $this->_redirect($this->formatURL('/agents/addeducation/'));
     }
     }

     
     //Address
      public function addaddressAction(){
          
          if (isset($this->session->step_three)){
          $request = $this->getRequest(); 
          $this->title = 'Add Agent Address Details';
//          $agent_id = '';
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
                                     $extPhoto = explode(".", $namePhoto);
                                     $renameFilePhoto= $profilePhotoName. '.' . $extPhoto['1'];
                                     
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
              $formData  = $this->_request->getPost();
              
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
                        'msg-success' => 'Address Details could not be added. Try again Later!',
                    )
                );
                 
                } else if($res==1){
                     $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'Agent Address details successfully Added.',
                    )
                );
                     $this->session->step_four = 1;
                      $this->_redirect($this->formatURL('/agents/addbank/'));
                      
            }                                
            }
                
            }
            
            $this->view->error = TRUE;
        } 
       
        $agent_id = $this->session->agent_id;
       
       
       /*$row = $this->_request->getPost();      
       if(!empty($row)){
       $row['res_state'] = $state->getStateCode($row['res_state']); 
       $row['estab_state'] = $state->getStateCode($row['estab_state']); 
       $form->populate($row);
       
        $this->view->item = $row;
        $this->view->form = $form;
       
        
        
        $addArray = $this->_request->getPost();
        $city = $addArray['res_city'];
        
        $form->getElement('city')->setValue($city);
        $form->getElement('es_city')->setValue($addArray['estab_city']);
       }*/
        
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
                        'msg-error' => 'Please add Identification details first.',
                    )
                );
                      $this->_redirect($this->formatURL('/agents/addidentification/'));
     }
         
     }
     
    public function detailscompleteAction(){
       
         $this->view->msg = 'Congratulations! Agent registered successfully.'; 
         $agentModel = new Agents();
         $agentuserModel = new AgentUser();
         $param = $agentModel->findById($this->session->agent_id);
         $m = new App\Messaging\MVC\Axis\Operation();
         
         $alert = new Alerts();
             try {
             
             $m->agentCreation(array(
                 'name'   => $param['name'],                 
                 'email'        => $param['email'],
                 'mobile1'        => $param['mobile1'],
                 'agent_code'     => $agentuserModel->getAgentCode($this->session->agent_id)), 'operation');
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
                    
         unset($this->session->agent_id);
         unset($this->session->step_one);
         unset($this->session->step_two);
         unset($this->session->step_three);
         unset($this->session->step_four);
         
         //$this->_redirect($this->formatURL('/approveagent/index/'));
     }
      
     
     
     
     //Bank
      public function addbankAction(){
          if (isset($this->session->step_four)){
          $request = $this->getRequest(); 
          $this->title = 'Add Agent Bank Details';
           $agent_id = '';
        $form = new AgentbankForm();
        $agentModel = new AgentUser();
        
        if ($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())){
                            
              $formData  = $this->_request->getPost();
              
   
    $formdata = array('fund_account_type' => $formData['fund_account_type'],
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
                        'msg-success' => 'Bank Details could not be added. Try again Later!',
                    )
                );
                 
                } else if($res==1){
                     $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'Agent Bank details successfully Added. Profile Complete',
                    )
                );
                     
                      $this->_redirect($this->formatURL('/agents/detailscomplete/'));
                      
                     $this->sendMobActivationCode($data);
            }                                
          
                
            }
            
            $this->view->error = TRUE;
        } 
        
        $agent_id = $this->session->agent_id;
       
        $row = $this->_request->getPost();
        $form->populate($row);
        $form->getElement('ifsc')->setValue($row['bank_ifsc_code']);
      
        $this->view->item = $row;
        $this->view->form = $form;
        $this->view->agent_id = $agent_id;
          }
        else {
         $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'Please add Address details first.',
                    )
                );
                      $this->_redirect($this->formatURL('/agents/addaddress/'));
     }
         
     }
     
     
     
     public function addocsAction(){
          
          
          $this->title = 'Add Agent Documents';
          
          $id = $this->_getParam('id');
          $agentModel = new Agents();
          $form = new AgentdocsForm();
          $docModel = new Documents();
          $config = App_DI_Container::get('ConfigObject');
          $user = Zend_Auth::getInstance()->getIdentity();
          $uploadlimit = $config->operation->uploadfile->size;
          $numOfUploads = $config->system->uploadfile->number;
          
          
          
          
          if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
                
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

                /* Uploading Document File on Server */
                $upload = new Zend_File_Transfer_Adapter_Http();
                
                $i = '';
                // Add Validators for uploaded file's extesion , mime type and size
               $upload->addValidator('Extension', false, array('jpg','jpeg' => false))
                    ->addValidator('FilesSize',false,array('min' => '5kB', 'max' => $uploadlimit));
                    //->addValidator('MimeType', false, array('application/octet-stream','image/gif', 'image/jpg','image/bmp','application/pdf'));
                //echo "<pre>";print_r($upload->getFileInfo());exit;
               
                $numDocs = sizeof($upload->getFileInfo());
               
                if ($numDocs >  $numOfUploads){
                    $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'Number of Allowed uploaded files exceeded.',
                       
                          )
                        );
                }
                else {
                    
                   $i=0;
                foreach($upload->getFileInfo() as $file => $info) 
                {
                       
              
                    
                    $upload->setDestination(UPLOAD_PATH .'/');
                  
                
                
                try {
                    //All validations correct then upload file
                    if($upload->isValid()){
                        // upload received file(s)
                        $upload->receive();
                       
                        
                 $uploadedData = $form->getValues();
                
                
                
                // functions for knowing about uploaded file 
                # Returns the file name for 'doc_path' named file element
                 $i == 0 ? $flg = '': $flg=$i;
                 $doc_path = 'doc_path'.$flg;
                 $doc_type = 'doc_type'.$flg;
                
                $doc_type_value = $this->_getParam($doc_type);
               
                $name = $upload->getFileName($doc_path);
               
                $checkAgentDocId = $docModel->checkAgentDoc($id, $doc_type_value);
                if($checkAgentDocId > 0){
                    $docModel->updateDocs($checkAgentDocId);
                }
                    

                # Returns the size and destination folder for 'doc_path' named file element 
                
                //$upload->setOption(array('useByteString' => false));
                
                 $dest = $upload->getDestination($doc_path);
                 $size = $upload->getFileSize($doc_path);

                # Returns the mimetype for the 'doc_path' form element
                $mimeType = $upload->getMimeType($doc_path);
                
               // get the file name and extension
               // $ext = explode(".",$name);
                $ext = pathinfo($name, PATHINFO_EXTENSION);

                // add document details along with agent id to DB
                $data = array('doc_agent_id'=>$id,'by_ops_id'=>$user->id,'ip'=>$agentModel->formatIpAddress(Util::getIP()),
                    'doc_type'=>$doc_type_value,'file_name'=>'','file_type'=>$ext['1'],
                    'status'=>STATUS_ACTIVE);  
               
                $res = $docModel->saveAgentDocs($data);
//                $renameFile = $res.'.'.$ext['1'];
                $renameFile = $res.'.'.$ext;
                // rename the file and update the record
                $dataArr = array('file_name'=>$renameFile);
                $update = $docModel->renameDocs($res,$dataArr);

               
                // Rename uploaded file using Zend Framework
                $fullFilePath = $dest.'/'.$renameFile;
                $filterFileRename = new Zend_Filter_File_Rename(array('target' => $fullFilePath, 'overwrite' => true));

                $filterFileRename -> filter($name);

               $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'File uploaded sucessfully: '.$info['name'],
                       
                          )
                        );
                
                    }
                    else {
                        $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'Documents could not be uploaded. Allowed Formats are Jpeg,Jpg only.',
                          )
                        );
                    }
                
                } catch (Zend_File_Transfer_Exception $e) {
                 echo $e->getMessage();
                 App_Logger::log($e->getMessage(), Zend_Log::ERR);
                }
                
               $i++;  

              

                }
            }
            $statusArr =  $agentModel->getStatus($id);
            if($statusArr['enroll_status'] == 'approved')
            $this->_redirect($this->formatURL('/agentsummary/view?id='.$id));
            else
            $this->_redirect($this->formatURL('/approveagent/view?id='.$id));
             }
             
            }
          $form->getElement('limit')->setValue($numOfUploads);
          $this->view->form = $form;
         
     }
     
     
    public function editAction(){
        $this->title = 'Edit Agent Basic Details';
        $user = Zend_Auth::getInstance()->getIdentity();
        $form = new EditAgentForm();
        
        
        $agentModel = new Agents();
        $formData  = $this->_request->getPost();
        $id = $this->_getParam('id');
        $this->session->agent_id = $id;
        $date = date('Y-m-d');
        $Corpflag = FALSE;
        $agentProductBinding = new BindAgentProductCommission();
        $agentProduct = $agentProductBinding->getAgentBinding($id, $date );
        if (!empty($agentProduct)){
        foreach($agentProduct as $agntprod){
            if($agntprod['program_type'] == PROGRAM_TYPE_CORP)
            {
                $Corpflag = TRUE;
                break;
            }
           
        }
        }
 
        
       
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
                
         $landline = $formData['std'].'-'.$formData['mobile2'];           
         $dataagents = array('first_name' => $formData['first_name'],
                    'middle_name' => $formData['middle_name'],
                    'last_name' => $formData['last_name'],
                    'email' => trim($formData['email']),
                    'mobile1' => $formData['mobile1'],
                    'mobile2' => $landline,
                    'centre_id' => $formData['centre_id'],
                    'terminal_id_tid_1' => $formData['terminal_id_tid_1'],
                    'terminal_id_tid_2' => $formData['terminal_id_tid_2'],
                    'terminal_id_tid_3' => $formData['terminal_id_tid_3'],
                    'institution_name' => $formData['institution_name'],
                    'ip' => $agentModel->formatIpAddress(Util::getIP()),
                    'bcagent' => $formData['bcagent']
                );
                $agentdetails = array(
                             'agent_id' => $id,
                             'first_name'=>$formData['first_name'], 
                             'middle_name'=>$formData['middle_name'],
                             'last_name'=>$formData['last_name'], 
                             'email'=>$formData['email'],
                             'auth_email'=>$formData['auth_email'],
                             'mobile1'=>$formData['mobile1'],
                             'mobile2'=>$landline,
                             //'office'=>$formData['office'],
                             //'shop'=>$formData['shop'],
                             'date_created'=>new Zend_Db_Expr('NOW()'),
                             'by_ops_id' =>$user->id,
                             'status' => STATUS_ACTIVE,
                             'ip' => $agentModel->formatIpAddress(Util::getIP())
                               );
                
          $allDetails = $agentModel->findagentDetailsById($id);
          
          $dataagentdetails = array_merge($allDetails, $agentdetails);
         
         
          unset($dataagentdetails['id']);
          
          $dataagents['Corpflag'] = $Corpflag;
          $agentmodel = $agentModel->updatedetails($dataagents,$dataagentdetails,$id);
       
          if ($agentmodel =='auth_email_req'){
               $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'Secondary email is mandatory for '.PROGRAM_TYPE_CORP.' products',
                    )
                );
               $this->_redirect($this->formatURL('/agents/edit?id='.$id));
          }
           if ($agentmodel =='mobile_dup' ){
                $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'This mobile is already registered!',
                    )
                );
                $this->_redirect($this->formatURL('/agents/edit?id='.$id));
                 
                }
                if ($agentmodel =='email_dup'){
                $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'Email already registered!',
                    )
                );
                 $this->_redirect($this->formatURL('/agents/edit?id='.$id));
                }
                 if ($agentmodel =='auth_email_dup'){
                $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'Auth Email already registered!',
                    )
                );
                 $this->_redirect($this->formatURL('/agents/edit?id='.$id));
                }
                
          if($agentmodel== 1){
                    $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'The Agent was successfully edited.',
                    )
                );
                   $this->session->agent_id = $id;
                   $detail_id =$form->getValue('agent_detail_id');
                   $inactiveArr = array('status' => STATUS_INACTIVE);
                   $agentmodel = $agentModel->inactiveAgentDetails($inactiveArr,$detail_id);
                   
            }
            else
                {
                    $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'The Agent details could not be edited.',
                    )
                );
            }
                
                
                
                $this->_redirect($this->formatURL('/agents/editeducation/'));
            }
        }else{
            
            
            if (!is_numeric($id)) {
                $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'The provided agent_id is invalid.',
                    )
                );
                
                $this->_redirect($this->formatURL('/agents/edit/'));
            }
            
            $row = $agentModel->findById($id);
            
            if (empty($row)) {
                $this->_helper->FlashMessenger(
                    array(
                        'msg-warning' => 'The requested agent_id could not be found.',
                    )
                );
                
               $this->_redirect($this->formatURL('/agents/edit/'));
            }
            
            
            $form->getElement('agent_detail_id')->setValue($row['row_id']);
            
            $form->populate($row->toArray());
            $this->view->item = $row;
        }
           $alternatePhone = $row['mobile2'];
           $phoneArr = explode("-",$alternatePhone);
           $std = $phoneArr['0'];
           $phoneNum = $phoneArr['1'];
           $row['mobile2'] = $phoneNum; 
           $form->getElement('std')->setValue($std);
           $form->getElement('mobile2')->setValue($phoneNum);
           $form->getElement('status')->setValue($row['enroll_status']);
          
        $this->view->agent_id = $id;
        $this->view->form = $form;
    }     

    public function editeducationAction(){
          if(!$this->session->agent_id)
              $this->_redirect($this->formatURL('/agentsummary/index/'));
              
        $this->title = 'Edit Agent Education Details';
        $user = Zend_Auth::getInstance()->getIdentity();
        $form = new AgenteduForm();
        $agentModel = new Agents();
        $formData  = $this->_request->getPost();
        $id = $this->session->agent_id;
        
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
                
                
         
         $agentdetails = array('education_level'=>$formData['education_level'],
             'matric_school_name' => $formData['matric_school_name'],
        'intermediate_school_name' =>$formData['intermediate_school_name'],
        'graduation_degree' => $formData['graduation_degree'],
        'graduation_college' =>$formData['graduation_college'],
        'p_graduation_degree' => $formData['p_graduation_degree'],
        'p_graduation_college'=>$formData['p_graduation_college'],
        'other_degree'=>$formData['other_degree'],'other_college'=>$formData['other_college'],
             'by_ops_id' =>$user->id,'ip' => $agentModel->formatIpAddress(Util::getIP())
                               );
         
         $allDetails = $agentModel->findagentDetailsById($id);
          
          $dataagentdetails = array_merge($allDetails, $agentdetails);
         
          if($formData['graduation_degree'] == '')unset($dataagentdetails['graduation_degree']);
          if($formData['graduation_college'] == '')unset($dataagentdetails['graduation_college']);
          if($formData['p_graduation_degree'] == '')unset($dataagentdetails['p_graduation_degree']);
          if($formData['p_graduation_college'] == '')unset($dataagentdetails['p_graduation_college']);
         
          unset($dataagentdetails['id']);       
             // print_r($dataagentdetails);
             // exit;
          $agentmodel = $agentModel->agupdateedudetails($dataagentdetails,$id);
                if($agentmodel){
                    $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'The Agent Education details was successfully edited.',
                    )
                );
                   $detail_id =$form->getValue('agent_detail_id');
                   $inactiveArr = array('status' => STATUS_INACTIVE);
                   $agentmodel = $agentModel->inactiveAgentDetails($inactiveArr,$detail_id);
            }
            else
                {
                    $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'The Agent details could not be edited.',
                    )
                );
            }
                
                
                //Regenerate Flag and Flippers
                App_FlagFlippers_Manager::save();
                
                $this->_redirect($this->formatURL('/agents/editidentification/'));
            }
        }else{
            
            
            if (!is_numeric($id)) {
                $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'The provided agent_id is invalid.',
                    )
                );
                
               $this->_redirect($this->formatURL('/agents/editeducation/'));
            }
            
            $row = $agentModel->findById($id);
            
            if (empty($row)) {
                $this->_helper->FlashMessenger(
                    array(
                        'msg-warning' => 'The requested agent_id could not be found.',
                    )
                );
                
                $this->_redirect($this->formatURL('/agents/editeducation/'));
            }
            
            
            
            $form->populate($row->toArray());
            $form->getElement('agent_detail_id')->setValue($row['row_id']);
           
            $this->view->item = $row;
        }
        $this->view->agent_id = $id;
        $this->view->form = $form;
    }    
    
    


     public function editidentificationAction() {
        $this->title = 'Edit Agent Identification details';
        $user = Zend_Auth::getInstance()->getIdentity();
        $form = new AgentidForm();
        $agentModel = new Agents();
        $agentUserModel = new AgentUser();
        $formData = $this->_request->getPost();
        $id = $this->session->agent_id;
        $objValidator = new Validator();
        $config = App_DI_Container::get('ConfigObject');
        $minAge = $config->system->agent->age->min;
        $maxAge = $config->system->agent->age->max;
        $currDate = date('Y-m-d');
        $errMsg = '';
        $isError=FALSE;
        
        $docModel = new Documents();
        $uploadlimit = $config->operation->uploadfile->size;
//        echo "<pre>";
//        print_r($formData);exit;

        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $formData['date_of_birth'] = Util::returnDateFormatted($formData['date_of_birth'], "d-m-Y", "Y-m-d", "-");
                $formData['passport_expiry'] = Util::returnDateFormatted($formData['passport_expiry'], "d-m-Y", "Y-m-d", "-");
                $datetime1 = date_create($currDate);
                $datetime2 = date_create($formData['date_of_birth']);
                $interval = date_diff($datetime1, $datetime2);
                $age = $interval->format('%y');

                if ($errMsg == '' && $age < $minAge) {
                    $errMsg = 'Minimum age should be 18 years.';
                }

                if ($errMsg == '' && $formData['Identification_type'] == 'passport') {
                    try {
                       //  exit('here');

                        $isPassportValidId = $objValidator->validatePassport($formData['Identification_number']);
                        /***** checking identification number duplicacy *****/
                        $passportParams = array('idNo'=>$formData['Identification_number'], 
                                                'agentId'=>$id, 
                                                'idType'=>$formData['Identification_type']
                                               );
                        
                        $isIdDuplicate = $agentUserModel->checkIdNumberDuplication($passportParams);
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
                                                'agentId'=>$id, 
                                                'addressProofType'=>$formData['address_proof_type']
                                               );
                        
                        $isIdDuplicate = $agentUserModel->checkAddressNumberDuplication($addressParams);
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
                                           'agentId'=>$id, 
                                           'idType'=>$formData['Identification_type']
                                          );
                        $isUidDuplicate = $agentUserModel->checkIdNumberDuplication($uidParams);
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
                                           'agentId'=>$id, 
                                           'idType'=>$formData['Identification_type']
                                          );
                        $isUidDuplicate = $agentUserModel->checkIdNumberDuplication($panParams);
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
                                           'agentId'=>$id                                          
                                          );
                        $isPANDuplicate = $agentUserModel->checkPANDuplication($panParams);
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
//echo '<!-- '.$errMsg. $idDocFile .' -- >';
                
                
                 /* Uploading Document File on Server */  
                 if ($errMsg == '' && ($idDocFile!='' || $addrDocFile!='')) {
                  //  if ($agentmodel == 'updated') {
//echo '<!-- here -- >';
                     
                        /*** renaming upload files as same name files can also be upload successfully ***/
                        $i=1;                
                        foreach($_FILES as $file_elem_name=>$file_info){
                            if(trim($file_info['name'])!=''){
                                $filenameArr = explode(".", $file_info['name']);   
                                //$newFilename = $filenameArr[0].$i.'.'.$filenameArr[1];
                                $ext = pathinfo($file_info['name'], PATHINFO_EXTENSION);
                                $newFilename = $filenameArr[0].$i.'.'.$ext;
                                $_FILES[$file_elem_name]['name'] = $newFilename;
                                $i++;
                            }
                        }
                      
                        
                        //upload files
                        $upload = new Zend_File_Transfer_Adapter_Http();     
                       // $uploadInfo = $upload->getFileInfo();
                        
                        // Add Validators for uploaded file's extesion , mime type and size
                        $upload->addValidator('Extension', false, array('jpg', 'jpeg','pdf','png'=>false))
                                ->addValidator('FilesSize', false, array('min' => '5kB', 'max' => $uploadlimit));
                       
                        $upload->setDestination(UPLOAD_PATH);


                        try {  
                            //All validations correct then upload file
                            if ($upload->isValid()) {
                               
                                // upload received file(s)
                                $upload->receive();

                                $uploadedData = $form->getValues();

                                $id_doc_path = $this->_getParam('id_doc_path');
                                $id_doc_type = $this->_getParam('Identification_type');
                                $add_doc_path = $this->_getParam('address_doc_path');
                                $add_doc_type = $this->_getParam('address_proof_type');

                                //$doc_type_value = $this->_getParam($doc_type);

                                $nameId = $upload->getFileName('id_doc_path');
                                $nameAdd = $upload->getFileName('address_doc_path');
                               
                                //echo '<pre>';
                                //  print_r($upload);
                                //$arr = $upload->getFileInfo('id_doc_path');
                                //$arr1 = $upload->getFileInfo('address_doc_path');
                                # Returns the size and destination folder for 'doc_path' named file element 
                                //$upload->setOption(array('useByteString' => false));
                                
                                /*** identification doc upload case ***/
                                if(!empty($nameId)){
                                    
                                    $destId = $upload->getDestination('id_doc_path');
                                    $sizeId = $upload->getFileSize('id_doc_path');
                                    
                                    // get the file name and extension
//                                    $extId = explode(".", $nameId);
                                    $extId = pathinfo($nameId, PATHINFO_EXTENSION);
                                    $checkAgentDocId = $docModel->checkAgentDoc($id, $id_doc_type);
                                    if ($checkAgentDocId > 0) {
                                        $docModel->updateDocs($checkAgentDocId);
                                    }
                                    
                                    // add document details along with agent id to DB
                                     $dataID = array('doc_agent_id' => $id, 'by_ops_id' => $user->id, 'ip' => $agentModel->formatIpAddress(Util::getIP()),
                                     'doc_type' => $id_doc_type, 'file_name' => '', 'file_type' => $extId['1'], 'status' => STATUS_ACTIVE);
                                    
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
//                                   $extAdd = explode(".", $nameAdd);
                                   $extAdd = pathinfo($nameAdd, PATHINFO_EXTENSION);
                                    $checkAgentDocAdd = $docModel->checkAgentDoc($id, $add_doc_type);
                                    if ($checkAgentDocAdd > 0) {
                                        $docModel->updateDocs($checkAgentDocAdd);
                                    }

                                // add document details along with agent id to DB
                                $dataAdd = array('doc_agent_id' => $id, 'by_ops_id' => $user->id, 'ip' => $agentModel->formatIpAddress(Util::getIP()),
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
                                            'msg-success' => 'File uploaded sucessfully',
                                        )
                                );
                            }
                            else {
                                    $this->_helper->FlashMessenger(
                                        array(
                                                'msg-error' => 'Documents could not be uploaded. Allowed Formats are Jpeg,Jpg,Pdf only.',
                                             )
                                    );
                                    $isError = TRUE;
                                }
                        } catch (Zend_File_Transfer_Exception $e) {
                            App_Logger::log($e->getMessage(), Zend_Log::ERR);
                            $this->_helper->FlashMessenger(
                                    array(
                                        'msg-error' => $e->getMessage(),
                                    )
                            );
                             $isError = TRUE;
                        }

                 }

                        // end of upload files
                        
                      /******** updating form details in db if no error is there *******/
                      if(!$isError && $errMsg==''){   
                        $agentdetails = array(
                        'date_of_birth' => $formData['date_of_birth'],
                        'gender' => $formData['gender'],
                        'Identification_type' => $formData['Identification_type'],
                        'Identification_number' => $formData['Identification_number'],
                        'passport_expiry' => $formData['passport_expiry'],
                        'address_proof_type' => $formData['address_proof_type'],
                        'address_proof_number' => $formData['address_proof_number'],
                        'pan_number' => $panNumber,
                        'by_ops_id' => $user->id,
                        'ip' => $agentModel->formatIpAddress(Util::getIP())
                    );

                    $allDetails = $agentModel->findagentDetailsById($id);

                    $dataagentdetails = array_merge($allDetails, $agentdetails);
//echo '<!-- HEI -- >';
                    unset($dataagentdetails['id']);
                    try {
                        $agentmodel = $agentModel->agupdatedetails($dataagentdetails, $id);
          $detail_id = $form->getValue('agent_detail_id');
                        $inactiveArr = array('status' => STATUS_INACTIVE);
                        $agentmodel = $agentModel->inactiveAgentDetails($inactiveArr, $detail_id);
                        $this->_helper->FlashMessenger(
                                array(
                                    'msg-success' => 'The Agent ID details were successfully edited.',
                                )
                        );

                        $this->_redirect($this->formatURL('/agents/editaddress/'));
                    } catch (Exception $e) {
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                        $errMsg = $e->getMessage();
                    }
                } 
                   
                     if($errMsg!='') {
                        $this->_helper->FlashMessenger(
                                array(
                                    'msg-error' => $errMsg,
                                )
                        );
                    }
                  
     } } else {

            if (!is_numeric($id)) {
                $this->_helper->FlashMessenger(
                        array(
                            'msg-error' => 'The provided agent_id is invalid.',
                        )
                );
            }
        
            $row = $agentModel->findById($id);

            if (empty($row)) {
                $this->_helper->FlashMessenger(
                        array(
                            'msg-warning' => 'The requested agent_id could not be found.',
                        )
                );
            }
            if ($row['pan_number'] == ucfirst(STATUS_APPLIED))
                $panStatus = ucfirst(STATUS_APPLIED);
            else
                $panStatus = STATUS_ALREADY;

            $form->getElement("pan_number_status")->setValue($panStatus);
            $form->getElement('agent_detail_id')->setValue($row['row_id']);
            $form->populate($row->toArray());
            $this->view->item = $row;
        }
        $this->view->agent_id = $id;
        $this->view->form = $form;
    }    
    
    public function editaddressAction(){
        $this->title = 'Edit Agent Address Details';
        $user = Zend_Auth::getInstance()->getIdentity();
        $form = new AgentaddressForm();
        $agentModel = new Agents();
        $formData  = $this->_request->getPost();
        $id = $this->session->agent_id;
        $state = new CityList(); 
        $agentDetailsArr = $agentModel->findById($id);
        $profilePhotoName = AGENT_PROFILE_PHOTO_PREFIX.$agentDetailsArr['agent_code'];
        $isError=FALSE;
        $config = App_DI_Container::get('ConfigObject');
        $uploadlimit = $config->operation->uploadfile->size;
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
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
                                ->addValidator('FilesSize', false, array('min' => '2kB', 'max' => $uploadlimit));
                       
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
         $agentdetails = array(
        'profile_photo' => $renameFilePhoto,     
        'res_type' => $formData['res_type'],
        'res_address1' =>$formData['res_address1'],
        'res_address2' => $formData['res_address2'],
        'res_city' =>$formData['res_city'],
        'res_taluka' => $formData['res_taluka'],'res_district' => $formData['res_district'],'res_state' => $stateName,
        'res_country' => $formData['res_country'],'res_pincode' => $formData['res_pincode'],
        'estab_name' => $formData['estab_name'],
        'estab_address1' =>$formData['estab_address1'],
        'estab_address2' => $formData['estab_address2'],
        'estab_city' =>$formData['estab_city'],
        'estab_taluka' => $formData['estab_taluka'],
        'estab_district' => $formData['estab_district'],'estab_state' => $esstateName,
        'estab_country' => $formData['estab_country'],'estab_pincode' => $formData['estab_pincode'],
        'by_ops_id' =>$user->id,'ip' => $agentModel->formatIpAddress(Util::getIP())
                               );
                
         $allDetails = $agentModel->findagentDetailsById($id);
          
          $dataagentdetails = array_merge($allDetails, $agentdetails);
          if(!$isError){      
          unset($dataagentdetails['id']);      
          $agentmodel = $agentModel->adressupdatedetails($dataagentdetails,$id);
                if($agentmodel){
                    $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'The Agent Address details were successfully edited.',
                    )
                );
                   $detail_id =$form->getValue('agent_detail_id');
                   
                   $inactiveArr = array('status' => STATUS_INACTIVE);
                   $agentmodel = $agentModel->inactiveAgentDetails($inactiveArr,$detail_id);
            }
            else
                {
                    $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'The Agent Address details could not be edited.',
                    )
                );
            }
              
                
                //Regenerate Flag and Flippers
                App_FlagFlippers_Manager::save();
                
                $this->_redirect($this->formatURL('/agents/editbank/'));
            }
            }
        }else{
            
            
            if (!is_numeric($id)) {
                $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'The provided agent_id is invalid.',
                    )
                );
                
               //$this->_redirect('/system/editaddress');
            }
            
            $row = $agentModel->findById($id);
            
            if (empty($row)) {
                $this->_helper->FlashMessenger(
                    array(
                        'msg-warning' => 'The requested agent_id could not be found.',
                    )
                );
                
                //$this->_redirect('/system/editaddress');
            }
            $addArray = $row->toArray();
            $city = $addArray['res_city'];
            $form->getElement('agent_detail_id')->setValue($row['row_id']);
           
            $row['res_state'] = $state->getStateCode($row['res_state']); 
            $row['estab_state'] = $state->getStateCode($row['estab_state']);
            $form->getElement('pin')->setValue($row['res_pincode']);
            $form->getElement('estab_pin')->setValue($row['estab_pincode']);
            $form->populate($row->toArray());
            $this->view->item = $row;
             $form->getElement('city')->setValue($city);
             $form->getElement('es_city')->setValue($addArray['estab_city']);
        }
        $this->view->agent_id = $id;
        $this->view->form = $form;
    }    
    
    

     
      public function editbankAction(){
        $this->title = 'Edit Agent Bank Details';
        $user = Zend_Auth::getInstance()->getIdentity();
        $form = new AgentbankForm();
        $agentModel = new Agents();
        $formData  = $this->_request->getPost();
        $id = $this->session->agent_id;
        
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
                
                
         
         $agentdetails = array('fund_account_type' => $formData['fund_account_type'],
        'bank_name' =>$formData['bank_name'],
        'bank_account_number' => $formData['bank_account_number'],
        'bank_location' => $formData['bank_location'],'bank_city' => $formData['bank_city'],'bank_ifsc_code' => $formData['bank_ifsc_code'],
        'branch_id' => $formData['branch_id'],'bank_area' => $formData['bank_area'],'by_ops_id' =>$user->id,'ip' => $agentModel->formatIpAddress(Util::getIP())
                 );
                
          $allDetails = $agentModel->findagentDetailsById($id);
          
          
          $dataagentdetails = array_merge($allDetails, $agentdetails);
          //  echo '<pre>';print_r($dataagentdetails );        exit;   
          unset($dataagentdetails['id']);     
          
          $agentmodel = $agentModel->bankupdatedetails($dataagentdetails,$id);
                if($agentmodel){
                    $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'The Agent Bank details were successfully edited.',
                    )
                );
                   $detail_id =$form->getValue('agent_detail_id');
                   $inactiveArr = array('status' => STATUS_INACTIVE);
                   $agentmodel = $agentModel->inactiveAgentDetails($inactiveArr,$detail_id);
                   unset($this->session->agent_id);
                   $this->_redirect($this->formatURL('/agentsummary/index/'));
            }
            else
                {
                    $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'The Agent details could not be edited.',
                    )
                );
                    
            }
                
                
                //Regenerate Flag and Flippers
                App_FlagFlippers_Manager::save();
                
                //$this->_redirect('/system/editbank');
            }
        }else{
            
            
            if (!is_numeric($id)) {
                $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'The provided agent_id is invalid.',
                    )
                );
                
                //$this->_redirect('/system/editbank');
            }
            
            $row = $agentModel->findById($id);
            
            if (empty($row)) {
                $this->_helper->FlashMessenger(
                    array(
                        'msg-warning' => 'The requested agent_id could not be found.',
                    )
                );
                
                //$this->_redirect('/system/editbank');
            }
            
            $form->getElement('agent_detail_id')->setValue($row['row_id']);
            $form->getElement('ifsc')->setValue($row['bank_ifsc_code']);
            $form->populate($row->toArray());
            $this->view->item = $row;
        }
        $this->view->agent_id = $id;
        $this->view->form = $form;
        
    }    
    

    
    public function signupAction(){
        $this->title = 'Agent Signup, Phone Verification';
         
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
                {
                    $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'Please check SMS on your mobile to get Verification Code.',
                    )
                );
                    $this->session->mobile1=$form->getValue('phone') ;
                    
                    //Generate random verification code and store it in a session and send it to mobile phone in  SMS
                    $alerts = new Alerts();
                    $verificationCode = $alerts->generateAuthCode();
                    
                    $this->session->ver_code = $verificationCode;
                   
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
//                    $this->_redirect('/agents/verification/');
                    $this->_redirect($this->formatURL('/agents/verification/'));
                    
                   
                }
                
            }
        }
        $this->view->form = $form;
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
//                   $this->_redirect('/system/add');
                   $this->_redirect($this->formatURL('/agents/add/'));
               }
               else
               {
                
                   
                   $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'Incorrect Verification code entered.',
                    )
                );
               }
            }
            
            
        } 
        
        $this->view->form = $form;
    }    
     public function addauthemailAction(){
         $this->title = 'Agent Auth Email';
         
        // Agent phone entry form.
        $form = new AgentAuthEmailForm();  
        $formData  = $this->_request->getPost();
        $id = $this->_getParam('id');
        $agentModel = new Agents();
        $agentDetails = $agentModel->findagentDetailsById($id);
         if ($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())){
                  try{
                   $data = $agentDetails;
                   unset($data['id']);
                   $data['ip'] = $agentModel->formatIpAddress(Util::getIP());
                   $data['by_ops_id'] = $user->id;
                   $data['auth_email'] = $formData['auth_email'];
                   $update = $agentModel->agupdateauthemail($data,$id);
                   $detail_id = $agentDetails['id'];
                   $inactiveArr = array('status' => STATUS_INACTIVE);
                   $agentmodel = $agentModel->inactiveAgentDetails($inactiveArr,$detail_id);
               
                if ( $update == 'updated'){
             
             $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'Secondary email updated successfully',
                    )
                );
             $this->_redirect($this->formatURL('/approvedagent/index?id='.$id)); 
                }
                else{
                   $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'Secondary email could not be updated',
                    )
                );  
                }
            }catch (Exception $e ) {    
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
            
       $this->view->name = $agentDetails['first_name'].' '.$agentDetails['last_name'];
       $this->view->form = $form;  
     }
     
     Public function agentbalancealertAction(){
         
        $this->title = 'Agent Balance Alert';

        $data['searchCriteria'] = $this->_getParam('searchCriteria');
        $data['keyword'] = $this->_getParam('keyword');
        $data['sub'] = $this->_getParam('sub');

        $agentModel = new Agents();
        $agentUserModel = new AgentUser();
        $form = new AgentSearchForm(array('action' => $this->formatURL('/agents/agentbalancealert'),
            'method' => 'POST',
        ));
        $form->getElement('searchCriteria')->setValue( $data['searchCriteria']);
        $form->getElement('keyword')->setValue( $data['keyword']);
        if ($data['sub'] != '') {

            $this->view->paginator = $agentModel->getAgentBalanceLimitDetails($data, $this->_getPage());
            $form->populate($data);
        }

        $this->view->backLink = 'searchCriteria=' . $data['searchCriteria'] . '&keyword=' . $data['keyword'] . '&sub=1';
        $this->view->controllerName = Zend_Registry::get('controllerName');
        $this->view->form = $form;
        $this->view->agentUser = $agentUserModel;
         
     }
     
     public function addagentbalancealertAction()
    {   
        $agentModel = new Agents();
        $user = Zend_Auth::getInstance()->getIdentity();
        $id = $this->_getParam('id');
        $agent_id = $this->_getParam('agent_id');
        $min_amount_alert = $this->_getParam('min_amount_alert');
        $row = $agentModel->findById($agent_id);
        $agent_code = $row['agent_code'];
        $agentSummery = $row['name'].'('.$row['agent_code'].')';
        $pageTitle = 'Agent Balance Alert :'.$agentSummery;
       // Get our form and validate it
        $form = new AgentBalanceAlertForm(array('action' => $this->formatURL('/agents/addagentbalancealert'),
                                           'method' => 'POST',
                                    )); 
         $form->getElement('id')->setValue($id);
         $form->getElement('agent_id')->setValue($agent_id);
         $form->getElement('min_amount_alert')->setValue($min_amount_alert);
        
        //$formData  = $this->_request->getPost();//$form->getValues();//
        $qurStr['id'] = $this->_getParam('id');
        $qurStr['agent_id'] = $this->_getParam('agent_id');
        $qurStr['min_amount_alert'] = $this->_getParam('min_amount_alert');
        $qurStr['sub'] = $this->_getParam('sub');
       
         
         if($qurStr['sub']!=''){             
              if($form->isValid($this->getRequest()->getPost())){
                  try{
                 $qurData['id'] = $qurStr['id'];
                 $qurData['agent_id'] = $qurStr['agent_id'];
                 $qurData['min_amount_alert'] = $qurStr['min_amount_alert'];
                  if ( $qurData['agent_id'] == ''){
                    $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'Invalid agent.',
                    )
                );
                $this->_redirect($this->formatURL('/agents/agentbalancealert?searchCriteria=agent_code&keyword='.$agent_code));  
                  }elseif ( $qurData['min_amount_alert'] == ''){
                    $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'Invalid agent alert amount.',
                    )
                );
                $this->_redirect($this->formatURL('/agents/addagentbalancealert?id='.$id.'&agent_id='.$agent_id.'&min_amount_alert='.min_amount_alert));   
                  }
                  
                 //edit and insert alert amount for this agent 
                  
                   
                 $checkdetail = array(
                     'agent_id'=>$agent_id
                 ) ;
                  
                  
                 $alertBalance = $agentModel->chkAgentalertDetail($checkdetail); 
                 if(!$alertBalance){
                        $data = array(
                           'agent_id' => $qurData['agent_id'], 
                           'value'=>$qurData['min_amount_alert'],                                         'status'=>AGENT_ACTIVE_STATUS,
                           'currency' =>'INR',
                           'type'=>SETTING_AGENT_MIN_BALANCE,
                           'by_ops_id' => $user->id,
                           'ip' => $agentModel->formatIpAddress(Util::getIP()),
                           'date_created' => new Zend_Db_Expr('NOW()')
                          ); 
                        
                      
                    $saveRecord = $agentModel->saveAgentAlertDetail($data);
                    if($saveRecord){
                      $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => $agentSummery.' balance alert successfully added.',
                    )
                );
                $this->_redirect($this->formatURL('/agents/agentbalancealert?searchCriteria=agent_code&sub=1&keyword='.$agent_code));      
                    }
                 }else{
                      $data = array(
                           'id' => $qurData['id'], 
                           'agent_id' => $qurData['agent_id'], 
                           'value'=>$qurData['min_amount_alert'],                                         'status'=>AGENT_ACTIVE_STATUS,
                           'currency' =>'INR',
                           'type'=>SETTING_AGENT_MIN_BALANCE,
                           'by_ops_id' => $user->id,
                           'ip' => $agentModel->formatIpAddress(Util::getIP()),
                           'date_created' => new Zend_Db_Expr('NOW()')
                          ); 
                      
                     
                     $updateRecord = $agentModel->updateAgentAlertDetail($data);
                    if($updateRecord){
                      $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => $agentSummery.' balance alert successfully updated.',
                    )
                );
                $this->_redirect($this->formatURL('/agents/agentbalancealert?searchCriteria=agent_code&sub=1&keyword='.$agent_code));  
                    }else{
                      $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'Unable to update agent balance alert amount.',
                    )
                );
                $this->_redirect($this->formatURL('/agents/addagentbalancealert?id='.$id.'&agent_id='.$agent_id.'&min_amount_alert='.min_amount_alert));      
                    }
                 }
                }catch (Exception $e ) {    
                                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                                $errMsg = $e->getMessage();
                                $this->_helper->FlashMessenger(
                                    array(
                                        'msg-error' => $errMsg,
                                    )
                                );
                    } 
              }
          }elseif ( $agent_id == ''){
                    $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'Invalid agent.',
                    )
                );
                $this->_redirect($this->formatURL('/agents/agentbalancealert?searchCriteria=agent_code&keyword='.$agent_code));  
                  }
            $this->view->form = $form;
            $this->view->title = $pageTitle;
          //  $this->view->formData = $qurStr; 
            //$this->view->callingRprtDur = $qurStr['dur'];
            //$this->view->src = $src;
        }
}
