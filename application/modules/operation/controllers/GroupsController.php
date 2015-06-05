<?php
/**
 * Allows user to manage the user groups
 *
 * @category backoffice
 * @package backoffice_controllers
 * @copyright company
 */

class GroupsController extends App_Operation_Controller
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
        $this->title = 'Groups Listing';
        
        $groupModel = new Group();
        $this->view->paginator = $groupModel->findAll($this->_getPage());
//        echo "<pre>";
//        foreach($row as $x){
//        print($x['name']."==");}exit;
    }
    
   
}