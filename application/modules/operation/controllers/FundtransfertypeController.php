<?php
/**
 * Allows the users to perform CRUD operations on privileges
 *
 * @category backoffice
 * @package backoffice_controllers
 * @copyright company
 */

class FundtransfertypeController extends App_Operation_Controller
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
    
    /**
     * Allows the user to view all the permissions registered
     * in the application
     *
     * @access public
     * @return void
     */
    public function indexAction(){
        $this->title = 'Fund Transfer Type Listing';
        
        $objFTT = new FundTransferType();
        $this->view->paginator = $objFTT->getFundTransferTypes($this->_getPage());        
    }
    
    /**
     * Allows the user to add another privilege in the application
     *
     * @access public
     * @return void
     */
    public function addAction(){
        $this->title = 'Add Fund Transfer Type';
        
        $form = new FundTransferTypeForm();
        $objFTT = new FundTransferType();
        $oprInfo = Zend_Auth::getInstance()->getIdentity();
        
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
                $formData = $form->getValues();
                $formData['status'] = STATUS_ACTIVE;
                $formData['by_ops_id'] = $oprInfo->id;
                $resp = $objFTT->save($formData);
                if($resp>0){
                    $this->_helper->FlashMessenger(
                        array(
                            'msg-success' => 'The fund transfer type was successfully added',
                        )
                    );
               }
                
//                $this->_redirect('/fundtransfertype/index');
                $this->_redirect($this->formatURL('/fundtransfertype/index/'));
            }
        }
        
        $this->view->form = $form;
    }
    
    /**
     * Edits an existing privilege
     *
     * @access public
     * @return void
     */
    public function editAction(){
        $this->title = 'Edit Fund Transfer Type';
        
        $form = new FundTransferTypeEditForm();
        $objFTT = new FundTransferType();
        $oprInfo = Zend_Auth::getInstance()->getIdentity();
        $fttId = $this->_getParam('id');
        
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
                $formData = $form->getValues();
                $formData['by_ops_id'] = $oprInfo->id;
                $formData['id'] = $fttId;
                
                $objFTT->save($formData);
                $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'The fund transfer type was successfully edited',
                    )
                );
                
                $this->_redirect($this->formatURL('/fundtransfertype/index/'));
            }
        }else{            
            
            if (!is_numeric($fttId)) {
                $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'The provided fund transfer type is invalid.',
                    )
                );
                
                $this->_redirect($this->formatURL('/fundtransfertype/index/'));
            }
            
            $row = $objFTT->findById($fttId);
            
            if (empty($row)) {
                $this->_helper->FlashMessenger(
                    array(
                        'msg-warning' => 'The requested fund transfer type could not be found',
                    )
                );
                
                $this->_redirect($this->formatURL('/fundtransfertype/index/'));
            }
            
            $form->populate($row->toArray());
            $this->view->item = $row;
        }
        
        $this->view->form = $form;
    }
    
    
}