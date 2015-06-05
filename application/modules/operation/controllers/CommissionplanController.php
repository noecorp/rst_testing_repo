<?php
/**
 * Allow the admins to manage critical info, users, groups, permissions, etc.
 *
 * @category backoffice
 * @package backoffice_controllers
 * @copyright company
 */

class CommissionplanController extends App_Operation_Controller
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

            //echo "<pre>";print_r($session);
        $this->title = 'Agent Commissions';
        
        $commissionplanModel = new CommissionPlan();
        $this->view->paginator = $commissionplanModel->findAll($this->_getPage());
    }
    
    
      /**
     * Allows the user to add another privilege in the application
     *
     * @access public
     * @return void
     */
    public function addAction(){
        $this->title = 'Add a new Commission Plan';
        $user = Zend_Auth::getInstance()->getIdentity();
        //$form = new AgentassigngroupForm();
        $form = new CommissionPlanForm();
        $commissionplanModel = new CommissionPlan();
        //echo "<pre>";print_r($form);exit;
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
                $row = $form->getValues();
                $row['by_ops_id'] = $user->id;
               
                $res = $commissionplanModel->add($row);
               if($res > 0){
                $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'The Commission Plan was successfully added',
                    )
                );                
                $this->_redirect($this->formatURL('/commissionplan/addcommitems?cp_id='.$res));
               }
              
               else if($res == 'name_dup'){
                $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'The Commission plan name exists',
                    )
                );
                
                
               }
               
                
            }
           
        }
         $form->populate($form->getValues());
        $this->view->form = $form;
    }
     
    public function commitemsAction(){
        
        
        $this->title = 'Commission Plan';
        
        $form = new CommissionitemForm();
        $commissionplanModel = new CommissionPlan();
        $cid = ($this->_getParam('cid') > 0) ? $this->_getParam('cid'): 0;
        $bindPlanItems = $commissionplanModel->getBindPlanItems();
        $this->view->paginator = $commissionplanModel->findItemsById($cid,$this->_getPage());
        
        $detailArr = $commissionplanModel->finddetailsById($cid);
       
        $this->view->name = $detailArr['name'];
        $this->view->cid = $detailArr['id'];
       
       
       
        $this->view->bindPlanItems = $bindPlanItems;
    }
    
    
    public function addcommitemsAction(){
        
        
        $this->title = 'Add New Commission Plan';
        $m = new App\Messaging\System\Operation();

        $form = new CommissionitemForm();
        $commissionplanModel = new CommissionPlan();
        $cid = $this->_getParam('cp_id');
        $form->getElement('cid')->setValue($cid);
        $this->view->form = $form;

        if($this->getRequest()->isPost()){
           
            if($form->isValid($this->getRequest()->getPost())){
               
                $row = $form->getValues();
               
                $checktypecode =  $commissionplanModel->chkDuplicateTypecode($row['typecode'],$cid);
            
                if($checktypecode)
                {
                    $rowtc = $commissionplanModel->getTypecodeDetails($row['typecode']);
                   
                    //unset($row['code']);
      
                    $rowArr = array(
                        'typecode' =>$row['typecode'] ,
                        'txn_flat' =>$row['txn_flat'] ,
                        'txn_pcnt' =>$row['txn_pcnt'] ,
                        'txn_min' =>$row['txn_min'] ,
                        'txn_max' =>$row['txn_max'] ,
                        //'cid' =>$row['cid'] ,
                        'plan_commission_id' => $cid,
                        'typecode_name' => $rowtc['name']
                    );
                    
                    $newArr = array(
                        'Typecode' =>$row['typecode'] ,
                        'Flat Rate' =>$row['txn_flat'] ,
                        'Percent' =>$row['txn_pcnt'] ,
                        'Minimum' =>$row['txn_min'] ,
                        'Maximum' =>$row['txn_max'] ,
                        'Description' => $rowtc['name']
                    );
                    $commissionplanModel->insertItem($rowArr);
                    
                      // SEND NOTIFICATION TO ADMIN USERS
                    
                  $commDetails = $commissionplanModel->finddetailsById($cid);
                  $mailData['email'] = ADMIN_EMAIL_IDS;
                  $mailData['product_name'] = 'NA';
                  $mailData['limit_category'] = 'COMMISSION PLAN: '.$commDetails['name'];
                  $mailData['param_name'] = 'COMMISSION ITEM';
                  $mailData['old_value'] = NEW_ADDITION;
                  $mailData['new_value'] = $newArr;
                  $m->limitUpdates($mailData); 
                    $this->_helper->FlashMessenger(
                        array(
                            'msg-success' => 'Commission plan item successfully added',
                        )
                    );
                     $this->_redirect($this->formatURL('/commissionplan/commitems?cid='.$cid));
                 }     
                
                else {
                    
                    $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'Transaction type already exists',
                    )
                    );
                   
                }
                $data = $form->getValues();
                $form->populate($data);
                
                $this->view->item = $data;
            }
        } 
                
        $detailArr = $commissionplanModel->finddetailsById($cid);
        $this->view->name = $detailArr['name'];
        
    }
    
      public function edititemAction(){
        
        
        $this->title = 'Edit Commission Plan';
        $m = new App\Messaging\System\Operation();
        $form = new CommissionitemForm();
        $commissionplanModel = new CommissionPlan();
        $id = $this->_getParam('id');
        $detailsArr = $commissionplanModel->itemsById($id); 
        
        $form->getElement('code')->setValue($detailsArr['typecode']);
        $form->getElement('cid')->setValue($detailsArr['plan_commission_id']);
        $this->view->form = $form;

        if($this->getRequest()->isPost()){
           
            if($form->isValid($this->getRequest()->getPost())){
               
                $row = $form->getValues();
               
                // $row['plan_commission_id'] = $cid;
                //$checktypecode =  $commissionplanModel->chkDuplicateTypecode($row['typecode'],$detailsArr['plan_commission_id']);
            
                
                $rowtc = $commissionplanModel->getTypecodeDetails($row['typecode']);

                $commissionplanModel->updateItem($id);
                $rowArr = array(
                    'typecode' =>$row['typecode'] ,
                    'txn_flat' =>$row['txn_flat'] ,
                    'txn_pcnt' =>$row['txn_pcnt'] ,
                    'txn_min' =>$row['txn_min'] ,
                    'txn_max' =>$row['txn_max'] ,
                    //'cid' =>$row['cid'] ,
                    'plan_commission_id' => $row['cid'],
                    'typecode_name' => $rowtc['name']
                );
                //echo '<pre>';print_r($rowArr);exit;
                $commissionplanModel->insertItem($rowArr);
                 $oldArr = array(
                        'Typecode' =>$detailsArr['typecode'] ,
                        'Flat Rate' =>$detailsArr['txn_flat'] ,
                        'Percent' =>$detailsArr['txn_pcnt'] ,
                        'Minimum' =>$detailsArr['txn_min'] ,
                        'Maximum' =>$detailsArr['txn_max'] ,
                        'Description' => $rowtc['name']
                    );
                 $newArr = array(
                        'Typecode' =>$row['typecode'] ,
                        'Flat Rate' =>$row['txn_flat'] ,
                        'Percent' =>$row['txn_pcnt'] ,
                        'Minimum' =>$row['txn_min'] ,
                        'Maximum' =>$row['txn_max'] ,
                        'Description' => $rowtc['name']
                    );
                // SEND NOTIFICATION TO ADMIN USERS
                    
                  $commDetails = $commissionplanModel->finddetailsById($detailsArr['plan_commission_id']);
                  $mailData['email'] = ADMIN_EMAIL_IDS;
                  $mailData['product_name'] = 'NA';
                  $mailData['limit_category'] = 'COMMISSION PLAN: '.$commDetails['name'];
                  $mailData['param_name'] = 'COMMISSION ITEM';
                  $mailData['old_value'] = $oldArr;
                  $mailData['new_value'] = $newArr;
                  $m->limitUpdates($mailData); 
                $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'Commission plan successfully edited',
                    )
                );
                 $this->_redirect($this->formatURL('/commissionplan/commitems?cid='.$detailsArr['plan_commission_id']));
                 
            }
        } 
        $detailsArr = $commissionplanModel->itemsById($id);

        $detArr = array ('txn_flat'=> Util::numberFormat($detailsArr['txn_flat'], FLAG_NO),
          'txn_pcnt' => Util::numberFormat($detailsArr['txn_pcnt'], FLAG_NO),
          'txn_min' =>Util::numberFormat($detailsArr['txn_min'], FLAG_NO) ,
          'txn_max' =>Util::numberFormat($detailsArr['txn_max'], FLAG_NO) );

        $mergeArr = array_merge($detailsArr,$detArr);

         $form->populate($mergeArr);

         $this->view->item = $detailsArr;

         $detArr = $commissionplanModel->finddetailsById($detailsArr['plan_commission_id']);

         $this->view->name = $detArr['name'];
     
        
    }
    public function deleteitemAction(){
        
        
        $this->title = 'Delete Transaction Type';
        $m = new App\Messaging\System\Operation();
        $form = new DeleteItemForm();
        $commissionplanModel = new CommissionPlan();
        $id = $this->_getParam('id');
        $detailsArr = $commissionplanModel->itemsById($id); 
        $form->getElement('id')->setValue($id);
        $form->getElement('cid')->setValue($detailsArr['plan_commission_id']);
        $this->view->form = $form;

        if($this->getRequest()->isPost()){
           
            if($form->isValid($this->getRequest()->getPost())){
             
               
                
                
                    
                    $commissionplanModel->deleteItem($id);
                    $oldArr = array(
                        'Typecode' =>$detailsArr['typecode'] ,
                        'Flat Rate' =>$detailsArr['txn_flat'] ,
                        'Percent' =>$detailsArr['txn_pcnt'] ,
                        'Minimum' =>$detailsArr['txn_min'] ,
                        'Maximum' =>$detailsArr['txn_max'] ,
                        'Description' => $rowtc['name']
                    );
                
                // SEND NOTIFICATION TO ADMIN USERS
                    
                  $commDetails = $commissionplanModel->finddetailsById($detailsArr['plan_commission_id']);
                  $mailData['email'] = ADMIN_EMAIL_IDS;
                  $mailData['product_name'] = 'NA';
                  $mailData['limit_category'] = 'COMMISSION PLAN: '.$commDetails['name'];
                  $mailData['param_name'] = 'COMMISSION ITEM';
                  $mailData['old_value'] = $oldArr;
                  $mailData['new_value'] = DELETION;
                  $m->limitUpdates($mailData); 
                    $this->_helper->FlashMessenger(
                        array(
                            'msg-success' => 'Commission plan item successfully deleted',
                        )
                    );
                     $this->_redirect($this->formatURL('/commissionplan/commitems?cid='.$detailsArr['plan_commission_id']));
                 
            }
        } 
                
                $this->view->name = $detailsArr['name'];     
     
                $this->view->item = (object)$detailsArr;     
     
        
    }
}