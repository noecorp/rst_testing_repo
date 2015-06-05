<?php
/**
 * Allows the users to perform CRUD operations on privileges
 *
 * @category backoffice
 * @package backoffice_controllers
 * @copyright company
 */

class AgentsettingController extends App_Operation_Controller
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
     * getting agent setting
     * in the application
     *
     * @access public
     * @return void
     */
    public function indexAction(){
        $this->title = 'Agent Settings';
        $objAS = new AgentSetting();
        $this->view->paginator = $objAS->getSettings(AGENT_SECTION_SETTING_ID, $this->_getPage());        
        $sectionInfo =  $objAS->getSettingInfo(AGENT_SECTION_SETTING_ID);
        $this->view->setting_section_name = $sectionInfo->setting_section_name;
    }
    
        
    /**
     * Edits an existing privilege
     *
     * @access public
     * @return void
     */
    public function editAction(){
        $this->title = 'Edit Setting';
        
        $form = new AgentSettingEditForm();
        $objAS = new AgentSetting();
        $oprInfo = Zend_Auth::getInstance()->getIdentity();
        $settingId = $this->_getParam('id');
        $row = $objAS->getSettingInfo($settingId);
        
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
                $formData = $form->getValues();
                $formData['by_ops_id'] = $oprInfo->id;
                $formData['id'] = $settingId;
                $formData['ip'] =  Util::getIP();
                $formData['settings_section_id'] = $row->settings_section_id;
                $formData['type'] = $row->type;
                $formData['currency'] = $row->currency;
                
                try{
                    $objAS->updateSetting($formData);
                }catch(Exception $e){
                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage(),));
                }
                
                $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'Agent setting was successfully edited.',
                    )
                );
                
//                $this->_redirect('/agentsetting/index');
                $this->_redirect($this->formatURL('/agentsetting/index/'));
            }
        }else{            
            
            if (!is_numeric($settingId)) {
                $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'The provided agent setting id is invalid.',
                    )
                );
                
//                $this->_redirect('/agentsetting/index');
                $this->_redirect($this->formatURL('/agentsetting/index/'));
            }
            
            
            if (empty($row)) {
                $this->_helper->FlashMessenger(
                    array(
                        'msg-warning' => 'The requested agent setting could not be found.',
                    )
                );
                
//                $this->_redirect('/agentsetting/index');
                $this->_redirect($this->formatURL('/agentsetting/index/'));
            }
            
            $form->populate($row->toArray());
            $this->view->item = $row;
        }
        
        $this->view->form = $form;
    }
    
    
}