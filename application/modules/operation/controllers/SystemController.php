 <?php
/**
 * Allow the admins to manage critical info, users, groups, permissions, etc.
 *
 * @category backoffice
 * @package backoffice_controllers
 * @copyright company
 */

class SystemController extends App_Operation_Controller
{
    /**
     * Overrides Zend_Controller_Action::init()
     *
     * @access public
     * @return void
     */
    
    private $session;
    
    public function init(){
        // init the parent
        parent::init();
        
        $this->session = new Zend_Session_Namespace("App.Operation.Controller");
    }
    
    /**
     * Controller's entry point
     *
     * @access public
     * @return void
     */
    public function indexAction(){
        
    }
     
    /* importcrnAction() will take inputs from user like bank name for processing the import crn
     */
    
    public function importcrnAction(){
        
        $this->title = 'Import CRN';
        $user = Zend_Auth::getInstance()->getIdentity();
        $form = new ImportCRNForm(array(
                                        'action' => $this->formatURL('/system/importcrn'),
                                        'method' => 'post',
                                        'name'=>'frmImportcrn',
                                        'id'=>'frmImportcrn'
                                    ));
        $this->view->form = $form;
        $objBanks = new Banks();
        
        
        if ($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())){
               $formData  = $this->_request->getPost();
               $bankId = isset($formData['bank_id'])?$formData['bank_id']:'';
               $bankInfo = $objBanks->getBankInfo($bankId);
               $bankInfo = $bankInfo->toArray();
               $unicode   = $bankInfo['unicode'];
               $crnPaths  = Zend_Registry::get('BANK_UNICODE_IMPORTCRN_PATH');              
               $redirPath = isset($crnPaths[$unicode])?$crnPaths[$unicode]:'';
               
               if($redirPath!=''){
                   $redirPath .= "bank_id/".$bankId;
                   $this->_redirect($this->formatURL($redirPath));
               } else {
                   $bankName = $bankInfo['name'];
                   $this->_helper->FlashMessenger( array('msg-error' => 'Import CRN feature not available for '.$bankName) );
               }
             
            }
        }
    }

        
        
    
}