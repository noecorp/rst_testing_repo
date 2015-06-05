<?php
/**
 * Allows the users to perform CRUD operations on other users
 *
 * @category backoffice
 * @package backoffice_controllers
 * @copyright company
 */

class UsersController extends App_Operation_Controller
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
     * Allows users to see all other users that are registered in
     * the application
     *
     * @access public
     * @return void
     */
    public function indexAction(){
        $this->title = 'Users Listing';
        
        $groupId = ($this->_getParam('gid') > 0) ? $this->_getParam('gid') : 0;
        $userModel = new OperationUser();
        $this->view->paginator = $userModel->findAll($this->_getPage(), NULL, FALSE, $groupId);

    }
    
        
    /**
     * Allows users to see other users' profiles
     *
     * @access public
     * @return void
     */
    public function viewAction(){
        $this->title = 'User Details';
        
        $userModel = new OperationUser();
        $id = $this->_getParam('id');
        
        if(!is_numeric($id)){
            $this->_helper->FlashMessenger(
                array(
                    'error' => 'The user id you provided is invalid',
                )
            );
            
            $this->_redirect($this->formatURL('/users/index/'));
        }
        
        $row = $userModel->findById($id);
        
        if(empty($row)){
            $this->_helper->FlashMessenger(
                array(
                    'error' => 'The user was successfully added',
                )
            );
            
            $this->_redirect($this->formatURL('/users/index/'));
        }
        
        $this->view->backlink = Zend_Controller_Front::getInstance()->getBaseUrl() . '/users/';
        $this->view->item = $row;
    }
    
    /**
     * Allows users to logically delete other users
     * (should be reserved for administrators)
     *
     * @access public
     * @return void
     */
    public function deleteAction(){
        $this->title = 'Delete User';
        
        $form = new DeleteForm();
        $userModel = new OperationUser();
        
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
                $userModel->deleteById($form->getValue('id'));
                $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'The user was successfully deleted',
                    )
                );
                
                App_FlagFlippers_Manager::save();
                
                $this->_redirect($this->formatURL('/users/index/'));
            }
        }else{
            $id = $this->_getParam('id');
            
            if(!is_numeric($id)){
                $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'The id you provided is invalid',
                    )
                );
                
                $this->_redirect($this->formatURL('/users/index/'));
            }
            
            if($id == 1){
                $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'It is forbidden to mess with the admin account in this release.',
                    )
                );
                
                $this->_redirect($this->formatURL('/users/index/'));
            }
            
            $row = $userModel->findById($id);
            
            if(empty($row)){
                $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'The requested item cannot be found',
                    )
                );
                
                $this->_redirect($this->formatURL('/users/index/'));
            }
            
            $this->view->item = $row;
            $form->populate($row->toArray());
        }
        
        $this->view->form = $form;
    }
}