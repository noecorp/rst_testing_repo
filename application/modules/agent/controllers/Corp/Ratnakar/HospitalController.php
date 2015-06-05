<?php
/**
 * Corp Ratnakar Default Entry Point
 *
 * @author Vikram
 */
class Corp_Ratnakar_HospitalController extends App_Agent_Controller
{
    //put your code here


    public function init()
    {
        parent::init();
    }    

    /**
     * Controller's entry point
     *
     * @access public
     * @return void
     */
    public function indexAction(){
       
    }
    
    
    /** searchAction will search hospital and tid with hid,tid,
     *  hospital name, pincode, state and city
     */
    public function searchAction(){
       
        $this->title = 'Hospital';

        $data['hospital_id_code'] = $this->_getParam('hospital_id_code');
        $data['terminal_id_code'] = $this->_getParam('terminal_id_code');
        $data['hospital_name'] = $this->_getParam('hospital_name');
        $data['pin_code'] = $this->_getParam('pin_code');
        $data['state'] = $this->_getParam('state');
        $data['city'] = $this->_getParam('city');
        $param = $data; 
        $data['csrfhash'] = $this->_getParam('csrfhash');
        $data['formName'] = $this->_getParam('formName');
        $data['submit_form'] = $this->_getParam('submit_form');
        $objHospital = new Corp_Ratnakar_Hospital();
        $state = new CityList();
        
        $form = new Corp_Ratnakar_HospitalSearchForm(array('action' => $this->formatURL('/corp_ratnakar_hospital/search'),
                                                            'method' => 'POST',
                                                            'name'=>'frmSearch',
                                                            'id'=>'frmSearch',
                                                       ));
        
       if ($data['submit_form'] != '') {
            
            if($data['state'] !=''){    
                $citylist = $state->getCityByStateCode($data['state']);
                $form->getElement("city")->setMultiOptions($citylist);
            }
                
           if($form->isValid($data)){ 
             
               
        
        
           if($param['state']!='')
            $param['state'] = $state->getStateName($param['state']);
            $this->view->paginator = $objHospital->getHospitalSearch($param, $this->_getPage()); 
            $this->view->submit_form = $data['submit_form'];
            $this->view->city = $data['city'];
         }
       }
        $this->view->backLink = 'hospital_id_code='.$data['hospital_id_code'].'&terminal_id_code='.$data['terminal_id_code'].'&hospital_name='.$data['hospital_name'].'&pin_code='.$data['pin_code'].'&state='.$data['state'].'&city='.$data['city'].'&submit_form=Search Hospital'.'&csrfhash='.$data['csrfhash'];
        
       //$this->view->backLink = 'searchCriteria='.$data['searchCriteria'].'&keyword='.$data['keyword'].'&sub=1';
       //$this->view->controllerName = Zend_Registry::get('controllerName');
       $this->view->form = $form;
       $this->view->formData = $data;
       $form->populate($data);
    
     }
     
     
     
     /* addAction will add hospital details with related tids
     */
    public function addAction(){
       
        $this->title = 'Hospital';

        //$data['submit_form'] = $this->_getParam('submit_form');
        $objHospital = new Corp_Ratnakar_Hospital();
        $objLog = new Log();
        $state = new CityList();
        $formData  = $this->_request->getPost();
        $user = Zend_Auth::getInstance()->getIdentity();
        $errorTids='';
        $errorExists = false;  
        $stateName='';
        $isTidRepeating = false;
        $form = new Corp_Ratnakar_AddHospitalForm(array('action' => $this->formatURL('/corp_ratnakar_hospital/add'),
                                                        'method' => 'POST',
                                                        'name'=>'frmAdd',
                                                        'id'=>'frmAdd',
                                                   ));
        
       
        
     
       if ($formData['submit_form'] != '') {
            
            if($formData['state'] !=''){    
                $citylist = $state->getCityByStateCode($formData['state']);
                $form->getElement("city")->setMultiOptions($citylist);
            }
                
           if($form->isValid($this->getRequest()->getPost())){ 
               
           
           
           // validating format of tids
           if(trim($formData['terminal_id_code'])!=''){
               $terminalIds = explode(",", $formData['terminal_id_code']);
               $newTerminalIds = array();
               
               foreach($terminalIds as $val){ 
                       $filteredVal = trim($val);
                       if($filteredVal!='')
                          $newTerminalIds[count($newTerminalIds)] = $filteredVal;
               }
               $terminalIds = $newTerminalIds;
               
               //var_dump($terminalIds); exit;
               $countTids = count($terminalIds);
               for($i=0; $i<$countTids; $i++){
                   if($terminalIds[$i]!=''){
                      
                       // checking tid format, if found invalid will show as error
                       if(!Util::isDigits($terminalIds[$i])){
                           //var_dump($terminalIds[$i]);
                           if($errorTids!='')
                              $errorTids .= ', ';
                           
                           $errorTids .= $terminalIds[$i];
                       }
                    
                       // checking tid length, if found invalid will show as error
                      $minLength = \App_DI_Container::get('ConfigObject')->terminal->id->minlength;
                      $maxLength = \App_DI_Container::get('ConfigObject')->terminal->id->maxlength;
                      
                       if(!Util::checkDigitsLength($terminalIds[$i], $minLength, $maxLength)){
                           if($errorTids!='')
                              $errorTids .= ', ';
                           
                           //echo $terminalIds[$i]; exit;
                           $errorTids .= $terminalIds[$i];
                       }
                   }
               }
               
               if($errorTids!=''){
                    $errorExists = true;
                    $errorMsg = 'Invalid tids ('.$errorTids.'), tids must be numeric format with '.$minLength.' to '.$maxLength.' digits long';
                    $this->_helper->FlashMessenger( array('msg-error' => $errorMsg,) ); 
                    $form->populate($formData);
                    App_Logger::log($errorMsg, Zend_Log::ERR);
                   
               }
               
           }
           
            // checking if tids getting repeat
           $tidsRepeatCount = array_count_values($terminalIds);
           
           foreach($tidsRepeatCount as $val){
               if($val>1)
                  $isTidRepeating = true;
           }
           
           if($isTidRepeating){
                $errorExists = true;
                $form->populate($formData);
                $this->_helper->FlashMessenger( array('msg-error' => 'Terminal Ids cannot be repeat') ); 
           }           
           else {  
           
           if(!$errorExists){
              // validating duplicacy of hospital id terminal id
           try{
                $isHospitalExist = $objHospital->isHospitalDuplicate($formData['hospital_id_code']);
                $isTerminalExist = $objHospital->isTerminalDuplicate($terminalIds);
                
           }catch (Exception $e ) {  
                $errorExists = true;
                $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) ); 
                $form->populate($formData);
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
             }
           }
            
            if(!$isHospitalExist && !$isTerminalExist && !$errorExists){
                
                if($formData['state']!='')
                   $stateName = $state->getStateName($formData['state']);
                
                $addHospitalParams = array(
                                            'hospital_id_code'=>$formData['hospital_id_code'],
                                            'name'=>$formData['hospital_name'],
                                            'address'=>$formData['address'],
                                            'city'=>$formData['city'],
                                            'state'=>$stateName,
                                            'pincode'=>$formData['pin_code'],
                                            'std_code'=>$formData['std_code'],
                                            'phone'=>$formData['phone'],
                                            'status'=>STATUS_ACTIVE,
                                            'by_agent_id'=>$user->id,
                                            'ip'=>$objHospital->formatIpAddress(Util::getIP())
                                          );
               
                $addTerminalParams = array(
                                            'status'=>STATUS_ACTIVE,
                                            'by_agent_id'=>$user->id,
                                            'ip'=>$objHospital->formatIpAddress(Util::getIP())
                                          );
                
                
                try{
                    // adding hospital in main table and in log table
                    $addHospitalResp = $objHospital->addHospital($addHospitalParams); 
                    $hospitalLogData = $addHospitalParams;
                    $hospitalLogData['hospital_id'] = $addHospitalResp;        
                    $objLog->insertlog($hospitalLogData,  DbTable::TABLE_LOG_RAT_CORP_HOSPITAL);
                    
                    // adding terminal in main table and in log table
                    $addTerminalParams['hospital_id'] = $addHospitalResp;
                    $addHospitalResp = $objHospital->addTerminal($addTerminalParams, $terminalIds); 
                    
                } catch (Exception $e ) {  
                        $errorExists = true;
                        $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) ); 
                        $form->populate($formData);
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
            } 
            $this->_helper->FlashMessenger( array('msg-success' => 'Hospital added successfully',) ); 
         }
        } // is tid repeating check over here
       }
        $this->view->city = $formData['city'];
        $this->view->pincode = $formData['pin_code'];
        
     }
     
       $this->view->form = $form;       
       //$form->populate($formData);
  }
     
   
  
  
    /* deleteAction will delete hospital details with related terminal ids
    */
    public function deleteAction(){
       
        $this->title = 'Hospital';

        //$data['submit_form'] = $this->_getParam('submit_form');
        $data['hospital_id_code'] = $this->_getParam('hospital_id_code');
        $data['terminal_id_code'] = $this->_getParam('terminal_id_code');
        $data['hospital_name'] = $this->_getParam('hospital_name');
        $data['pin_code'] = $this->_getParam('pin_code');
        $data['state'] = $this->_getParam('state');
        $data['city'] = $this->_getParam('city');
        $data['csrfhash'] = $this->_getParam('csrfhash');
        $data['formName'] = $this->_getParam('formName');
        $objHospital = new Corp_Ratnakar_Hospital();
        $objLog = new Log();
        $state = new CityList();
        $formData  = $this->_request->getPost();
        $hospitalId = $this->_getParam('id');
        $user = Zend_Auth::getInstance()->getIdentity();
        $form = new Corp_Ratnakar_DeleteHospitalForm(array('action' => $this->formatURL('/corp_ratnakar_hospital/delete'),
                                                        'method' => 'POST',
                                                        'name'=>'frmDelete',
                                                        'id'=>'frmDelete',
                                                   ));
        
        $result = $objHospital->getHospitalDetails(array('hospital_id'=> $hospitalId));
        $resultArr = $result->toArray();
        $redictUrl = $this->formatURL('/corp_ratnakar_hospital/search?hospital_id_code='.$data['hospital_id_code'].'&terminal_id_code='.$data['terminal_id_code'].'&hospital_name='.$data['hospital_name'].'&pin_code='.$data['pin_code'].'&state='.$data['state'].'&city='.$data['city'].'&submit_form=Search Hospital'.'&csrfhash='.$data['csrfhash'].'&formName='.$data['formName']);
       // $form->$_cancelLinkUrl = $redictUrl;
        $form->setCancelLink($redictUrl);

        if ($formData['submit'] != '') {
            
           
           if($form->isValid($this->getRequest()->getPost())){ 
               
               try{
                   
                    $delResp = $objHospital->deleteHospital($hospitalId);
                    
                    $hospitalLogParams = array(
                                       'hospital_id'=> $resultArr[0]['id'],
                                       'hospital_id_code'=> $resultArr[0]['hospital_id_code'],
                                       'name'=> $resultArr[0]['name'],
                                       'address'=> $resultArr[0]['address'],
                                       'city'=> $resultArr[0]['city'],
                                       'state'=> $resultArr[0]['state'],
                                       'pincode'=> $resultArr[0]['pincode'],
                                       'std_code'=> $resultArr[0]['std_code'],
                                       'phone'=> $resultArr[0]['phone'],
                                       'status'=> STATUS_DELETED,
                                       'by_agent_id'=> $user->id,
                                       'ip'=>$objHospital->formatIpAddress(Util::getIP())
                                      );
                    
                    $tids = explode(",", $resultArr[0]['terminal_id']);
                    $delLogResp = $objHospital->addDeleteHospitalLog($hospitalLogParams, $tids);
               } catch (Exception $e ) {  
                        $errorExists = true;
                        $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) ); 
                        $form->populate($formData);
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
            } 
               
            if($delResp && $delLogResp){
                $this->_helper->FlashMessenger( array('msg-success' => 'Hospital deleted successfully') ); 
                $this->_redirect($this->formatURL('/corp_ratnakar_hospital/search?hospital_id_code='.$formData['hospital_id_code'].'&terminal_id_code='.$formData['terminal_id_code'].'&hospital_name='.$formData['hospital_name'].'&pin_code='.$formData['pin_code'].'&state='.$formData['state'].'&city='.$formData['city'].'&submit_form=Search Hospital'.'&csrfhash='.$formData['csrfhash'].'&formName='.$formData['formName']));
            }
               
       }
        
     }
     
       
       
       $this->view->form = $form; 
       $this->view->hospitalInfo = $result; 
       $data['id'] = $resultArr[0]['id'];
       $form->populate($data);
  }
  
  /* editAction will edit hospital details with related terminal ids
    */
    public function editAction(){
       
         $this->title = 'Hospital';

        //$data['submit_form'] = $this->_getParam('submit_form');
        $objHospital = new Corp_Ratnakar_Hospital();
        $objLog = new Log();
        $state = new CityList();
        $data['id'] = $this->_getParam('id');
        $formData  = $this->_request->getPost();
        $user = Zend_Auth::getInstance()->getIdentity();
        
        $errorTids='';
        $errorExists = false;  
        $stateName='';
        $form = new Corp_Ratnakar_EditHospitalForm(array('action' => $this->formatURL('/corp_ratnakar_hospital/edit'),
                                                        'method' => 'POST',
                                                        'name'=>'frmEdit',
                                                        'id'=>'frmEdit',
                                                   ));
        
        $redictUrl = $this->formatURL('/corp_ratnakar_hospital/search?hospital_id_code='.$this->_getParam('hospital_id_code').'&terminal_id_code='.$this->_getParam('terminal_id_code').'&hospital_name='.$this->_getParam('hospital_name').'&pin_code='.$this->_getParam('pin_code').'&state='.$this->_getParam('state').'&city='.$this->_getParam('city').'&submit_form=Search Hospital'.'&csrfhash='.$this->_getParam('csrfhash').'&formName='.$this->_getParam('formName'));
       
             
       if ($formData['submit_form'] != '') {
            
            if($formData['state'] !=''){    
                $citylist = $state->getCityByStateCode($formData['state']);
                $form->getElement("city")->setMultiOptions($citylist);
            }
        
           if($form->isValid($this->getRequest()->getPost())){ 
               
           
           
           // validating format of tids
           if(trim($formData['terminal_id_code'])!=''){
               $terminalIds = explode(",", $formData['terminal_id_code']);
               $newTerminalIds = array();
               foreach($terminalIds as $index => $val){ 
                       $filteredVal = trim($val);
                       if($filteredVal!='')
                          $newTerminalIds[count($newTerminalIds)] = $filteredVal;
               }
               $terminalIds = $newTerminalIds;
               
               //var_dump($terminalIds); exit;
               $countTids = count($terminalIds);
               for($i=0; $i<$countTids; $i++){
                   if($terminalIds[$i]!=''){
                      
                       // checking tid format, if found invalid will show as error
                       if(!Util::isDigits($terminalIds[$i])){
                           //var_dump($terminalIds[$i]);
                           if($errorTids!='')
                              $errorTids .= ', ';
                           
                           $errorTids .= $terminalIds[$i];
                       }
                       
                       // checking tid length, if found invalid will show as error
                      $minLength = \App_DI_Container::get('ConfigObject')->terminal->id->minlength;
                      $maxLength = \App_DI_Container::get('ConfigObject')->terminal->id->maxlength;
                      
                       if(!Util::checkDigitsLength($terminalIds[$i], $minLength, $maxLength)){
                           if($errorTids!='')
                              $errorTids .= ', ';
                           
                           //echo $terminalIds[$i]; exit;
                           $errorTids .= $terminalIds[$i];
                       }
                   }
               }
               
               if($errorTids!=''){
                    $errorExists = true;
                    $errorMsg = 'Invalid tids ('.$errorTids.'), tids must be numeric format with '.$minLength.' to '.$maxLength.' digits long';
                    $this->_helper->FlashMessenger( array('msg-error' => $errorMsg,) ); 
                    
                    $form->populate($formData);
                    App_Logger::log($errorMsg, Zend_Log::ERR);
                   
               }
               
           }
           
           
           // analysing the new and old tids 
           $oldTids = explode(",", $formData['old_terminal_id_code']);
           $newTids = $terminalIds;
           $countOldTids = count($oldTids);
           $countNewTids = count($newTids);
           $deleteableTids = array();
           $addableTids = array();
           $isAddableTid =false;
           $isDeleteableTid =false;
           $isTidRepeating = false;
           
           // checking if tids getting repeat
           $tidsRepeatCount = array_count_values($newTids);
           
           foreach($tidsRepeatCount as $val){
               if($val>1)
                  $isTidRepeating = true;
           }
           
           if($isTidRepeating){
                $errorExists = true;
                $this->_helper->FlashMessenger( array('msg-error' => 'Terminal Ids cannot be repeat') ); 
           }           
           else{               
               
           // old tids delete request by user
           for($i=0; $i<$countOldTids; $i++){
               if(!in_array($oldTids[$i], $newTids)){
                   $deleteableTids[count($deleteableTids)] = $oldTids[$i];
                   $isDeleteableTid =true;
               }
           }
           
           // new tids request by user
           for($i=0; $i<$countNewTids; $i++){
               if(!in_array($newTids[$i], $oldTids)){
                   $addableTids[count($addableTids)] = $newTids[$i];
                   $isAddableTid =true;
               }
           }
           
           $countAddableTids = count($addableTids);
           $countDeleteableTids = count($deleteableTids);
           
           if(!$errorExists){
               
               if($isAddableTid){
                   
                // validating duplicacy of hospital id terminal id
                try{
                     $isTerminalExist = $objHospital->isTerminalDuplicate($addableTids);

                }catch (Exception $e ) {  
                     $errorExists = true;
                     $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) ); 
                     $form->populate($formData);
                     App_Logger::log($e->getMessage(), Zend_Log::ERR);
                  }
               }
               
            // adding new Tids here
            if($isAddableTid && !$isTerminalExist){                
               
                $addTerminalParams = array(
                                            'hospital_id'=>$formData['id'],
                                            'status'=>STATUS_ACTIVE,
                                            'by_agent_id'=>$user->id,
                                            'ip'=>$objHospital->formatIpAddress(Util::getIP())
                                          );
                
               
                try{
                    // adding hospital in main table and in log table
                    $addHospitalResp = $objHospital->addTerminal($addTerminalParams, $addableTids); 
                                       
                } catch (Exception $e ) {  
                        $errorExists = true;
                        $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) ); 
                        $form->populate($formData);
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
            } 
          }    // adding new Tids over here                 

          
            // deleting old Tids here
            if($isDeleteableTid){                
             
                try{
                    // adding hospital in main table and in log table
                    $deleteResp = $objHospital->deleteHospitalTerminal('','', $deleteableTids); 
                    $deleteResp = $objHospital->addDeleteTerminalLog('', $deleteableTids);               
             
                } catch (Exception $e ) {  
                        $errorExists = true;
                        $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) ); 
                        $form->populate($formData);
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
            } 
          }    // deleting new Tids over here   
          
           
           
          // udpating to hospital details
          $hospitalDetails = array(
                                    'hospital_id_code'=>$formData['hospital_id_code'],
                                    'name'=>$formData['hospital_name'],
                                    'address'=>$formData['address'],
                                    'city'=>$formData['city'],
                                    'state'=>$state->getStateName($formData['state']),
                                    'pincode'=>$formData['pin_code'],
                                    'std_code'=>$formData['std_code'],
                                    'phone'=>$formData['phone'],
                                    'by_agent_id'=>$user->id,
                                    'ip'=>$objHospital->formatIpAddress(Util::getIP()),
                                    
                                  );
          
            $updateResp = $objHospital->updateHospital($hospitalDetails, $formData['id']);
           $this->_redirect($redictUrl);
            $this->_helper->FlashMessenger( array('msg-success' => 'Hospital edited successfully',) ); 
           }
         }
        } // tid is repeating check over here
       
        
                } // form validating if
       
        //$this->view->city = $formData['city'];
        
    
       if(empty($formData)){
           
        $result = $objHospital->getHospitalDetails(array('hospital_id'=>$data['id']));
        $hospitalInfo = array();
        if(!empty($result)){
                $resultToArr = $result->toArray();
                $hospitalInfo = $resultToArr[0];
        }
        $state = new CityList();
        $stateCode = $state->getStateCode($hospitalInfo['state']);
        $populateData = array(
                                'id'=>$hospitalInfo['id'],
                                'hospital_id_code'=>$hospitalInfo['hospital_id_code'],
                                'terminal_id_code'=>$hospitalInfo['terminal_id_code'],
                                'old_terminal_id_code'=>$hospitalInfo['terminal_id_code'],
                                'hospital_name'=>$hospitalInfo['name'], 
                                'address'=>$hospitalInfo['address'],
                                'state'=>$stateCode,
                                'city'=>$hospitalInfo['city'],
                                'pin_code'=>$hospitalInfo['pincode'],
                                'std_code'=>$hospitalInfo['std_code'],
                                'phone'=>$hospitalInfo['phone'],
                             );
        
         if($stateCode !=''){    
                $citylist = $state->getCityByStateCode($stateCode);
                $form->getElement("city")->setMultiOptions($citylist);
            }
            
//       $this->view->pincode = $populateData['pin_code'];
       } else 
           $populateData = $formData;
       
        $this->view->form = $form;  
        
        $form->populate($populateData);
        $this->view->city = $populateData['city'];
        $this->view->pincode = $populateData['pin_code'];
       
    }
  
}
