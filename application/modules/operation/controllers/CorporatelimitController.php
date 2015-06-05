<?php
/**
 * Allow the admins to manage critical info, users, groups, permissions, etc.
 *
 * @category backoffice
 * @package backoffice_controllers
 * @copyright company
 */

class CorporatelimitController extends App_Operation_Controller
{
    /**
     * Holds the current controller's name
     * 
     * @var mixed
     * @access protected
     */
    protected $_controllerName;
    
    /**
     * Holds the base url for generating 
     * links
     * 
     * @var mixed
     * @access protected
     */
    protected $_baseUrl;
    /**
     * Overrides Zend_Controller_Action::init()
     *
     * @access public
     * @return void
     */
    public function init(){
        // init the parent
        parent::init();
    }
    
    
    public function indexAction(){
        $this->title = 'Corporate Limit';
        $errMsg='';        
        
        $form = new AgentLimitForm();
        //$objLimValid = new Validator_AgentBalance();//AgentBalance();
        $agentModel = new Agents();
        $objAgnBal = new AgentBalance();
        $session = new Zend_Session_Namespace('App.Operation.Controller');
        $oprInfo = Zend_Auth::getInstance()->getIdentity();
        
        $formData  = $this->_request->getPost();
        $id = $this->_getParam('id');
         
         if($id<1){
//            $this->_redirect('/agentsummary/'); exit;
            $this->_redirect($this->formatURL('/corporate/index/')); exit;
         }
       
        $row = $agentModel->findById($id);
        $agentBalance = $agentModel->getAgentBalance($id);
        $frmDetails = $row->toArray();
        if(!empty($agentBalance)) {
            $agentBal = $agentBalance->toArray();
        } else {
           $agentBal = array('amount' => '0.00'); 
        }
        $agentDetails = array_merge($frmDetails, $agentBal);
    
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){                   
               
                    $loadAmount = $formData['amount_limit'];
                    //$resp = $objAgentBal->loadAgentBalance(array_merge(array('agent_id'=>$id), $form->getValues()));
                    //$resp = $objAgnBal->validateAgentBalance(array('agent_id'=>$id, 'amount'=>$loadAmount));  
                                   
                    //$resp = $agentBal->loadAgentBalance(array_merge(array('agent_id'=>$id), array('amount'=>$formData['amount_limit'])));
                    $data = array(
                                   'first_name'=>$agentDetails['first_name'], 
                                   'last_name'=>$agentDetails['last_name'],
                                   'amount'=>$formData['amount_limit'], 
                                   'active_balance'=>$agentDetails['amount'],
                                   'email'=>$agentDetails['email'],
                                   'operation_name'=>$oprInfo->username,
                                   'new_balance'=>$agentDetails['amount']+$formData['amount_limit'],
                                   'mobile1'=>$agentDetails['mobile1'],
                                   'agent_name'=>$agentDetails['first_name']." ".$agentDetails['last_name']
                                 );

                    $session = new Zend_Session_Namespace('App.Operation.Controller');         

                    $session->$id = $data;
                    //$this->limitConfirmAction(array_merge(array('agent_id'=>$id), $data));
                    $encId = Util::getEncrypt($id);
//                    $this->_redirect('/corporatelimit/confirm?id='.$encId);                          
                    $this->_redirect($this->formatURL('/corporatelimit/confirm?id='.$encId));                          
            }
        } else {          
            
           if (empty($row)) {
                $this->_helper->FlashMessenger(
                    array(
                        'msg-warning' => 'The requested agent_id could not be found',
                    )
                );
                
//                $this->_redirect('/agentsummary/');
                $this->_redirect($this->formatURL('/agentsummary/'));
            }     
                      
        }
         $form->populate($agentDetails);
         $this->view->agentDetails = $agentDetails;
         $this->view->form = $form;
    } 
    
    
    public function confirmAction(){
        $this->title = 'Confirm Agent Limit';
        $agentId = Util::getDecrypt($this->_getParam('id'));
        
        $session = new Zend_Session_Namespace('App.Operation.Controller');         
        $agentDetails = isset($session->$agentId)?$session->$agentId:array();  
       
        $objBalance = new AgentBalance();
        $amountAfterLoad = $objBalance->getBalanceAfterLoad($agentId, $agentDetails['amount']);
        $agentDetails['amountAfterLoad'] = $amountAfterLoad;        
     
        $form = $this->getForm();
        
        $form->getElement("id")->setValue($agentId);
        $this->view->form = $form;
        $this->view->item = array(
                                  'first_name'=>$agentDetails['first_name'],
                                  'last_name'=>$agentDetails['last_name'],
                                  'amount'=>$agentDetails['amount'],
                                  'active_balance'=>$agentDetails['active_balance'],
                                  'amountAfterLoad'=>$amountAfterLoad
                                 );
        
    }
    
    public function getForm(){
        return new ConfirmLimitForm(array(
            'action' => $this->formatURL('/corporatelimit/update'),
            'method' => 'post',
        ));
    }
    
   
    
    public function limitAction(){
        
        $this->title = 'Corporate Limits';
        $lid = ($this->_getParam('lid') > 0) ? $this->_getParam('lid') : 0;
        
        $corporatelimitModel = new Corporatelimit();   
        $corporatelimit = new Corporatelimits();   
        $bindCorplimit = $corporatelimitModel->getbindCorplimit(); 
        $this->view->paginator = $corporatelimit->findAllLimit($lid,$this->_getPage());  
        $this->view->bindCorplimit = $bindCorplimit;
    
    }
    
     public function addAction(){
        $user = Zend_Auth::getInstance()->getIdentity();
       
       
        $this->title = 'Add Corporate Limit';
        //$id = $this->_getParam('id');
        
        $form = new CorporatelimitcreateForm();
        $corplimitModel = new Corporatelimits();
        
        
        if($this->getRequest()->isPost()){
           
            if($form->isValid($this->getRequest()->getPost())){
               
                $row = $form->getValues();
                $row['by_ops_id'] = $user->id;
                
                $limitName = $corplimitModel->checkname($row['name']);
                
                
                if(empty($limitName)){
                
              
               if(isset($errmsg)){
   	        $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => $errmsg,
                    )
                );
               }
               else {
                $corplimitModel->insert($row);
                $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'Corporate limit info successfully added',
                    )
                );
//                 $this->_redirect('/corporatelimit/limit');
                 $this->_redirect($this->formatURL('/corporatelimit/limit'));
               }
                }
                else
                {
                    $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'Corporate limit Name exists',
                    )
                );
                }
            }
        } 
        $this->view->form = $form;
    }
    
    
    public function listAction(){
        $this->title = 'Corporate Limits'; 
        $this->_baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
        
        $this->_controllerName = Zend_Registry::get('controllerName');
        
        $this->title = '';
        $id = $this->_getParam('id');
        $limitModel = new Corporatelimit();
        $corpModel = new CorporateUser();
        $row = $corpModel->findById($id);
        $this->view->paginator = $limitModel->getCorporatelimit($this->_getPage(),$id); 
        $this->view->id = $id;
        $this->view->name = ucfirst($row['first_name'])." ".ucfirst($row['last_name']);
        $this->view->agentStatus = $row['status'];
        
        $this->view->cnt = count($this->view->paginator);
        $this->view->baseUrl = $this->_baseUrl;
        $this->view->controllerName = $this->_controllerName;
    }
    
    public function addlimitAction(){
        $user = Zend_Auth::getInstance()->getIdentity();
       
       
        $this->title = 'Assign Corporate Limits';
        $id = $this->_getParam('id');
        
        $form = new CorporatebindlimitForm();
        $limitModel = new Corporatelimit();
        
        $form->getElement('corporate_id')->setValue($id);
        if($this->getRequest()->isPost()){
          
            if($form->isValid($this->getRequest()->getPost())){
               
                //$row = $form->getValues();
                $row['corporate_id'] = $id;
                $row['by_ops_id'] = $user->id;
                $startDate = Util::returnDateFormatted($form->getValue('date_start'), "d-m-Y", "Y-m-d", "-");
                $row['date_start'] = $startDate;
                $row['corporate_limit_id'] = $form->getValue('corporate_limit_id');
               
    
                
                $res = $limitModel->savecorporatelimit($row);
             
                if ($res > 0){
                    $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'Corporate limit assigned successfully',
                    )
                );
//                 $this->_redirect('/corporatelimit/list?id='.$id);
                 $this->_redirect($this->formatURL('/corporatelimit/list?id='.$id));
                }
                else{
                  $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'Corporate limit could not be added',
                    )
                );
//                 $this->_redirect('/corporatelimit/addlimit?id='.$id);  
                 $this->_redirect($this->formatURL('/corporatelimit/addlimit?id='.$id));  
                }
                
                
            }
        } 
        $this->view->form = $form;
    }
    
      public function editlimitAction(){
        $user = Zend_Auth::getInstance()->getIdentity();
       
       
        $this->title = 'Edit Assigned Corporate Limits';
        $id = $this->_getParam('id');
        
        $form = new CorporatebindlimitForm();
        $limitModel = new Corporatelimit();
        $dataArr = $limitModel->findBinddetailsById($id);
        $tomorrowsDate = date('Y-m-d', strtotime(date('Y-m-d') . ' + 1 day'));
        $todaysDate = date('Y-m-d');
        $form->getElement('corporate_id')->setValue($dataArr['corporate_id']);
        //$form->getElement('date_start')->setValue($tomorrowsDate);
        
        if($this->getRequest()->isPost()){
          
            if($form->isValid($this->getRequest()->getPost())){
               
                //$row = $form->getValues();
                $row['id'] = $id;
                $row['corporate_id'] = $dataArr['corporate_id'];
                $row['by_ops_id'] = $user->id;
                $startDate = Util::returnDateFormatted($form->getValue('date_start'), "d-m-Y", "Y-m-d", "-");
                $row['date_start'] = $startDate;
                $row['corporate_limit_id'] = $form->getValue('corporate_limit_id');
                $chkLastdetails  = $limitModel->checkLastdetails($dataArr['corporate_id']); 
                $datefromDb = $chkLastdetails['date_start'];
                $datefromForm = $startDate;
                $datetimeToday = strtotime($todaysDate);
                $datetime1 = strtotime($datefromDb);
                $datetime2 = strtotime(Util::returnDateFormatted($datefromForm,  "Y-m-d", "d-m-Y","-"));
                 
                 //$interval = $datetime2->diff($datetime1);
                 //$days =  $interval->format('%d');
                if($datetime2 <= $datetimeToday){
                    $this->_helper->FlashMessenger(
                        array(
                        'msg-error' => 'Start date cannot be less than or equal to today\'s date',
                        )
                    );
                }
                 
               else if($chkLastdetails['corporate_limit_id'] == $form->getvalue('corporate_limit_id') && $datetime2 >= $datetime1){
                    
                                     
                     $this->_helper->FlashMessenger(
                        array(
                        'msg-error' => 'Corporate limit already assigned',
                        )
                    );
                     
                     
                     
                }
                 else if ($datetime2 < $datetime1){
                   $this->_helper->FlashMessenger(
                        array(
                        'msg-error' => "Date selected cannot be less than current agent limit's start date.",
                        //'msg-error' => "The date is already in the existing records. Please select another start date.",
                        )
                    );
                     
                      
                }  
                
                
                else {
                
                $res = $limitModel->updateLimit($row);
                unset($row['id']);
                $limitModel->savecorporatelimit($row);
                if ($res > 0){
                    $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'Corporate limit successfully assigned',
                    )
                );
//                 $this->_redirect('/corporatelimit/list?id='.$dataArr['agent_id']);
                 $this->_redirect($this->formatURL('/corporatelimit/list?id='.$dataArr['corporate_id']));
                }
                else{
                  $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'Corporate limit could not be updated',
                    )
                );
                 
                }
                
                
            }
           }
        } 
        
        
         
        //$dataArr['date_start'] = Util::returnDateFormatted($dataArr['date_start'], "Y-m-d", "d-m-Y", "-");
        $dataArr['date_start'] = Util::returnDateFormatted($tomorrowsDate, "Y-m-d", "d-m-Y", "-");
        $form->populate($dataArr);
        
        $this->view->item = $dataArr;
        $this->view->form = $form;
    }
    
    public function deletelimitAction(){
         
        $this->title = 'Delete Assigned Corporate Limits';
        $id = $this->_getParam('id');
        $prevId = $this->_getParam('pid');
        
        $form = new DeleteCorpLimitForm();
        $limitModel = new Corporatelimit();
        $dataArr = $limitModel->findBinddetailsById($id);
        //$lastId = $limitModel->getlastId($dataArr['agent_id']);
        $form->getElement('id')->setValue($id);
        $form->getElement('pid')->setValue($prevId);
        
        if($this->getRequest()->isPost()){
            
            if($form->isValid($this->getRequest()->getPost())){
               
            
                
                $res = $limitModel->deleteLimit($id, $prevId );
               if ($res > 0){
                    $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'Corporate limit successfully deleted',
                    )
                );
//                 $this->_redirect('/corporatelimit/list?id='.$dataArr['agent_id']);
                 $this->_redirect($this->formatURL('/corporatelimit/list?id='.$dataArr['corporate_id']));
                }
                else{
                  $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'Corporate limit could not be deleted',
                    )
                );
                 
                }
                
            }
            }
         $corpName = $limitModel->getCorporateByLimit($id);
         $detailsArr = $limitModel->getActiveCorplimit($corpName['id']);
        
         
         $form->populate($detailsArr);
         $this->view->item = (object)$detailsArr;
         $this->view->agent_name = ucfirst($corpName['first_name'])." ".ucfirst($corpName['last_name']);
         $form->getElement('corporateId')->setValue($corpName['corporate_id']);
         $this->view->form = $form;
    }
    
    public function editcorplimitAction(){
        $user = Zend_Auth::getInstance()->getIdentity();
       
       
        $this->title = 'Edit Corporate Limits';
        $id = $this->_getParam('id');
        
        $form = new CorporatelimitcreateForm();
        $limitModel = new Corporatelimits();
        $detailsArr = $limitModel->findById($id);
        $form->populate($detailsArr->toArray());
        $this->view->item = $detailsArr;
        
        if($this->getRequest()->isPost()){
           
            if($form->isValid($this->getRequest()->getPost())){
               
                $row = $form->getValues();
                $row['by_ops_id'] = $user->id;
                
               
               if(isset($errmsg)){
   	        $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => $errmsg,
                    )
                );
               }
               else {
                $update = $limitModel->editupdate($id);
              
                $limitModel->insert($row);
               // exit;
                $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'Corporate limit info successfully updated',
                    )
                );
//                 $this->_redirect('/corporatelimit/limit');
                 $this->_redirect($this->formatURL('/corporatelimit/limit'));
               }
               
            }
       
        
        } 
         
        $this->view->form = $form;
    }
        public function deletecorplimitAction(){
        $user = Zend_Auth::getInstance()->getIdentity();
       
       
        $this->title = 'Delete Corporate Limit';
        $id = $this->_getParam('id');
        
        $form = new DeleteCorpLimitForm();
        $limitModel = new Corporatelimits();
        $detailsArr = $limitModel->findById($id);
        $form->getElement('id')->setValue($id); 
        //$form->populate($detailsArr->toArray());
        
        if($this->getRequest()->isPost()){
           
            if($form->isValid($this->getRequest()->getPost())){
               
               
                
               
                try{
                $res = $limitModel->delete($id);
                }
                catch(Exception $e){
                    App_Logger::log($e->getMessage(), Zend_Log::ERR);         
                    $addMsg = $e->getMessage();
                }
               if($res == 'deleted'){
                $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'The Corporate limit was successfully deleted',
                    )
                );                
//                $this->_redirect('/corporatelimit/limit');
                $this->_redirect($this->formatURL('/corporatelimit/limit'));
               }
              
                else {
                 $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => $addMsg,
                    )
                );
                
                
               }
               }
               
            
        } 
        $this->view->name = $detailsArr['name'];
        $this->view->item = (object)$detailsArr;
        $this->view->form = $form;
    }
}