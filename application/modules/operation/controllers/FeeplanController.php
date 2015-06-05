<?php
/**
 * Allow the admins to manage critical info, users, groups, permissions, etc.
 *
 * @category backoffice
 * @package backoffice_controllers
 * @copyright company
 */

class FeeplanController extends App_Operation_Controller
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
    
    /* indexAction will show the list of fee plans
     */
    public function indexAction(){

            //echo "<pre>";print_r($session);
        $this->title = 'Fee Plans';
        $feeplanModel = new FeePlan();
        $this->view->paginator = $feeplanModel->findAll($this->_getPage());
    }
    
    
      /**
     * Allows the user to add another fee plan in the application
     *
     * @access public
     * @return void
     */
    public function addAction(){
        
        $this->title = 'Add a new Commission Plan';
        $user = Zend_Auth::getInstance()->getIdentity();
        //$form = new AgentassigngroupForm();
        $form = new AddFeePlanForm();
        $feeplanModel = new FeePlan();
        //echo "<pre>";print_r($form);exit;
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
                $row = $form->getValues();
                $row['by_ops_id'] = $user->id;
               
                $res = $feeplanModel->add($row);
               if($res > 0){
                $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'The Fee Plan was successfully added',
                    )
                );                
                $this->_redirect($this->formatURL('/feeplan/addfeeitems?fp_id='.$res));
               }
              
               else if($res == 'name_dup'){
                $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'The Fee plan name exists',
                    )
                );
                
                
               }
               
                
            }
           
        }
         $form->populate($form->getValues());
        $this->view->form = $form;
    }
     
    public function feeitemsAction(){
        
        
        $this->title = 'Fee Plan Items';
       // $form = new FeeItemForm();
        $feeplanModel = new FeePlan();
        $fid = ($this->_getParam('fid') > 0) ? $this->_getParam('fid'): 0;
        
        $bindPlanItems = $feeplanModel->getBindPlanItems();
           
        $this->view->paginator = $feeplanModel->findItemsById($fid,$this->_getPage());
        $detailArr = $feeplanModel->finddetailsById($fid);
        
        $this->view->name = $detailArr['name'];
        $this->view->fid = $detailArr['id'];
       
        $this->view->bindPlanItems = $bindPlanItems;
    }
    
    
    public function addfeeitemsAction(){
        
        
        $this->title = 'Add New Fee Plan';
        $m = new App\Messaging\System\Operation();

        $form = new FeeitemForm();
       
        $feeplanModel = new FeePlan();
        $fid = $this->_getParam('fp_id');
        $form->getElement('fid')->setValue($fid);
        $this->view->form = $form;

        if($this->getRequest()->isPost()){
           
            if($form->isValid($this->getRequest()->getPost())){
               
                $row = $form->getValues();
               
                $checktypecode =  $feeplanModel->chkDuplicateTypecode($row['typecode'],$fid);
            
            
                if($checktypecode)
                {
                    
                    $rowtc = $feeplanModel->getTypecodeDetails($row['typecode']);
                  
                    //unset($row['code']);
      
                    $rowArr = array(
                        'typecode' =>$row['typecode'] ,
                        'txn_flat' =>$row['txn_flat'] ,
                        'txn_pcnt' =>$row['txn_pcnt'] ,
                        'txn_min' =>$row['txn_min'] ,
                        'txn_max' =>$row['txn_max'] ,
                        //'cid' =>$row['cid'] ,
                        'plan_fee_id' => $fid,
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
                    //echo '<pre>';print_r($rowArr);exit;
                    $feeplanModel->insertItem($rowArr);
                    
                    // SEND NOTIFICATION TO ADMIN USERS
                    
                  $feeDetails = $feeplanModel->finddetailsById($fid);
                  $mailData['email'] = ADMIN_EMAIL_IDS;
                  $mailData['product_name'] = 'NA';
                  $mailData['limit_category'] = 'FEE PLAN: '.$feeDetails['name'];
                  $mailData['param_name'] = 'FEE ITEM';
                  $mailData['old_value'] = NEW_ADDITION;
                  $mailData['new_value'] = $newArr;
                  $m->limitUpdates($mailData); 
                  
                  
                    $this->_helper->FlashMessenger(
                        array(
                            'msg-success' => 'Fee plan item successfully added',
                        )
                    );
                     $this->_redirect($this->formatURL('/feeplan/feeitems?fid='.$fid));
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
                
        $detailArr = $feeplanModel->finddetailsById($fid);
        $this->view->name = $detailArr['name'];
        
    }
    
      public function edititemAction(){
        
        
        $this->title = 'Edit Fee Plan';
        $m = new App\Messaging\System\Operation();
        $form = new FeeitemForm();
        $feeplanModel = new FeePlan();
        $id = $this->_getParam('id');
        $detailsArr = $feeplanModel->itemsById($id); 
        
        $form->getElement('code')->setValue($detailsArr['typecode']);
        $form->getElement('fid')->setValue($detailsArr['plan_fee_id']);
        $this->view->form = $form;
        
        if($this->getRequest()->isPost()){
           
            if($form->isValid($this->getRequest()->getPost())){
               
                $row = $form->getValues();
               
                // $row['plan_commission_id'] = $cid;
                //$checktypecode =  $commissionplanModel->chkDuplicateTypecode($row['typecode'],$detailsArr['plan_commission_id']);
            
                
                $rowtc = $feeplanModel->getTypecodeDetails($row['typecode']);

                $feeplanModel->updateItem($id);
                $rowArr = array(
                    'typecode' =>$row['typecode'] ,
                    'txn_flat' =>$row['txn_flat'] ,
                    'txn_pcnt' =>$row['txn_pcnt'] ,
                    'txn_min' =>$row['txn_min'] ,
                    'txn_max' =>$row['txn_max'] ,
                    //'cid' =>$row['cid'] ,
                    'plan_fee_id' => $row['fid'],
                    'typecode_name' => $rowtc['name']
                );
                
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
                    
                  $feeDetails = $feeplanModel->finddetailsById($detailsArr['plan_fee_id']);
                  $mailData['email'] = ADMIN_EMAIL_IDS;
                  $mailData['product_name'] = 'NA';
                  $mailData['limit_category'] = 'FEE PLAN: '.$feeDetails['name'];
                  $mailData['param_name'] = 'FEE ITEMS';
                  $mailData['old_value'] = $oldArr;
                  $mailData['new_value'] = $newArr;
                  $m->limitUpdates($mailData); 
                  
                $feeplanModel->insertItem($rowArr);
                $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'Fee plan successfully edited',
                    )
                );
                 $this->_redirect($this->formatURL('/feeplan/feeitems?fid='.$detailsArr['plan_fee_id']));
                 
            }
        } 
        $detailsArr = $feeplanModel->itemsById($id);

        $detArr = array ('txn_flat'=> Util::numberFormat($detailsArr['txn_flat'], FLAG_NO),
          'txn_pcnt' => Util::numberFormat($detailsArr['txn_pcnt'], FLAG_NO),
          'txn_min' =>Util::numberFormat($detailsArr['txn_min'], FLAG_NO) ,
          'txn_max' =>Util::numberFormat($detailsArr['txn_max'], FLAG_NO) );

        $mergeArr = array_merge($detailsArr,$detArr);

         $form->populate($mergeArr);

         $this->view->item = $detailsArr;

         $detArr = $feeplanModel->finddetailsById($detailsArr['plan_fee_id']);

         $this->view->name = $detArr['name'];
     
        
    }
    public function deleteitemAction(){
        
        $this->title = 'Delete Transaction Type';
        
        $form = new DeleteFeeItemForm(array('method' => 'post'));
        $feeplanModel = new FeePlan();
        $id = $this->_getParam('id');   
        $detailsArr = $feeplanModel->itemsById($id); 
        $form->getElement('id')->setValue($id);
        $form->getElement('fid')->setValue($detailsArr['plan_fee_id']);
        $this->view->form = $form;
        $formData  = $this->_request->getPost();          
          
        if($this->getRequest()->isPost()){
           
            if($form->isValid($this->getRequest()->getPost())){
                    
                    $feeplanModel->deleteItem($id);
                    
                    $this->_helper->FlashMessenger(
                        array(
                            'msg-success' => 'Commission plan item successfully deleted',
                        )
                    );
                     $this->_redirect($this->formatURL('/feeplan/feeitems?fid='.$detailsArr['plan_fee_id']));
                 
            }
        } 
                
            $this->view->name = $detailsArr['name'];     

            $this->view->item = (object)$detailsArr;     
        
    }
     
}