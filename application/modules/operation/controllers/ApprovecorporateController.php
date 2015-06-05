<?php
/**
 * Allow the admins to manage Agent fee.
 *
 * @category Agent fee
 * @package operation_module
 * @copyright Transerv
 */

class ApprovecorporateController extends App_Operation_Controller
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
    }
    
    
    public function indexAction(){
        $this->title = 'Approval Pending Corporates Listing';
        $approveagentModel = new Approvecorporate();
        $data = array();
        
        $data['searchCriteria'] = $this->_getParam('searchCriteria');
        $data['keyword'] = $this->_getParam('keyword');
        $data['sub'] = $this->_getParam('sub');
        
        $form = new CorporateSearchForm(array('action' => $this->formatURL('/approvecorporate/index'),
            'method' => 'POST',
        ));
        
        if ($data['sub'] != '')
        {
            $this->view->paginator = $approveagentModel->getDetails($data, $this->_getPage());
            $form->populate($data);
        }
        else
        {
            $this->view->paginator = $approveagentModel->getDetails($data, $this->_getPage());
        }
        $this->view->form = $form;
    }
    
      public function rejectedlistAction(){

            //echo "<pre>";print_r($session);
         $this->title = 'Rejected Corporate Listing';
        
        $approveagentModel = new Approvecorporate();
        $this->view->paginator = $approveagentModel->getrejectedDetails($this->_getPage());
    }
    
    
    
      /**
     * Allows the Operation user to Approve Agent
     *
     * @access public
     * @return void
     */
    
    public function approveAction(){
        $this->title = 'Approve Corporate';
        
        $agentsModel = new CorporateUser();
        $approveagentModel = new Approvecorporate();
        $id = $this->_getParam('id');
        $agentModel = new CorporateUser();
        $agentInfo = $agentModel->findById($id);
        //echo '<pre>';print_r($info);exit;
        $form = new CorporateApproveForm($agentInfo);        
        //if( $agentsModel->getAgentType($agentInfo['id']) != NORMAL_AGENT) {
        //        $form->removeElement('is_super_agent');
        //}
        $user = Zend_Auth::getInstance()->getIdentity();
        $res = '';
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
                $formData  = $this->_request->getPost();
               //echo "<pre>";print_r($formData);exit; 
                $data = array('agent_id' => $id,'by_ops_id' => $user->id, 
                    'status_old' => STATUS_PENDING,'status_new' => STATUS_APPROVED,'remarks' => $formData['remarks']
                    );
            //if(isset($formData['is_super_agent'])) {
            //    $data['user_type'] = $formData['is_super_agent'];
            //}
               
                
                try{
                    $res = $approveagentModel->approveById($id,$data);
                    //$res = true;
                    
                } catch(Exception $e){
                     App_Logger::log($e->getMessage(), Zend_Log::ERR);
                     $Msg = $e->getMessage();
                }
             
                if($res){
                    $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'Corporate successfully Approved.',
                    )
                );
                    
                    if(isset($agentInfo['id'])) {
                        $this->_redirect($this->formatURL('/approvedcorporate/assigncorporatelimits?id='.$id));                        
                    } else {
                        $this->_redirect($this->formatURL('/approvecorporate/index/'));                                                                        
                    }
                    
                }
                else
                {
                    $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => $Msg,
                    )
                );
                }
                
                //Regenerate Flag and Flippers
                //App_FlagFlippers_Manager::save();
                
                
            }
        }else{
            
            $id = $this->_getParam('id');
            $row = $approveagentModel->findCorporate($id);
            
            if (empty($row)) {
                $this->_helper->FlashMessenger(
                    array(
                        'msg-warning' => sprintf('We cannot find Corporate with id %s', $id),
                    )
                );
                $this->_redirect($this->formatURL('/approvecorporate/index/'));
            }
            
            $form->populate($row);
            $this->view->item = (object)$row;
        }
        $row = $approveagentModel->findCorporate($id);
         
        $this->view->item = (object)$row;
        $this->view->form = $form;
    }
    
       /**
     * Allows the Operation user to Reject Agent
     *
     * @access public
     * @return void
     */
    
    public function rejectAction(){
        $this->title = 'Reject Agent';
        
        $form = new RejectForm();
        $approveagentModel = new Approvecorporate();
        $agentsModel = new CorporateUser();
        $id = $this->_getParam('id');
        $user = Zend_Auth::getInstance()->getIdentity();
        $res = '';
        $m = new App\Messaging\MVC\Axis\Operation();
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
                $formData  = $this->_request->getPost();
                $data = array('agent_id' => $id,'by_ops_id' => $user->id, 
                    'status_old' => STATUS_PENDING,'status_new' => STATUS_REJECTED,'remarks' => $formData['remarks']
                    );
               
               
                
                try{
                    $res = $approveagentModel->rejectById($id,$data);
                    
                } catch(Exception $e){
                     App_Logger::log($e->getMessage(), Zend_Log::ERR);
                     $Msg = $e->getMessage();
                }
             
                if($res){
                   
                    $row = $agentsModel->findById($id);
                    $emailData = array(
                        'name' => ucfirst($row['first_name']).' '.ucfirst($row['last_name']),
                        'email' => $row['email']
                    );
                    $m->agentRejectionMail($emailData);
                    $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'Corporate successfully rejected.',
                    )
                );
                    $this->_redirect($this->formatURL('/approvecorporate/rejectedlist/'));
                }
                else
                {
                    $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => $Msg,
                    )
                );
                }
                
                //Regenerate Flag and Flippers
                //App_FlagFlippers_Manager::save();
                
                
            }
        }else{
            $id = $this->_getParam('id');
            $row = $approveagentModel->findCorporate($id);
            
            if (empty($row)) {
                $this->_helper->FlashMessenger(
                    array(
                        'msg-warning' => sprintf('We cannot find Corporate with id %s', $id),
                    )
                );
                $this->_redirect($this->formatURL('/approvecorporate/index/'));
            }
            
            $form->populate($row);
            $this->view->item = (object)$row;
        }
        $row = $approveagentModel->findCorporate($id);
         
        $this->view->item = (object)$row;
        $this->view->form = $form;
    }
    
    public function viewAction() {
        
       $this->title = 'Agent details'; 
       $agentModel = new Agents();
       $approveagentModel = new Approveagent();
       $agentlimitModel = new Agentlimit();
       $id = $this->_getParam('id');
       
       $row = $agentModel->findById($id);
       
      
        $documents = $agentModel->agentDoclist($id);
         
         $this->view->document = $documents->toArray();
       
         $this->view->backlink = Zend_Controller_Front::getInstance()->getBaseUrl() . '/approvecorporate/';
         $this->view->item = $row;
         $this->view->agent_id = $id;        
        
    }
    
}