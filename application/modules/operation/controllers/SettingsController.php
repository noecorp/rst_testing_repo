 <?php
/**
 * Allow the admins to manage all setting links etc.
 *
 * @category backoffice
 * @package backoffice_controllers
 * @copyright company
 */

class SettingsController extends App_Operation_Controller
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
    
    public function addagentcityAction(){
        $this->title = 'Add Agent City Details';
        $user = Zend_Auth::getInstance()->getIdentity();
        $form = new AgentaddcityForm();
        $cityModel = new CityList(); 
        if ($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())){
               $formData  = $this->_request->getPost();
               $insertArr = array(
                   'country_code' => Util::getCountryCode('IN'),
                   'state_code' => strtoupper($formData['state_code']), 
                   'name' => ucfirst($formData['name']), 
                   'code' => $formData['code'], 
                   'pincode' => $formData['pincode'], 
                   'std_code' => $formData['std_code'], 
               );
               try{
                   
                 $isDuplicate = $cityModel->checkCityDuplicacy($insertArr);
                 
                 if(!$isDuplicate){
                
                 $cityModel->insert($insertArr);
                 $this->_helper->FlashMessenger(
                                        array(
                                                'msg-success' => 'City details successfully added',
                                             )
                                    );
                  $this->_redirect($this->formatURL('/settings/index/'));
                 }
                 else{
                    
                    $this->_helper->FlashMessenger(
                                        array(
                                                'msg-error' => 'City details already exist',
                                             )
                                    );  
                 }
               }catch (Zend_File_Transfer_Exception $e) {
                            App_Logger::log($e->getMessage(), Zend_Log::ERR);
                            $this->_helper->FlashMessenger(
                                    array(
                                        'msg-error' => $e->getMessage(),
                                    )
                            );
                        }
            }
        }   
        
        $row = $form->getValues();
        $form->populate($row);
        $this->view->form = $form;
    }
    
    public function addcustomercityAction(){
        $this->title = 'Add Customer City Details';
        $user = Zend_Auth::getInstance()->getIdentity();
        $form = new CustomeraddcityForm();
        $cityModel = new RctMaster();
        if ($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())){
               $formData  = $this->_request->getPost();
               $insertArr = array(
                   'ref_rec_type' => RCT_MASTER_CITY_CODE,
                   'ref_code' => strtoupper($formData['ref_code']), 
                   'ref_desc' => ucfirst($formData['ref_desc']), 
                   'state_id' => '', 
                   'state_code' => $formData['state_code'], 
                   'zone_name' => $formData['zone_name'], 
                   'city_id' => '', 
                   'brcode' => '', 
               );
               try{
                   
                 if($insertArr['state_id'] == ''){
                    $stateName = $cityModel->getStateName($insertArr['state_code']);

                    $stateID = $cityModel->getStateID($stateName);
                    $insertArr['state_id'] = $stateID;
                 }
                   
                 $isDuplicate = $cityModel->checkCityDuplicacy($insertArr);
                 
                 if(!$isDuplicate){
                 
                 
                 $cityModel->insert($insertArr);
                 $this->_helper->FlashMessenger(
                                        array(
                                                'msg-success' => 'Customer city details successfully added',
                                             )
                                    );
                  $this->_redirect($this->formatURL('/settings/index/'));
                 }
                 else{
                    
                    $this->_helper->FlashMessenger(
                                        array(
                                                'msg-error' => 'Customer city details already exist',
                                             )
                                    );  
                 }
               }catch (Zend_File_Transfer_Exception $e) {
                            App_Logger::log($e->getMessage(), Zend_Log::ERR);
                            $this->_helper->FlashMessenger(
                                    array(
                                        'msg-error' => $e->getMessage(),
                                    )
                            );
                        }
            }
        }   
        
        $row = $form->getValues();
        $form->populate($row);
        $this->view->form = $form;
    }
    
    public function manageifscAction(){
	$this->title = 'Search IFSC Code';
	$this->view->heading = 'Search IFSC Code';
	$this->view->addifscLink = array($this->formatURL('/settings/addifsc/'),'Add new IFSC code'); 
	$form = new IfscSearchForm(array(
		'action' => $this->formatURL('/settings/manageifsc'),
		'method' => 'POST',
	));
	$sub = $this->_getParam('submit');
	$qurStr['bank_name'] = $this->_getParam('bank_name');
	$qurStr['ifsc_code'] = $this->_getParam('ifsc_code');
	$form->populate($qurStr); 
	$page = $this->_getParam('page');
	if(!empty($sub)){  
	    if($form->isValid($qurStr)){
		$qurData['bank_name'] =  $qurStr['bank_name'];
		$qurData['ifsc_code'] = $qurStr['ifsc_code'];
		$page = $this->_getParam('page');
		$this->view->title = 'Detail of IFSC Code : <em>'.$qurStr['ifsc_code'].'</em>'; 
		$objIfsc = new BanksIFSC();
		$ifscLists = $objIfsc->getListIfsc($qurData);
		$paginator = $objIfsc->paginateByArray($ifscLists, $page, $paginate = NULL);
		$this->view->paginator=$paginator;
		$this->view->sub = $sub; 
	    }
	} 
	$this->view->form = $form; 
	$this->view->formData = $qurStr;
    }
    
    public function addifscAction(){
	$this->title = 'Add new IFSC code';
	$this->view->heading = 'Add new IFSC code';
	$form = new IfscAddForm(array(
		'action' => $this->formatURL('/settings/addifsc'),
		'method' => 'POST', 
	)); 
        $form->setCancelLink($this->formatURL('/settings/manageifsc'));
	$sub = $this->_getParam('submit');
	$qurStr['bank_name'] = $this->_getParam('bank_name');
	$qurStr['ifsc_code'] = $this->_getParam('ifsc_code');
	$qurStr['micr_code'] = $this->_getParam('micr_code');
	$qurStr['branch_name'] = $this->_getParam('branch_name');
	$qurStr['address'] = $this->_getParam('address');
	$qurStr['contact'] = $this->_getParam('contact');
	$qurStr['city'] = $this->_getParam('city');
	$qurStr['district'] = $this->_getParam('district');
	$qurStr['state'] = $this->_getParam('state');
	$qurStr['enable_for'] = $this->_getParam('enable_for');
	$page = $this->_getParam('page');
	if(!empty($sub)){  
	    $validate =  $form->isValid($qurStr);
	    $form->populate($qurStr);
	    if ($validate) { 
		try {
		    $qurData['bank_name'] =  $qurStr['bank_name'];
		    $qurData['ifsc_code'] = $qurStr['ifsc_code'];
		    $qurData['micr_code'] = $qurStr['micr_code'];
		    $qurData['branch_name'] = $qurStr['branch_name'];
		    $qurData['address'] = $qurStr['address'];
		    $qurData['contact'] = $qurStr['contact'];
		    $qurData['city'] = $qurStr['city'];
		    $qurData['district'] = $qurStr['district'];
		    $qurData['state'] = $qurStr['state'];
		    $qurData['enable_for'] = $qurStr['enable_for'];  
                    $objIfsc = new BanksIFSC();
		    $ifscInsrt = $objIfsc->addnewIfsc($qurData);
		    if(!empty($ifscInsrt)){
			$msg = 'IFSC Code "'.$ifscInsrt['ifsc_code']. '" added successfully';
			$this->_helper->FlashMessenger(array('msg-success' => $msg));
			$this->_redirect($this->formatURL('/settings/manageifsc?bank_name='.$ifscInsrt['bank_name'].'&ifsc_code='.$ifscInsrt['ifsc_code'].'&submit=submit'));
		    }
                } catch (Exception $e) {
                    App_Logger::log($e->getMessage(), Zend_Log::ERR); 
                    $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage()));
                }
	    } else {
		$this->_helper->FlashMessenger(array('msg-error' => 'Please remove errors'));
	    }
	}
	$this->view->form = $form;
	$this->view->formData = $qurStr;
    }
    
    
    
    public function updateifscAction(){
	$this->title = 'Update IFSC code';  
	/*
	 * Check valid Update IFSC code
	 */
	$checkData['bank_name'] = $this->_getParam('bank_name');
	$checkData['ifsc_code'] = $this->_getParam('ifsc_code');
	$objIfsc = new BanksIFSC();
	$ifscData = $objIfsc->getListIfsc($checkData);
	if(count($ifscData) == 0){
	    $this->_helper->FlashMessenger(array('msg-error' => 'No IFSC code found to update.'));
	    $this->_redirect($this->formatURL('/settings/manageifsc')); exit();
	}
	$this->view->heading = 'Upadte IFSC code : <em>'.$ifscData[0]['ifsc_code'].'</em> &nbsp; <small> ('.$ifscData[0]['bank_name'].')</small>';
	$form = new IfscUpdateForm(array(
		'action' => '',
		'method' => 'POST',
	));
        $form->setCancelLink($this->formatURL('/settings/manageifsc?bank_name='.$ifscData[0]['bank_name'].'&ifsc_code='.$ifscData[0]['ifsc_code'].'&submit=submit'));	
	$form->getElement('micr_code')->setValue($ifscData[0]['micr_code']);
	$form->getElement('branch_name')->setValue($ifscData[0]['branch_name']);
	$form->getElement('address')->setValue($ifscData[0]['address']);
	$form->getElement('contact')->setValue($ifscData[0]['contact']);
	$form->getElement('city')->setValue($ifscData[0]['city']);
	$form->getElement('district')->setValue($ifscData[0]['district']);
	$form->getElement('state')->setValue($ifscData[0]['state']);
	$form->getElement('enable_for')->setValue($ifscData[0]['enable_for']); 
	/*
	 * Update Script Start here
	 */
	$sub = $this->_getParam('submit'); 
	$qurStr['micr_code'] = $this->_getParam('micr_code');
	$qurStr['branch_name'] = $this->_getParam('branch_name');
	$qurStr['address'] = $this->_getParam('address');
	$qurStr['contact'] = $this->_getParam('contact');
	$qurStr['city'] = $this->_getParam('city');
	$qurStr['district'] = $this->_getParam('district');
	$qurStr['state'] = $this->_getParam('state');
	$qurStr['enable_for'] = $this->_getParam('enable_for');
	$page = $this->_getParam('page'); 
	if(!empty($sub)){
	    $validate =  $form->isValid($qurStr);
	    $form->populate($qurStr);
	    if ($validate) { 
		try {
		    $whereCon['bank_name'] =  $ifscData[0]['bank_name'];
		    $whereCon['ifsc_code'] = $ifscData[0]['ifsc_code']; 
		    $updateData['micr_code'] = $qurStr['micr_code'];
		    $updateData['branch_name'] = $qurStr['branch_name'];
		    $updateData['address'] = $qurStr['address'];
		    $updateData['contact'] = $qurStr['contact'];
		    $updateData['city'] = $qurStr['city'];
		    $updateData['district'] = $qurStr['district'];
		    $updateData['state'] = $qurStr['state'];
		    $updateData['enable_for'] = $qurStr['enable_for']; 
		    if($objIfsc->update($updateData, $whereCon)){
			$msg = 'IFSC Code "'.$ifscData[0]['ifsc_code']. '" updated successfully';
			$this->_helper->FlashMessenger(array('msg-success' => $msg));
			$this->_redirect($this->formatURL('/settings/manageifsc?bank_name='.$ifscData[0]['bank_name'].'&ifsc_code='.$ifscData[0]['ifsc_code'].'&submit=submit'));
		    } else{
			$this->_helper->FlashMessenger(array('msg-error' => 'Update not done.'));
		    }
                } catch (Exception $e) {
                    App_Logger::log($e->getMessage(), Zend_Log::ERR); 
                    $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage()));
                }
	    } else {
		$this->_helper->FlashMessenger(array('msg-error' => 'Please remove errors'));
	    }
	}
	$this->view->form = $form;
	$this->view->formData = $qurStr;
    }
    
    
}