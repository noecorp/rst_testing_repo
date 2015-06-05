<?php
/**
 * Allows user to manage the user groups
 *
 * @category backoffice
 * @package backoffice_controllers
 * @copyright company
 */

class RolesController extends App_Operation_Controller
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
     * Allows the user to view all the user groups registered
     * in the application
     *
     * @access public
     * @return void
     */
    public function indexAction(){
        $this->title = 'Roles Listing';
        
        $rolesModel = new Roles();
        $this->view->paginator = $rolesModel->findAll($this->_getPage());
//        echo "<pre>";
//        foreach($row as $x){
//        print($x['name']."==");}exit;
    }
    
    /**
     * Allows the user to add another user group in the
     * application
     *
     * @access public
     * @return void
     */
    public function addAction(){
        $this->title = 'Add New User Group';
        
        $form = new GroupForm();
        $groupModel = new Group();
        
        if ($this->getRequest()->isPost()) { 
            if($form->isValid($this->getRequest()->getPost())) {
                $row = $form->getValues();
                $data['name'] = $row['name'];
                $data['parent_id'] = 0;
                $already = $groupModel->existsGroupName($data['name']);
                if(isset($already) && $already > 0){
                   $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => sprintf('The group name %s already exists', $form->getValue('name')),
                    )
                ); 
                }
                else {
                    $groupId = $groupModel->insert($data);
                    $this->_helper->FlashMessenger(
                        array(
                            'msg-success' => sprintf('The group %s was successfully added', $form->getValue('name')),
                        )
                    );

                    App_FlagFlippers_Manager::save();

                    $this->_redirect($this->formatURL('/groups/flippers?id='.$groupId));
                    
                }
            }
        }
        
        
        $this->view->form = $form;
    }
    
    /**
     * Edits an existing user group
     *
     * @access public
     * @return void
     */
    public function editAction(){
        $this->title = 'Edit User Group';
        
        $form = new GroupForm();
        $groupModel = new Group();
        
        if ($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
                $row = $form->getValues();
                $data['name'] = $row['name'];
                $data['parent_id'] = 0;
                $already = $groupModel->existsGroupName($data['name']);
                if(isset($already) && $already != $row['id']){
                   $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => sprintf('The group name %s already exists', $form->getValue('name')),
                    )
                ); 
                }
                else {
                    $groupModel->update($data, "id = ".$row['id']);
                    $this->_helper->FlashMessenger(
                        array(
                            'msg-success' => 'The group was successfully edited',
                        )
                    );

                    App_FlagFlippers_Manager::save();

                    $this->_redirect($this->formatURL('/groups/flippers?id='.$row['id']));
                }
            }
        }//else{
            $id = $this->_getParam('id');
            if (!is_numeric($id)) {
                $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'The provided group id is invalid.',
                    )
                );
                
                $this->_redirect($this->formatURL('/groups/index/'));
            }
//            echo 'dddddd';exit;
            $row = $groupModel->findById($id);
//            echo "<pre>";print_r($row);exit;
            if (empty($row)) {
                $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'The requested group could not be found',
                    )
                );
                
                $this->_redirect($this->formatURL('/groups/index/'));
            }
            
            $form->populate($row->toArray());
            $this->view->item = $row;
        //}
        
        $this->view->form = $form;
    }
    
    /**
     * Allows the user to delete an existing user group. All the users attached to
     * this group *WILL NOT* be deleted, they will just lose all 
     * privileges granted by this group
     *
     * @access public
     * @return void
     */
    public function deleteAction(){
        $this->title = 'Delete User Group';
        
        $form = new DeleteForm();
        $groupModel = new Group();
        
        if ($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
                $groupModel->deleteGroupFlippers($form->getValue('id'));
                $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'The group was successfully deleted',
                    )
                );
                
                App_FlagFlippers_Manager::save();
                
                $this->_redirect($this->formatURL('/groups/index/'));
            }
        }else{
            $id = $this->_getParam('id');
            $row = $groupModel->findById($id);
            
            if (empty($row)) {
                $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => sprintf('We cannot find group with id %s', $id),
                    )
                );
                $this->_redirect($this->formatURL('/groups/index/'));
            }
            
            $form->populate($row->toArray());
            $this->view->item = $row;
        }
        
        $this->view->form = $form;
    }
    
    /**
     * Allows the user to manage individual permissions for each
     * user group
     *
     * @access public
     * @return void
     */
    public function flippersAction(){
        $this->title = 'Manage Permissions For Group';
        
        $form = new GroupPermissionsForm();
        $fliperModel = new Flipper();
        $groupModel = new Group();
        
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())) {
                $fliperModel->savePermissions($form->getValues());
                $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => sprintf('Permissions for group %s were successfully updated', $group['name']),
                    )
                );
                
                App_FlagFlippers_Manager::save();
                
                $this->_redirect($this->formatURL('/groups/index/'));
            }
        }else{
            $id = $this->_getParam('id');
            
            if (!is_numeric($id)) {
                $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => sprintf('We cannot find group with id %s', $id),
                    )
                );
                $this->_redirect($this->formatURL('/groups/index/'));
            }
            
            $group = $groupModel->findById($id);
            $flipper = $fliperModel->findByGroupId($id);
            
            if (empty($group)) {
                $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => sprintf('The permissions for the group %s were updated', $form->getValue('name')),
                    )
                );
                
                $this->_redirect($this->formatURL('/groups/index/'));
            }
            
            $form->populate($flipper->toArray(), $id);
            $this->view->item = $group;
        }
        
        $this->view->form = $form;
        
    }
}