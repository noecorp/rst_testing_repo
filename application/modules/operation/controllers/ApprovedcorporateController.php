<?php
/**
 * Allow the admins to manage Agent Bank, Program Type, Product, Product Limit, Commission, Fee.
 *
 * @category Agent fee
 * @package operation_module
 * @copyright Transerv
 */

class ApprovedcorporateController extends App_Operation_Controller
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
        $approvecorporateModel = new Approvecorporate();
        $agentModel = new Corporates();
        $agentUserModel = new CorporateUser();
        $id = $this->_getParam('id');

        $resArr = $approvecorporateModel->getcorporateDetails($id);
        $agentType = $agentUserModel->getCorporateType($id);
        if($agentType == LOCAL_CORPORATE) {
            $this->view->isSubAgent = TRUE;
        } else {
            $this->view->isSubAgent = FALSE;            
        }
        
        $this->view->id = $id;
        $this->view->first_name = $resArr['first_name'];
        $this->view->last_name = $resArr['last_name'];
        $this->view->email = $resArr['email'];
        $this->view->paginator = $approvecorporateModel->getcorporateproductDetails($this->_getPage(),$id);
        $this->view->baseUrl = $this->_baseUrl;
        $this->view->controllerName = $this->_controllerName;
       
    }
    
    
    public function corporateproductAction()
    {
       
        $this->title = 'Assign Product';
        $id = $this->_getParam('id');

        $form = new CorporateProductForm();
        $agentproductModel = new BindCorporateProductCommission();
        $productId = $agentproductModel->findProductById($id);
       
        
           
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
              
                $formData  = $this->_request->getPost();
                // Assign product,product limit and commission plan to agent
                 $res = $this->assignCorporateProduct($formData, $id);
                if ($res){
                    $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'New product assigned to corporate',
                    )
                );
                    $this->_redirect($this->formatURL('/approvedcorporate/index?id='.$id));
                }
                else
                {
                    $this->_helper->FlashMessenger(
                        array(
                        'msg-error' => 'Current assigned product cannot be reassigned to corporate',
                        )
                    );
                }
        
        } // valid
           
            $row = $this->_request->getPost();
            $this->view->item = $row;
            $form->populate($row);
            
            $form->getElement('product')->setValue($row['product_id']);
            $form->getElement('limit')->setValue($row['corporate_limit_id']); 
        }
        //echo 'PID= '.$productId['product_id'];
        if(isset($productId['product_id'])){
            $form->getElement('product')->setValue($productId['product_id']);
        }
       
        $this->view->form = $form;
    }
    
     private function assignCorporateProduct($formData, $id)
    {
         $user = Zend_Auth::getInstance()->getIdentity();
         $agentproductModel = new BindCorporateProductCommission();
         $chkAgentProduct = $agentproductModel->chkDuplicateCorporateProduct($id, $formData['product_id']);
                if($chkAgentProduct)
                {
                    $dateStart = Util::returnDateFormatted($formData['date_start'], "d-m-Y", "Y-m-d", "-");
                    $data = array('corporate_id'=>$id, 
                           'product_id'=>$formData['product_id'],
                           'plan_commission_id'=>$formData['plan_commission_id'],
                           'plan_fee_id'=>$formData['plan_fee_id'],
                           'by_ops_id' => $user->id,
                           'date_start' => $dateStart
                          ); 
                    //echo '<pre>';print_r($data);     
                    $res = $agentproductModel->corporateProduct($data); 
               
              
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
     
  private function assignCorporateLimit($formData,$id)
    {
                $user = Zend_Auth::getInstance()->getIdentity();

                $agentlimitModel = new Corporatelimit();
              //$row = $form->getValues();
                $row['corporate_id'] = $id;
                $row['by_ops_id'] = $user->id;
                $startDate = Util::returnDateFormatted($formData['date_start'], "d-m-Y", "Y-m-d", "-");
                $row['date_start'] = $startDate;
                $row['corporate_limit_id'] = $formData['corporate_limit_id'];
               
                $res = $agentlimitModel->savecorporatelimit($row);
               
                if ($res > 0){
                    return TRUE;
                 }
                else{
                      return FALSE;
                }
                
     }
     
     public function editcorporateproductAction()
    {
        
        $user = Zend_Auth::getInstance()->getIdentity();
       
        $date = date('Y-m-d');
        $agentProductBinding = new BindCorporateProductCommission();
        $agentProduct = $agentProductBinding->getCorporateBinding($id, $date );
        
        $this->title = 'Edit Product, Product Limit, Commission Plan & Fee';
        $id = $this->_getParam('id');
       
        
        $form = new EditCorpproductForm();
        $agentproductModel = new BindCorporateProductCommission();
        
        $details = $agentproductModel->findById($id);
        $prodDetails = $agentproductModel->getProductById($id);
        
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
              
                $formData  = $this->_request->getPost();
                $startDate = Util::returnDateFormatted($form->getValue('date_start'), "d-m-Y", "Y-m-d", "-");
                $data = array(
                           'corporate_id'=>$details['corporate_id'],
                           'product_id'=>$details['product_id'],
                           'plan_commission_id'=>$formData['plan_commission_id'],
                           'plan_fee_id'=>$formData['plan_fee_id'],
                           'by_ops_id' => $user->id,
                           'date_start' => $startDate
                          ); 
                
                 $chkLastdetails  = $agentproductModel->checkLastdetails($details['corporate_id'], $details['product_id']); 
                 $datefromDb = $chkLastdetails['date_start'];
                 $datefromForm = $startDate;
                 $datetime1 = new DateTime($datefromDb);
                 $datetime2 = new DateTime($datefromForm);
                 //$interval = $datetime2->diff($datetime1);
                 //$days =  $interval->format('%d');
                
                 
                if($prodDetails['program_type'] == PROGRAM_TYPE_MVC && $chkLastdetails['corporate_limit_id'] == $form->getvalue('corporate_limit_id') && $chkLastdetails['plan_commission_id'] == $form->getvalue('plan_commission_id') && $datetime2 >= $datetime1)
                {
                    
                     $this->_helper->FlashMessenger(
                        array(
                        'msg-error' => 'This is the current product limit & commission plan assigned',
                        )
                    );
                     
                }
                elseif($prodDetails['program_type'] == PROGRAM_TYPE_REMIT && $chkLastdetails['corporate_limit_id'] == $form->getvalue('corporate_limit_id') && $chkLastdetails['plan_commission_id'] == $form->getvalue('plan_commission_id') && $chkLastdetails['plan_fee_id'] == $form->getvalue('plan_fee_id') && $datetime2 >= $datetime1)
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
                    $resinsert = $agentproductModel->corporateProduct($data);
              
                    if($res > 0)
                    {
                        $this->_helper->FlashMessenger(
                          array(
                              'msg-success' => 'Product, product limit, commission plan, fee plan edited successfully for agent',
                          )
                      );
                      $this->_redirect($this->formatURL('/approvedcorporate/index?id='.$details['corporate_id']));
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
        $form->getElement('limit')->setValue($details['corporate_limit_id']);
        $form->getElement('program_type')->setValue($prodDetails['program_type']);
        $this->view->productName = $prodDetails['name'];
        $this->view->item = $details;
        $this->view->form = $form;
    }
    
    public function deletecorporateproductAction()
    {
         
        $this->title = 'Delete Assigned Corporate Product, Product Limit, Commission Plan and Fee';
        $id = $this->_getParam('id');
        $prevId = $this->_getParam('pid');
        
        $form = new DeleteAgentProductForm();
        $agentproductModel = new BindAgentProductCommission();
        $dataArr = $agentproductModel->findById($id);
        //echo "<pre>";print_r($dataArr);exit;
        //$lastId = $agentproductModel->getlastId($dataArr['corporate_id']);
        $form->getElement('id')->setValue($id);
        $form->getElement('pid')->setValue($prevId);
        $form->getElement('agentId')->setValue($dataArr['corporate_id']);
        
        if($this->getRequest()->isPost()){
            
            if($form->isValid($this->getRequest()->getPost())){
               
            
                
                $res = $agentproductModel->deleteBindAgentProductCommission($id, $prevId );
               if ($res > 0){
                    $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'Assigned corporate product, product limit, commission plan and fee plan successfully deleted.',
                    )
                );
                 $this->_redirect($this->formatURL('/approvedcorporate/index?id='.$dataArr['corporate_id']));
                }
                else{
                  $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'Assigned corporate product, product limit, commission plan and fee plan could not be deleted',
                    )
                );
                 
                }
                
            }
            }
         $detailsArr = $agentproductModel->getActiveAgentproductlimit($id);
         $agentName = $agentproductModel->getAgentName($dataArr['corporate_id']);
         $this->view->agentName = ucfirst($agentName['first_name'])." ".ucfirst($agentName['last_name']);
         $this->view->item = (object)$detailsArr;
         $this->view->form = $form;
    }
   /*
    * Assign product, product limit, commission plan and agent limit 
    * at one place immediately after the Agent get approved
    */ 
    public function assigncorporatelimitsAction()
    {
        
        $this->title = 'Assign Product, Corporate Limit, Commission';
        $id = $this->_getParam('id');
        
        $form = new CorporateLimitsForm();
        $agentproductModel = new BindCorporateProductCommission();
       
        $productId = $agentproductModel->findProductById($id);
        
        if (!empty($productId))
        {
             $this->_redirect($this->formatURL('/approvedcorporate/index?id='.$id));
        }
        
           
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
              
                $formData  = $this->_request->getPost();
                // Agent product limit and commission plan assignment
                $res = $this->assignCorporateProduct($formData, $id);
                // Agent limit assignment
                $limit = $this->assignCorporateLimit($formData,$id);
                
                if ($res){
                    $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'New product, product limit, commission plan, fee plan and corporate limit assigned to corporate',
                    )
                );
                   //$this->_redirect($this->formatURL('/agentsummary/view?id='.$id));
                    $this->_redirect($this->formatURL('/approvedcorporate/index?id='.$id));
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
            $form->getElement('limit')->setValue($row['corporate_limit_id']); 
        
        }
        //echo 'PID= '.$productId['product_id'];
        if(isset($productId['product_id'])) {
            $form->getElement('product')->setValue($productId['product_id']);
        }
       
        $this->view->form = $form;
    }
    
    public function resendauthemailverificationemailAction(){
       $id = $this->_getParam('id');;
       $corporate_id = $this->_getParam('corporate_id');;
       $approveagentModel = new Approveagent();  
       $agentModel = new Agents();
       $auth_verification_code = Util::hashVerification($id);
       $param = $agentModel->findById($corporate_id);
       $detailsArr = array('id' =>$param['id'],'first_name'=>$param['first_name'],'last_name'=>$param['last_name'],'name'=>$param['name'],
             'email'=>$param['email'],'auth_email'=>$param['auth_email'],'agent_code'=>$param['agent_code']);
         
       $auth_activation_id = $approveagentModel->sendAuthVerificationCode($id,$auth_verification_code,$detailsArr);
        $this->_redirect($this->formatURL('/approvedcorporate/index?id='.$corporate_id.'&mailsent=TRUE'));
       
    }
            
}