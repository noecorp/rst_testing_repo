<?php
/**
 * Allow the admins to manage Agent Bank, Program Type, Product, Product Limit, Commission, Fee.
 *
 * @category Agent fee
 * @package operation_module
 * @copyright Transerv
 */

class ApprovedagentController extends App_Operation_Controller
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

        $this->_baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
        
        $this->_controllerName = Zend_Registry::get('controllerName');
        
            //echo "<pre>";print_r($session);
        $this->title = 'Products Limit List';
        $this->view->first_name = '';
        $this->view->last_name = '';
        $this->view->email = '';
        $approveagentModel = new Approveagent();
        $agentModel = new Agents();
        $agentUserModel = new AgentUser();
        $id = $this->_getParam('id');
//        $mailsent = FALSE;
//        $mailsent = $this->_getParam('mailsent');
//        $date = date('Y-m-d');
//        $Corpflag = FALSE;
//        $agentProductBinding = new BindAgentProductCommission();
//        $agentProduct = $agentProductBinding->getAgentBinding($id, $date );
//        if (!empty($agentProduct)){
//        foreach($agentProduct as $agntprod){
//           
//            if($agntprod['program_type'] == PROGRAM_TYPE_CORP)
//            {
//                $Corpflag = TRUE;
//                break;
//            }
//           
//        }
//        }
//         $authEmailVerifiedStatus = $agentModel->getAgentAuthEmailVerifySatus($id);
//       
//         if($Corpflag == TRUE && $authEmailVerifiedStatus['auth_email'] != '' && $authEmailVerifiedStatus['auth_email_verification_status'] != STATUS_VERIFIED && $authEmailVerifiedStatus['auth_email_verification_id'] == 0 && $mailsent == FALSE) {
//          $url = '/approvedagent/resendauthemailverificationemail?id='.$authEmailVerifiedStatus['id'].'&agent_id='.$id;
//          $link =  '<a href="'. $this->formatURL($url) . '" title="Resend Email">Resend Mail</a>'; 
//         
//          $this->_helper->FlashMessenger(
//                    array(
//                        'msg-error' => 'Secondary Email is not verified, agent needs to check email for further instructions. '.$link,
//                    )
//                );   
//        }
//        if($mailsent == TRUE){
//            $this->_helper->FlashMessenger(
//                    array(
//                        'msg-success' => 'Secondary Email verification link has been sent',
//                    )
//                );  
//        }
        $resArr = $approveagentModel->getagentDetails($id);
        $agentType = $agentUserModel->getAgentType($id);
        if($agentType == DISTRIBUTOR_AGENT || $agentType == SUB_AGENT) {
            $this->view->isSubAgent = TRUE;
        } else {
            $this->view->isSubAgent = FALSE;            
        }
        
        $this->view->id = $id;
        $this->view->first_name = $resArr['first_name'];
        $this->view->last_name = $resArr['last_name'];
        $this->view->email = $resArr['email'];
        $this->view->paginator = $approveagentModel->getagentproductDetails($this->_getPage(),$id);
        $this->view->baseUrl = $this->_baseUrl;
        $this->view->controllerName = $this->_controllerName;
       
    }
    
    
    public function agentproductAction()
    {
       
        $this->title = 'Assign Product, Product Limit, Commission & Fee';
        $id = $this->_getParam('id');
        
//        $HICflag = FALSE;
//        $date = date('Y-m-d');
//        $agentProductBinding = new BindAgentProductCommission();
//        $agentProduct = $agentProductBinding->getAgentBinding($id, $date );
//        if (!empty($agentProduct)){
//        foreach($agentProduct as $agntprod){
//            if($agntprod['program_type'] == PROGRAM_TYPE_CORP)
//            {
//                $HICflag = TRUE;
//                break;
//            }
//           
//        }
//        }
//        $this->title = 'Edit Product, Product Limit, Commission Plan & Fee';
//        $id = $this->_getParam('id');
//        if($HICflag){
//           
//             $this->_redirect($this->formatURL('/agents/addauthemail?id='.$id));
//        }
//        
        $form = new AgentproductForm();
        $agentproductModel = new BindAgentProductCommission();
        //$approveagentModel = new Approveagent();
        $productId = $agentproductModel->findProductById($id);
       
        
           
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
              
                $formData  = $this->_request->getPost();
                // Assign product,product limit and commission plan to agent
               
                $res = $this->assignAgentProduct($formData, $id);
                if ($res){
                    $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'New product, product limit, commission plan assigned to agent',
                    )
                );
                    $this->_redirect($this->formatURL('/approvedagent/index?id='.$id));
                }
                else
                {
                    $this->_helper->FlashMessenger(
                        array(
                        'msg-error' => 'Current assigned product cannot be reassigned to agent',
                        )
                    );
                }
        
        } // valid
           
            $row = $this->_request->getPost();
            $this->view->item = $row;
            $form->populate($row);
            
            $form->getElement('product')->setValue($row['product_id']);
            $form->getElement('limit')->setValue($row['product_limit_id']); 
        }
        //echo 'PID= '.$productId['product_id'];
        if(isset($productId['product_id'])){
            $form->getElement('product')->setValue($productId['product_id']);
        }
       
        $this->view->form = $form;
    }
    
     private function assignAgentProduct($formData, $id)
    {
         $user = Zend_Auth::getInstance()->getIdentity();
         $agentproductModel = new BindAgentProductCommission();
         $chkAgentProduct = $agentproductModel->chkDuplicateAgentProduct($id, $formData['product_id']);
                if($chkAgentProduct)
                {
                    $dateStart = Util::returnDateFormatted($formData['date_start'], "d-m-Y", "Y-m-d", "-");
                    $data = array('agent_id'=>$id, 
                           'product_id'=>$formData['product_id'],
                           'product_limit_id'=>$formData['product_limit_id'],
                           'plan_commission_id'=>$formData['plan_commission_id'],
                           'plan_fee_id'=>$formData['plan_fee_id'],
                           'by_ops_id' => $user->id,
                           'date_start' => $dateStart
                          ); 
                    //echo '<pre>';print_r($data);     
                    $res = $agentproductModel->agentProduct($data); 
               
              
                    if($res > 0)
                    {
                      return $res;
                    }
            
                }
                else
                {
                    return FALSE;
                } 
     }
     
     private function assignAgentLimit($formData,$id)
    {
                $user = Zend_Auth::getInstance()->getIdentity();

                $agentlimitModel = new Agentlimit();
          
              //$row = $form->getValues();
                $row['agent_id'] = $id;
                $row['by_ops_id'] = $user->id;
                $startDate = Util::returnDateFormatted($formData['date_start'], "d-m-Y", "Y-m-d", "-");
                $row['date_start'] = $startDate;
                $row['agent_limit_id'] = $formData['agent_limit_id'];
               
               // echo '<pre>';print_r($row);
                
                $res = $agentlimitModel->saveagentlimit($row);
             
                if ($res > 0){
                    return TRUE;
                 }
                else{
                      return FALSE;
                }
                
     }
     
     public function editagentproductAction()
    {
        
        $user = Zend_Auth::getInstance()->getIdentity();
       
        $HICflag = FALSE;
        $date = date('Y-m-d');
        $agentProductBinding = new BindAgentProductCommission();
        $agentProduct = $agentProductBinding->getAgentBinding($id, $date );
        if (!empty($agentProduct)){
        foreach($agentProduct as $agntprod){
            if($agntprod['program_type'] == PROGRAM_TYPE_HIC)
            {
                $HICflag = TRUE;
                break;
            }
           
        }
        }
        $this->title = 'Edit Product, Product Limit, Commission Plan & Fee';
        $id = $this->_getParam('id');
        if($HICflag){
           
             $this->_redirect($this->formatURL('/agents/addauthemail?id='.$id));
        }
        
        
        $form = new EditagentproductForm();
        $agentproductModel = new BindAgentProductCommission();
        
        $details = $agentproductModel->findById($id);
        $prodDetails = $agentproductModel->getProductById($id);
        
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
              
                $formData  = $this->_request->getPost();
                $startDate = Util::returnDateFormatted($form->getValue('date_start'), "d-m-Y", "Y-m-d", "-");
                $data = array( 'agent_id'=>$details['agent_id'],
                           'product_id'=>$details['product_id'],
                           'product_limit_id'=>$formData['product_limit_id'],
                           'plan_commission_id'=>$formData['plan_commission_id'],
                           'plan_fee_id'=>$formData['plan_fee_id'],
                           'by_ops_id' => $user->id,
                           'date_start' => $startDate
                          ); 
                
                $chkLastdetails  = $agentproductModel->checkLastdetails($details['agent_id'], $details['product_id']); 
                $datefromDb = $chkLastdetails['date_start'];
                 $datefromForm = $startDate;
                 $datetime1 = new DateTime($datefromDb);
                 $datetime2 = new DateTime($datefromForm);
                 //$interval = $datetime2->diff($datetime1);
                 //$days =  $interval->format('%d');
                
                 
                if($prodDetails['program_type'] == PROGRAM_TYPE_MVC && $chkLastdetails['product_limit_id'] == $form->getvalue('product_limit_id') && $chkLastdetails['plan_commission_id'] == $form->getvalue('plan_commission_id') && $datetime2 >= $datetime1)
                {
                    
                     $this->_helper->FlashMessenger(
                        array(
                        'msg-error' => 'This is the current product limit & commission plan assigned',
                        )
                    );
                     
                }
                elseif($prodDetails['program_type'] == PROGRAM_TYPE_REMIT && $chkLastdetails['product_limit_id'] == $form->getvalue('product_limit_id') && $chkLastdetails['plan_commission_id'] == $form->getvalue('plan_commission_id') && $chkLastdetails['plan_fee_id'] == $form->getvalue('plan_fee_id') && $datetime2 >= $datetime1)
                {
                    
                     $this->_helper->FlashMessenger(
                        array(
                        'msg-error' => 'This is the current product limit, commission plan & fee plan assigned',
                        )
                    );
                     
                }
                else if ($datetime2 < $datetime1){
                   $this->_helper->FlashMessenger(
                        array(
                        'msg-error' => "Date selected cannot be less than current agent setup's start date",
                        //'msg-error' => "The date is already in the existing records. Please select another start date.",
                        )
                    );
                     
                      
                }  
                else
                {
                    $res = $agentproductModel->updagentProduct($id , $startDate); 
                    $agentproductModel->agentProduct($data);
                    
                    if($res > 0)
                    {
                        $agentModel = new AgentUser();
                        $agenttype = $agentModel->getAgentType($details['agent_id']);

                        if($agenttype == SUPER_AGENT || $agenttype == DISTRIBUTOR_AGENT) {
                            $info = $agentModel->getChildrenInfo($details['agent_id'], $agenttype);
                            if(!empty($info)) {
                                foreach($info as $val) {
                                    $chkLastdetails = $agentproductModel->checkLastdetails($val['to_object_id'], $details['product_id']);
                                    $updateArr = array( 'agent_id'=>$val['to_object_id'],
                                        'product_id'=>$details['product_id'],
                                        'product_limit_id'=>$formData['product_limit_id'],
                                        'plan_commission_id'=>$formData['plan_commission_id'],
                                        'plan_fee_id'=>$formData['plan_fee_id'],
                                        'by_ops_id' => $user->id,
                                        'date_start' => $startDate
                                    );

                                    $agentproductModel->updagentProduct($chkLastdetails['id'] , $startDate);
                                    $agentproductModel->agentProduct($updateArr);

                                    if($agenttype == SUPER_AGENT) {
                                        $subInfo = $agentModel->getChildrenInfo($val['to_object_id'], DISTRIBUTOR_AGENT);
                                        if(!empty($subInfo)) {
                                            foreach($subInfo as $subagent) {
                                                $chkLastdetails = $agentproductModel->checkLastdetails($subagent['to_object_id'], $details['product_id']);
                                                $subupdateArr = array( 'agent_id'=>$subagent['to_object_id'],
                                                    'product_id'=>$details['product_id'],
                                                    'product_limit_id'=>$formData['product_limit_id'],
                                                    'plan_commission_id'=>$formData['plan_commission_id'],
                                                    'plan_fee_id'=>$formData['plan_fee_id'],
                                                    'by_ops_id' => $user->id,
                                                    'date_start' => $startDate
                                                );
                                                $agentproductModel->updagentProduct($chkLastdetails['id'] , $startDate);
                                                $agentproductModel->agentProduct($subupdateArr);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        
                        $this->_helper->FlashMessenger(
                          array(
                              'msg-success' => 'Product, product limit, commission plan, fee plan edited successfully for agent',
                          )
                      );
                      $this->_redirect($this->formatURL('/approvedagent/index?id='.$details['agent_id']));
                   }
                }
            }
        
        }
        $details = $agentproductModel->findById($id);
        //$row = $approveagentModel->findById($id);
        $details['date_start'] = Util::returnDateFormatted($details['date_start'], "Y-m-d", "d-m-Y", "-");
        $form->populate($details);
        $form->getElement('id')->setValue($id);
        $form->getElement('product')->setValue($details['product_id']);
        $form->getElement('limit')->setValue($details['product_limit_id']);
        $form->getElement('program_type')->setValue($prodDetails['program_type']);
        $this->view->productName = $prodDetails['name'];
        $this->view->item = $details;
        $this->view->form = $form;
    }
    
    public function deleteagentproductAction()
    {
         
        $this->title = 'Delete Assigned Agent Product, Product Limit, Commission Plan and Fee';
        $id = $this->_getParam('id');
        $prevId = $this->_getParam('pid');
        
        $form = new DeleteAgentProductForm();
        $agentproductModel = new BindAgentProductCommission();
        $dataArr = $agentproductModel->findById($id);
        //echo "<pre>";print_r($dataArr);exit;
        //$lastId = $agentproductModel->getlastId($dataArr['agent_id']);
        $form->getElement('id')->setValue($id);
        $form->getElement('pid')->setValue($prevId);
        $form->getElement('agentId')->setValue($dataArr['agent_id']);
        
        if($this->getRequest()->isPost()){
            
            if($form->isValid($this->getRequest()->getPost())){
               
            
                
                $res = $agentproductModel->deleteBindAgentProductCommission($id, $prevId );
               if ($res > 0){
                    $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'Assigned agent product, product limit, commission plan and fee plan successfully deleted.',
                    )
                );
                 $this->_redirect($this->formatURL('/approvedagent/index?id='.$dataArr['agent_id']));
                }
                else{
                  $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'Assigned agent product, product limit, commission plan and fee plan could not be deleted',
                    )
                );
                 
                }
                
            }
            }
         $detailsArr = $agentproductModel->getActiveAgentproductlimit($id);
         $agentName = $agentproductModel->getAgentName($dataArr['agent_id']);
         $this->view->agentName = ucfirst($agentName['first_name'])." ".ucfirst($agentName['last_name']);
         $this->view->item = (object)$detailsArr;
         $this->view->form = $form;
    }
   /*
    * Assign product, product limit, commission plan and agent limit 
    * at one place immediately after the Agent get approved
    */ 
    public function assignagentlimitsAction()
    {
        
        $this->title = 'Assign Product, Product Limit, Commission, Fee and Agent Limit';
        $id = $this->_getParam('id');
        
        $form = new AgentLimitsForm();
        $agentproductModel = new BindAgentProductCommission();
       
        $productId = $agentproductModel->findProductById($id);
        
        if (!empty($productId))
        {
             $this->_redirect($this->formatURL('/approvedagent/index?id='.$id));
        }
        
           
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
              
                $formData  = $this->_request->getPost();
                // Agent product limit and commission plan assignment
                $res = $this->assignAgentProduct($formData, $id);
                // Agent limit assignment
                $limit = $this->assignAgentLimit($formData,$id);
                
                if ($res){
                    $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'New product, product limit, commission plan, fee plan and agent limit assigned to agent',
                    )
                );
                    $this->_redirect($this->formatURL('/agentsummary/view?id='.$id));
                }
                else
                {
                    $this->_helper->FlashMessenger(
                        array(
                        'msg-error' => 'Could not be assigned',
                        )
                    );
                }
                
            } // valid
        
            $row = $this->_request->getPost();
            $this->view->item = $row;
            $form->populate($row);
            $form->getElement('product')->setValue($row['product_id']);
            $form->getElement('limit')->setValue($row['product_limit_id']); 
        
        }
        //echo 'PID= '.$productId['product_id'];
        if(isset($productId['product_id'])) {
            $form->getElement('product')->setValue($productId['product_id']);
        }
       
        $this->view->form = $form;
    }
    
    public function resendauthemailverificationemailAction(){
       $id = $this->_getParam('id');;
       $agent_id = $this->_getParam('agent_id');;
       $approveagentModel = new Approveagent();  
       $agentModel = new Agents();
       $auth_verification_code = Util::hashVerification($id);
       $param = $agentModel->findById($agent_id);
       $detailsArr = array('id' =>$param['id'],'first_name'=>$param['first_name'],'last_name'=>$param['last_name'],'name'=>$param['name'],
             'email'=>$param['email'],'auth_email'=>$param['auth_email'],'agent_code'=>$param['agent_code']);
         
       $auth_activation_id = $approveagentModel->sendAuthVerificationCode($id,$auth_verification_code,$detailsArr);
        $this->_redirect($this->formatURL('/approvedagent/index?id='.$agent_id.'&mailsent=TRUE'));
       
    }
            
}