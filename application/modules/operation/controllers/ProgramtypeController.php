<?php
/**
 * Allow the admins to manage Program Type for products.
 *
 * @category Operation Remit
 * @copyright Transerv
 */

class ProgramtypeController extends App_Operation_Controller
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
        $this->title = 'Program Type Listing';
        
        $objProgramtype = new ProgramType();
        $this->view->paginator = $objProgramtype ->getSettings(SETTINGS_SECTION_ID_PROGRAM_TYPE, $this->_getPage());
    }
    
    
      /**
     * Allows the user to add another privilege in the application
     *
     * @access public
     * @return void
     */
    public function addAction(){
        $this->title = 'Add New Program Type';
        
        $form = new ProgramTypeForm();
        $objProgramtype = new ProgramType();
        $oprInfo = Zend_Auth::getInstance()->getIdentity();
        
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
                
                $formData = $form->getValues();
                $formData['by_ops_id'] = $oprInfo->id;
                $formData['ip'] =  $objProgramtype->formatIpAddress(Util::getIP());
                $formData['settings_section_id'] = SETTINGS_SECTION_ID_PROGRAM_TYPE;
                $formData['type'] = $form->getValue('value');
                $formData['value'] = $form->getValue('value');
                //echo "<pre>";print_r($formData);exit;
                $res = $objProgramtype->add($formData);
                
               if($res == 'added'){
                $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'The program type was successfully added',
                    )
                );                
                $this->_redirect($this->formatURL('/programtype/index/'));
               }
               else if($res == 'value_dup'){
                $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'The program type value exists',
                    )
                );
                
                
               
               }
               else if($res == 'name_dup'){
                $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'The program type name exists',
                    )
                );
                
                
               }
               
                
            }
           
        }
         $form->populate($form->getValues());
        $this->view->form = $form;
    }
    
     public function viewAction() {
       $this->title = 'Program Type details'; 
       $transactionModel = new Transactiontype();
       
       $id = $this->_getParam('id');
       $row = $transactionModel->finddetailsById($id);
       
       $this->view->backlink = Zend_Controller_Front::getInstance()->getBaseUrl() . '/programtype/';
       $this->view->item = $row;
      
       
        
        
        
    }
    
}