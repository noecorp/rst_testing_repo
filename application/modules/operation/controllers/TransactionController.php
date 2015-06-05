<?php
/**
 * Allow the admins to manage critical info, users, groups, permissions, etc.
 *
 * @category backoffice
 * @package backoffice_controllers
 * @copyright company
 */

class TransactionController extends App_Operation_Controller
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
        $this->title = 'Transaction Type Listing';
        
        $transactionModel = new Transactiontype();
        $this->view->paginator = $transactionModel->findAll($this->_getPage());
    }
    
    
      /**
     * Allows the user to add another privilege in the application
     *
     * @access public
     * @return void
     */
    public function addAction(){
        $this->title = 'Add New Transaction Type';
        
        $form = new TransactiontypeForm();
        $transactionModel = new Transactiontype();
        //echo "<pre>";print_r($form);exit;
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
                $res = $transactionModel->add($form->getValues());
               if($res == 'added'){
                $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'The Transaction type was successfully added',
                    )
                );                
                $this->_redirect($this->formatURL('/transaction/index/'));
               }
               else if($res == 'typecode_dup'){
                $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'The Typecode exists',
                    )
                );
                
                
               
               }
               else if($res == 'name_dup'){
                $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'The Transaction type name exists',
                    )
                );
                
                
               }
               
                
            }
           
        }
         $form->populate($form->getValues());
        $this->view->form = $form;
    }
    
     public function viewAction() {
       $this->title = 'Transaction Type details'; 
       $transactionModel = new Transactiontype();
       
       $id = $this->_getParam('id');
       $row = $transactionModel->finddetailsById($id);
       
       $this->view->backlink = Zend_Controller_Front::getInstance()->getBaseUrl() . '/transaction/';
       $this->view->item = $row;
      
       
        
        
        
    }
    
}