<?php
/**
 * Allow the admins to manage product limits for MVC
 *
 * @category backoffice
 * @package backoffice_controllers
 * @copyright company
 */

class Mvc_ProductlimitController extends App_Operation_Controller
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
        
        $pid = ($this->_getParam('pid') > 0) ? $this->_getParam('pid') : 0;  
        $plid = ($this->_getParam('plid') > 0) ? $this->_getParam('plid') : 0;  
        if($pid == 0 && $plid == 0){
            $this->_redirect($this->formatURL('/product/index/'));
        }
        $productModel = new Products();
        $productlimitModel = new Mvc_Productlimit();
        $bindProducts = $productModel->getBindProductsLimits();
        $this->view->paginator = $productlimitModel->findAll($pid,$plid,$this->_getPage());
        
        if($pid > 0){
            $detailsArr = $productModel->findById($pid);
            $this->view->pid = $pid;
        }
        if($plid > 0){
            $detailsArr = $productlimitModel->findByPlId($plid);
            $this->view->pid =  $detailsArr['product_id'];
        }
       
        $this->view->name = $detailsArr['name'];
        $this->view->product_code = $detailsArr['ecs_product_code'];
       
        $this->view->bindProducts = $bindProducts;
    }
    
    
    public function addlimitAction(){
     
         $this->title = 'Add new Product Limit';
         $m = new App\Messaging\System\Operation();
         $id = $this->_getParam('pid');
         $user = Zend_Auth::getInstance()->getIdentity();
         $productModel = new Products();
         $form = new Mvc_ProductLimitForm();
         $productlimitModel = new Mvc_Productlimit();
         $form->getElement('pid')->setValue($this->_getParam('pid'));
         $detailsArr = $productModel->findById($id);
         $form->getElement('currency')->setValue($detailsArr['currency']);
        //echo "<pre>";print_r($form);exit;
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
                $row = $form->getValues();
                $row['product_id'] = $id;
                $row['by_ops_id'] = $user->id;
                
                        // gets the last max id row details
                $limitCode =  $productlimitModel->getLastProductLimit();

                $productLimitCodeFromDb =  $limitCode['product_limit_code'];

                if(strlen($productLimitCodeFromDb) > 0){

                // Get the numeric suffix
                $numeric = explode(PRODUCT_LIMIT_CODE_PREFIX,$productLimitCodeFromDb);
                $suffix = intval($numeric['1']);
                $suffix++;
                }
                else{
                   $suffix = 1;
                }
                $codeSuffix= str_pad($suffix, PRODUCT_LIMIT_CODE_SUFFIX_LENGTH,0,STR_PAD_LEFT);
                $productLimitCode= PRODUCT_LIMIT_CODE_PREFIX.$codeSuffix;

                $row['product_limit_code'] = $productLimitCode;
                
                try{
                  
                $res = $productlimitModel->add($row);
                }
                catch(Exception $e){
                     App_Logger::log($e->getMessage(), Zend_Log::ERR);
                     $addMsg = $e->getMessage();
                }
               if($res == 'added'){
            
  
                $newArr = array(
                            'Product Limit Name' => $row['name'],
                            'Currency' => $row['currency'],
                            'Minimum Amount for First Load' => $row['limit_out_first_load'],
                            'Minimum Reload Amount per Trxn' => $row['limit_out_min_txn'],
                            'Maximum Reload Amount per Trxn' => $row['limit_out_max_txn'],
                            'Max no. of Txns per Day' => $row['cnt_out_max_txn_daily'],
                            'Max Amount per Day' => $row['limit_out_max_daily'],
                            'Max no. of Txns per Month' => $row['cnt_out_max_txn_monthly'],
                            'Max Amount per Month' => $row['limit_out_max_monthly'],
                            'Max no. of Txns per Year' => $row['cnt_out_max_txn_yearly'],
                            'Max Amount per Year' => $row['limit_out_max_yearly'],
                        );
                        // SEND NOTIFICATION TO ADMIN USERS
                    
                  $mailData['email'] = ADMIN_EMAIL_IDS;
                  $mailData['product_name'] = $row['pname'];
                  $mailData['limit_category'] = 'PRODUCT LIMIT: '.$row['name'];
                  $mailData['param_name'] = 'NA';
                  $mailData['old_value'] = NEW_ADDITION;
                  $mailData['new_value'] = $newArr;
                  $m->limitUpdates($mailData); 
                $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'The Product Limit was successfully added',
                    )
                );                
//                $this->_redirect('/product/limit?pid='.$id);
                $this->_redirect($this->formatURL('/mvc_productlimit/index?pid='.$id));
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
        $form->populate($form->getValues());
        
        $this->view->name = $detailsArr['name'];
        $this->view->product_code = $detailsArr['ecs_product_code'];
        $form->getElement('pname')->setValue($detailsArr['name']);
        $this->view->form = $form;
    }
    
     public function editlimitAction(){
     
        
         $this->title = 'Edit Product Limit';
         $m = new App\Messaging\System\Operation();
         $id = $this->_getParam('id');
         $user = Zend_Auth::getInstance()->getIdentity();
         $productModel = new Products();
         $form = new Mvc_ProductLimitForm();
         $productlimitModel = new Mvc_Productlimit();
         $detailsArr = $productlimitModel->findById($id);
         $form->getElement('pid')->setValue($detailsArr['product_id']);
         $form->populate($detailsArr->toArray());
        //echo "<pre>";print_r($detailsArr);exit;
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
                $row = $form->getValues();
                $row['by_ops_id'] = $user->id;
                $row['product_id'] = $detailsArr['product_id'];
                
                        // gets the last max id row details
                $limitCode =  $productlimitModel->getLastProductLimit();

                $productLimitCodeFromDb =  $limitCode['product_limit_code'];

                if(strlen($productLimitCodeFromDb) > 0){

                // Get the numeric suffix
                $numeric = explode(PRODUCT_LIMIT_CODE_PREFIX,$productLimitCodeFromDb);
                $suffix = intval($numeric['1']);
                $suffix++;
                }
                else{
                   $suffix = 1;
                }
                $codeSuffix= str_pad($suffix, PRODUCT_LIMIT_CODE_SUFFIX_LENGTH,0,STR_PAD_LEFT);
                $productLimitCode= PRODUCT_LIMIT_CODE_PREFIX.$codeSuffix;

                
                
                
                $row['product_limit_code'] = $productLimitCode;
                
                try{
                    
                $update = $productlimitModel->editupdate($id);
               
                $res = $productlimitModel->edit($row,$detailsArr['name']);
                }
                catch(Exception $e){
                     App_Logger::log($e->getMessage(), Zend_Log::ERR);
                     $addMsg = $e->getMessage();
                }
                
               if($res == 'edited' && $update == 'updated'){
                
                 $oldArr = array(
                            'Product Limit Name' => $detailsArr['name'],
                            'Currency' => $detailsArr['currency'],
                            'Minimum Amount for First Load' => $detailsArr['limit_out_first_load'],
                            'Minimum Reload Amount per Trxn' => $detailsArr['limit_out_min_txn'],
                            'Maximum Reload Amount per Trxn' => $detailsArr['limit_out_max_txn'],
                            'Max no. of Txns per Day' => $detailsArr['cnt_out_max_txn_daily'],
                            'Max Amount per Day' => $detailsArr['limit_out_max_daily'],
                            'Max no. of Txns per Month' => $detailsArr['cnt_out_max_txn_monthly'],
                            'Max Amount per Month' => $detailsArr['limit_out_max_monthly'],
                            'Max no. of Txns per Year' => $detailsArr['cnt_out_max_txn_yearly'],
                            'Max Amount per Year' => $detailsArr['limit_out_max_yearly'],
                        );
                 
                $newArr = array(
                            'Product Limit Name' => $row['name'],
                            'Currency' => $row['currency'],
                            'Minimum Amount for First Load' => $row['limit_out_first_load'],
                            'Minimum Reload Amount per Trxn' => $row['limit_out_min_txn'],
                            'Maximum Reload Amount per Trxn' => $row['limit_out_max_txn'],
                            'Max no. of Txns per Day' => $row['cnt_out_max_txn_daily'],
                            'Max Amount per Day' => $row['limit_out_max_daily'],
                            'Max no. of Txns per Month' => $row['cnt_out_max_txn_monthly'],
                            'Max Amount per Month' => $row['limit_out_max_monthly'],
                            'Max no. of Txns per Year' => $row['cnt_out_max_txn_yearly'],
                            'Max Amount per Year' => $row['limit_out_max_yearly'],
                        );
                        // SEND NOTIFICATION TO ADMIN USERS
                    
                  $mailData['email'] = ADMIN_EMAIL_IDS;
                  $mailData['product_name'] = $row['pname'];
                  $mailData['limit_category'] = 'PRODUCT LIMIT: '.$row['name'];
                  $mailData['param_name'] = 'NA';
                  $mailData['old_value'] = $oldArr;
                  $mailData['new_value'] = $newArr;
                  $m->limitUpdates($mailData); 
                
                $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'The Product Limit was successfully edited',
                    )
                );                
//                $this->_redirect('/product/limit?pid='.$detailsArr['product_id']);
                $this->_redirect($this->formatURL('/mvc_productlimit/index?pid='.$detailsArr['product_id']));
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
        
        $prodArray = $productModel->findById($detailsArr['product_id']);
       
        $this->view->name = $prodArray['name'];
        $this->view->product_code = $prodArray['ecs_product_code'];
        $this->view->form = $form;
    }
    
    public function deletelimitAction(){
         $this->title = 'Delete Product Limit';
         $m = new App\Messaging\System\Operation();
         $id = $this->_getParam('id');
         $productModel = new Products();
         $form = new Mvc_DeleteProductLimitForm();
         $productlimitModel = new Mvc_Productlimit();
         $detailsArr = $productlimitModel->findById($id);
         $form->getElement('id')->setValue($id);
         $form->getElement('pid')->setValue($detailsArr['product_id']);
        
         $form->populate($form->getValues());
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
               
                
                
                
                
                try{
                $res = $productlimitModel->delete($id);
                }
                catch(Exception $e){                 
                      
                     App_Logger::log($e->getMessage(), Zend_Log::ERR);
                     $addMsg = $e->getMessage();
                }
               if($res == 'deleted'){
                 
                 $oldArr = array(
                            'Product Limit Name' => $detailsArr['name'],
                            'Currency' => $detailsArr['currency'],
                            'Minimum Amount for First Load' => $detailsArr['limit_out_first_load'],
                            'Minimum Reload Amount per Trxn' => $detailsArr['limit_out_min_txn'],
                            'Maximum Reload Amount per Trxn' => $detailsArr['limit_out_max_txn'],
                            'Max no. of Txns per Day' => $detailsArr['cnt_out_max_txn_daily'],
                            'Max Amount per Day' => $detailsArr['limit_out_max_daily'],
                            'Max no. of Txns per Month' => $detailsArr['cnt_out_max_txn_monthly'],
                            'Max Amount per Month' => $detailsArr['limit_out_max_monthly'],
                            'Max no. of Txns per Year' => $detailsArr['cnt_out_max_txn_yearly'],
                            'Max Amount per Year' => $detailsArr['limit_out_max_yearly'],
                        );
                 
               
                        // SEND NOTIFICATION TO ADMIN USERS
                    
                  $mailData['email'] = ADMIN_EMAIL_IDS;
                  $mailData['product_name'] = $row['pname'];
                  $mailData['limit_category'] = 'PRODUCT LIMIT: '.$row['name'];
                  $mailData['param_name'] = 'NA';
                  $mailData['old_value'] = $oldArr;
                  $mailData['new_value'] = DELETION;
                  $m->limitUpdates($mailData);   
                
                $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'The Product Limit was successfully deleted',
                    )
                );                
//                $this->_redirect('/product/limit?pid='.$detailsArr['product_id']);
                $this->_redirect($this->formatURL('/mvc_productlimit/index?pid='.$detailsArr['product_id']));
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