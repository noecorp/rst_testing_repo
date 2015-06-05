<?php
/**
 * Allow the admins to manage critical info, users, groups, permissions, etc.
 *
 * @category backoffice
 * @package backoffice_controllers
 * @copyright company
 */

class AgentlimitController extends App_Operation_Controller
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
        $this->title = 'Agent Limit';
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
            $this->_redirect($this->formatURL('/agentsummary/index/')); exit;
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
//                    $this->_redirect('/agentlimit/confirm?id='.$encId);                          
                    $this->_redirect($this->formatURL('/agentlimit/confirm?id='.$encId));                          
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
            'action' => $this->formatURL('/agentlimit/update'),
            'method' => 'post',
        ));
    }
    
   
    
    public function limitAction(){
        
        $this->title = 'Agent Limits';
        $lid = ($this->_getParam('lid') > 0) ? $this->_getParam('lid') : 0;
        
        $agentlimitModel = new Agentlimit();   
        $bindAgentlimit = $agentlimitModel->getbindAgentlimit(); 
        $this->view->paginator = $agentlimitModel->findAllLimit($lid,$this->_getPage());  
        $this->view->bindAgentlimit = $bindAgentlimit;
    
    }
    
     public function addAction(){
        $user = Zend_Auth::getInstance()->getIdentity();
        $m = new App\Messaging\System\Operation();
       
        $this->title = 'Add Agent Limit';
        //$id = $this->_getParam('id');
        
        $form = new AgentlimitcreateForm();
        $agentlimitModel = new Agentlimit();
        
        
        if($this->getRequest()->isPost()){
           
            if($form->isValid($this->getRequest()->getPost())){
               
                $row = $form->getValues();
                $row['by_ops_id'] = $user->id;
                
                $limitName = $agentlimitModel->checkname($row['name']);
                
                
                if(empty($limitName)){
                
                /*switch (true) {
            case ($row['cnt_out_max_txn_daily'] > $row['cnt_out_max_txn_monthly']):
                $errmsg = 'No. of daily Transactions cannot be more than no. of monthly transactions.';
                break;
            case ($row['cnt_out_max_txn_monthly'] > $row['cnt_out_max_txn_yearly']):
                $errmsg = 'No. of monthly Transactions cannot be more than no. of yearly transactions.';
                break;
            case ($row['limit_out_max_daily'] > $row['limit_out_max_monthly']):
                $errmsg = 'Limit of daily Transactions cannot be more than limit of monthly transactions.';
                break;
            case ($row['limit_out_max_monthly'] > $row['limit_out_max_yearly']):
                $errmsg = 'Limit of monthly Transactions cannot be more than limit of yearly transactions.';
                break;
            case ($row['limit_out_min_txn'] > $row['limit_out_max_txn']):
                $errmsg = 'Min Transaction limit cannot be more than max transaction limit.';
                break;
               }*/
               if(isset($errmsg)){
   	        $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => $errmsg,
                    )
                );
               }
               else {
                $agentlimitModel->insert($row);
           	
               $newArr = array(
                            'Name' => $row['name'],
                            'Currency' => $row['currency'],
                            'Min Amount per Trxn' => $row['limit_out_min_txn'],
                            'Max Amount per Trxn' => $row['limit_out_max_txn'],
                            'Max no. of Txns per Day' => $row['cnt_out_max_txn_daily'],
                            'Max Amount per Day' => $row['limit_out_max_daily'],
                            'Max no. of Txns per Month' => $row['cnt_out_max_txn_monthly'],
                            'Max Amount per Month' => $row['limit_out_max_monthly'],
                            'Max no. of Txns per Year' => $row['cnt_out_max_txn_yearly'],
                            'Max Amount per Year' => $row['limit_out_max_yearly'],
                        );
                        // SEND NOTIFICATION TO ADMIN USERS
                    
                  $mailData['email'] = ADMIN_EMAIL_IDS;
                  $mailData['product_name'] = 'NA';
                  $mailData['limit_category'] = 'AGENT LIMIT: '.$row['name'];
                  $mailData['param_name'] = 'NA';
                  $mailData['old_value'] = NEW_ADDITION;
                  $mailData['new_value'] = $newArr;
                  $m->limitUpdates($mailData); 
                $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'Agent limit info successfully added',
                    )
                );
//                 $this->_redirect('/agentlimit/limit');
                 $this->_redirect($this->formatURL('/agentlimit/limit'));
               }
                }
                else
                {
                    $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'Agent limit Name exists',
                    )
                );
                }
            }
        } 
        $this->view->form = $form;
    }
    
    
    public function listAction(){
        $this->title = 'Agent Limits'; 
        $this->_baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
        
        $this->_controllerName = Zend_Registry::get('controllerName');
        
        $this->title = '';
        $id = $this->_getParam('id');
        $agentlimitModel = new Agentlimit();
        $agentModel = new Agents();
        $row = $agentModel->findById($id);
        $this->view->paginator = $agentlimitModel->getAgentlimit($this->_getPage(),$id); 
        $this->view->id = $id;
        $this->view->name = ucfirst($row['first_name'])." ".ucfirst($row['last_name']);
        $this->view->agentStatus = $row['status'];
        
        $this->view->cnt = count($this->view->paginator);
        $this->view->baseUrl = $this->_baseUrl;
        $this->view->controllerName = $this->_controllerName;
    }
    
    public function addlimitAction(){
        $user = Zend_Auth::getInstance()->getIdentity();
       
       
        $this->title = 'Assign Agent Limits';
        $id = $this->_getParam('id');
        
        $form = new AgentbindlimitForm();
        $agentlimitModel = new Agentlimit();
        
        $form->getElement('agent_id')->setValue($id);
        if($this->getRequest()->isPost()){
          
            if($form->isValid($this->getRequest()->getPost())){
               
                //$row = $form->getValues();
                $row['agent_id'] = $id;
                $row['by_ops_id'] = $user->id;
                $startDate = Util::returnDateFormatted($form->getValue('date_start'), "d-m-Y", "Y-m-d", "-");
                $row['date_start'] = $startDate;
                $row['agent_limit_id'] = $form->getValue('agent_limit_id');
               
    
                
                $res = $agentlimitModel->saveagentlimit($row);
             
                if ($res > 0){
                    $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'Agent limit assigned successfully',
                    )
                );
//                 $this->_redirect('/agentlimit/list?id='.$id);
                 $this->_redirect($this->formatURL('/agentlimit/list?id='.$id));
                }
                else{
                  $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'Agent limit could not be added',
                    )
                );
//                 $this->_redirect('/agentlimit/addlimit?id='.$id);  
                 $this->_redirect($this->formatURL('/agentlimit/addlimit?id='.$id));  
                }
                
                
            }
        } 
        $this->view->form = $form;
    }
    
      public function editlimitAction(){
        $user = Zend_Auth::getInstance()->getIdentity();
       
       
        $this->title = 'Edit Assigned Agent Limits';
        $id = $this->_getParam('id');
        
        $form = new AgentbindlimitForm();
        $agentlimitModel = new Agentlimit();
        $dataArr = $agentlimitModel->findBinddetailsById($id);
        $tomorrowsDate = date('Y-m-d', strtotime(date('Y-m-d') . ' + 1 day'));
        $todaysDate = date('Y-m-d');
        $form->getElement('agent_id')->setValue($dataArr['agent_id']);
        //$form->getElement('date_start')->setValue($tomorrowsDate);
        
        if($this->getRequest()->isPost()){
          
            if($form->isValid($this->getRequest()->getPost())){
               
                //$row = $form->getValues();
                $row['id'] = $id;
                $row['agent_id'] = $dataArr['agent_id'];
                $row['by_ops_id'] = $user->id;
                $startDate = Util::returnDateFormatted($form->getValue('date_start'), "d-m-Y", "Y-m-d", "-");
                $row['date_start'] = $startDate;
                $row['agent_limit_id'] = $form->getValue('agent_limit_id');
                $chkLastdetails  = $agentlimitModel->checkLastdetails($dataArr['agent_id']); 
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
                 
               else if($chkLastdetails['agent_limit_id'] == $form->getvalue('agent_limit_id') && $datetime2 >= $datetime1){
                    
                                     
                     $this->_helper->FlashMessenger(
                        array(
                        'msg-error' => 'Agent limit already assigned',
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
                $res = $agentlimitModel->updateLimit($row);
                unset($row['id']);
                $agentlimitModel->saveagentlimit($row);                

                if ($res > 0){
                    $agentModel = new AgentUser();
                    $agenttype = $agentModel->getAgentType($dataArr['agent_id']);
                    
                    if($agenttype == SUPER_AGENT || $agenttype == DISTRIBUTOR_AGENT) {
                        $info = $agentModel->getChildrenInfo($dataArr['agent_id'], $agenttype);
                        if(!empty($info)) {
                            foreach($info as $val) {
                                $chkLastdetails = $agentlimitModel->checkLastdetails($val['to_object_id']);
                                $updateArr = array(
                                    'id' => $chkLastdetails['id'],
                                    'agent_id' => $val['to_object_id'],
                                    'by_ops_id' => $user->id,
                                    'date_start' => $startDate,
                                    'agent_limit_id' => $row['agent_limit_id']
                                );

                                $agentlimitModel->updateLimit($updateArr);
                                unset($updateArr['id']);
                                $agentlimitModel->saveagentlimit($updateArr);
                                
                                if($agenttype == SUPER_AGENT) {
                                    $subInfo = $agentModel->getChildrenInfo($val['to_object_id'], DISTRIBUTOR_AGENT);
                                    if(!empty($subInfo)) {
                                        foreach($subInfo as $subagent) {
                                            $chkLastdetails = $agentlimitModel->checkLastdetails($subagent['to_object_id']);
                                            $subupdateArr = array(
                                                'id' => $chkLastdetails['id'],
                                                'agent_id' => $subagent['to_object_id'],
                                                'by_ops_id' => $user->id,
                                                'date_start' => $startDate,
                                                'agent_limit_id' => $row['agent_limit_id']
                                            );
                                            
                                            $agentlimitModel->updateLimit($subupdateArr);
                                            unset($subupdateArr['id']);
                                            $agentlimitModel->saveagentlimit($subupdateArr);
                                        }
                                    }
                                }
                            }
                        }                        
                    }
                    
                    $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'Agent limit successfully assigned',
                    )
                );
//                 $this->_redirect('/agentlimit/list?id='.$dataArr['agent_id']);
                 $this->_redirect($this->formatURL('/agentlimit/list?id='.$dataArr['agent_id']));
                }
                else{
                  $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'Agent limit could not be updated',
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
         
        $this->title = 'Delete Assigned Agent Limits';
        $id = $this->_getParam('id');
        $prevId = $this->_getParam('pid');
        
        $form = new DeleteLimitForm();
        $agentlimitModel = new Agentlimit();
        $dataArr = $agentlimitModel->findBinddetailsById($id);
        //$lastId = $agentlimitModel->getlastId($dataArr['agent_id']);
        $form->getElement('id')->setValue($id);
        $form->getElement('pid')->setValue($prevId);
        
        if($this->getRequest()->isPost()){
            
            if($form->isValid($this->getRequest()->getPost())){
               
            
                
                $res = $agentlimitModel->deleteLimit($id, $prevId );
               if ($res > 0){
                    $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'Agent limit successfully deleted',
                    )
                );
//                 $this->_redirect('/agentlimit/list?id='.$dataArr['agent_id']);
                 $this->_redirect($this->formatURL('/agentlimit/list?id='.$dataArr['agent_id']));
                }
                else{
                  $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'Agent limit could not be deleted',
                    )
                );
                 
                }
                
            }
            }
         $agentName = $agentlimitModel->getAgentByLimit($id);
         $detailsArr = $agentlimitModel->getActiveAgentlimit($agentName['id']);
        
         
         $form->populate($detailsArr);
         $this->view->item = (object)$detailsArr;
         $this->view->agent_name = ucfirst($agentName['first_name'])." ".ucfirst($agentName['last_name']);
         $form->getElement('agentId')->setValue($agentName['agent_id']);
         $this->view->form = $form;
    }
    
    public function editagentlimitAction(){
        $user = Zend_Auth::getInstance()->getIdentity();
        $m = new App\Messaging\System\Operation();
       
        $this->title = 'Edit Agent Limits';
        $id = $this->_getParam('id');
        
        $form = new AgentlimitcreateForm();
        $agentlimitModel = new Agentlimit();
        $detailsArr = $agentlimitModel->findById($id);
         
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
                $update = $agentlimitModel->editupdate($id);
              
                $agentlimitModel->insert($row);
                
                 $oldArr = array(
                            'Name' => $detailsArr['name'],
                            'Currency' => $detailsArr['currency'],
                            'Min Amount per Trxn' => $detailsArr['limit_out_min_txn'],
                            'Max Amount per Trxn' => $detailsArr['limit_out_max_txn'],
                            'Max no. of Txns per Day' => $detailsArr['cnt_out_max_txn_daily'],
                            'Max Amount per Day' => $detailsArr['limit_out_max_daily'],
                            'Max no. of Txns per Month' => $detailsArr['cnt_out_max_txn_monthly'],
                            'Max Amount per Month' => $detailsArr['limit_out_max_monthly'],
                            'Max no. of Txns per Year' => $detailsArr['cnt_out_max_txn_yearly'],
                            'Max Amount per Year' => $detailsArr['limit_out_max_yearly'],
                        );
                 
                 
                $newArr = array(
                            'Name' => $row['name'],
                            'Currency' => $row['currency'],
                            'Min Amount per Trxn' => $row['limit_out_min_txn'],
                            'Max Amount per Trxn' => $row['limit_out_max_txn'],
                            'Max no. of Txns per Day' => $row['cnt_out_max_txn_daily'],
                            'Max Amount per Day' => $row['limit_out_max_daily'],
                            'Max no. of Txns per Month' => $row['cnt_out_max_txn_monthly'],
                            'Max Amount per Month' => $row['limit_out_max_monthly'],
                            'Max no. of Txns per Year' => $row['cnt_out_max_txn_yearly'],
                            'Max Amount per Year' => $row['limit_out_max_yearly'],
                        );
                        // SEND NOTIFICATION TO ADMIN USERS
                    
                  $mailData['email'] = ADMIN_EMAIL_IDS;
                  $mailData['product_name'] = 'NA';
                  $mailData['limit_category'] = 'AGENT LIMIT: '.$row['name'];
                  $mailData['param_name'] = 'NA';
                  $mailData['old_value'] = $oldArr;
                  $mailData['new_value'] = $newArr;
                  $m->limitUpdates($mailData); 
               // exit;
                $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'Agent limit info successfully updated',
                    )
                );
//                 $this->_redirect('/agentlimit/limit');
                 $this->_redirect($this->formatURL('/agentlimit/limit'));
               }
               
            }
       
        
        } 
         
        $this->view->form = $form;
    }
        public function deleteagentlimitAction(){
        $user = Zend_Auth::getInstance()->getIdentity();
        $m = new App\Messaging\System\Operation();
       
        $this->title = 'Delete Agent Limit';
        $id = $this->_getParam('id');
        
        $form = new DeleteAgentLimitForm();
        $agentlimitModel = new Agentlimit();
        $detailsArr = $agentlimitModel->findById($id);
        $form->getElement('id')->setValue($id); 
        //$form->populate($detailsArr->toArray());
        
        if($this->getRequest()->isPost()){
           
            if($form->isValid($this->getRequest()->getPost())){
               
               
                
               
                try{
                $res = $agentlimitModel->delete($id);
                
                
                }
                catch(Exception $e){
                    App_Logger::log($e->getMessage(), Zend_Log::ERR);         
                    $addMsg = $e->getMessage();
                }
               if($res == 'deleted'){
                   
                  $oldArr = array(
                            'Name' => $detailsArr['name'],
                            'Currency' => $detailsArr['currency'],
                            'Min Amount per Trxn' => $detailsArr['limit_out_min_txn'],
                            'Max Amount per Trxn' => $detailsArr['limit_out_max_txn'],
                            'Max no. of Txns per Day' => $detailsArr['cnt_out_max_txn_daily'],
                            'Max Amount per Day' => $detailsArr['limit_out_max_daily'],
                            'Max no. of Txns per Month' => $detailsArr['cnt_out_max_txn_monthly'],
                            'Max Amount per Month' => $detailsArr['limit_out_max_monthly'],
                            'Max no. of Txns per Year' => $detailsArr['cnt_out_max_txn_yearly'],
                            'Max Amount per Year' => $detailsArr['limit_out_max_yearly'],
                        );
                 
                 
              
                        // SEND NOTIFICATION TO ADMIN USERS
                    
                  $mailData['email'] = ADMIN_EMAIL_IDS;
                  $mailData['product_name'] = 'NA';
                  $mailData['limit_category'] = 'AGENT LIMIT: '.$row['name'];
                  $mailData['param_name'] = 'NA';
                  $mailData['old_value'] = $oldArr;
                  $mailData['new_value'] = DELETION;
                  $m->limitUpdates($mailData); 
                $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'The agent limit was successfully deleted',
                    )
                );                
//                $this->_redirect('/agentlimit/limit');
                $this->_redirect($this->formatURL('/agentlimit/limit'));
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