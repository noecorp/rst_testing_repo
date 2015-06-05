
<?php
/**
 * Allows the Corporate to register
 *
 * @category backoffice
 * @package backoffice_controllers
 * @copyright company
 */

class SettingController extends App_Corporate_Controller
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
        // initiating the session
        $this->session = new Zend_Session_Namespace("App.Corporate.Controller");
        $user = Zend_Auth::getInstance()->getIdentity();
        $corporateModel = new CorporateUser();
        // use the withoutlogin layout
        if(!isset($user->id)) {
            $this->_redirect($this->formatURL('/profile/login'));
            exit;
        }
    }
    
    
    
    public function indexAction(){
         
    }
    
    public function updatemobileAction(){
       
        $user = Zend_Auth::getInstance()->getIdentity();
        $this->title = 'Corporate Signup';
        $form = new CorporateUpdateMobileForm();      
        
        if ($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())){
                $corporateModel = new CorporateUser();
                
                try {
                    $res = $corporateModel->checkMobile($form->getValue('phone'),$form->getValue('old_phone'));
                }catch (Exception $e ) {
                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    $errMsg = $e->getMessage();
                    $this->_helper->FlashMessenger(array('msg-error' => $errMsg,));
                }  
                
                if($res =='invalid_user'){
                 
                    $this->_helper->FlashMessenger(array(
                        'msg-error' => 'Invalid current mobile number.',
                        )
                    );
                }elseif($res =='phone_dup'){
                 
                    $this->_helper->FlashMessenger(array(
                        'msg-error' => 'Mobile number already registered with us',
                        )
                    );
                }else{   
                    $this->_helper->FlashMessenger(
                        array(
                            'msg-success' => 'Please check SMS on your mobile to get verification code',
                        )
                    );
                    $this->session->mobile1=$form->getValue('phone') ;
                    
                    $alerts = new Alerts();
                    $verificationCode = $alerts->generateAuthCode();
                    //echo $verificationCode;
                    $this->session->ver_code = $verificationCode;
                    $this->session->new_mobile = $form->getValue('phone');
                    try{
                        
                        $info = array ('v_code'=>$verificationCode,'mobile1'=>$form->getValue('phone'));
                        
                        $sendConf = $alerts->sendVerificationCode($info, 'operation');
                         
                         
                    } catch (Exception $e ) {    
                                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                                $errMsg = $e->getMessage();
                                $this->_helper->FlashMessenger(
                                    array(
                                        'msg-error' => $errMsg,
                                    )
                                );
                    } 
                    $this->_redirect($this->formatURL('/setting/verification/'));
                }
            }
        }
        $this->view->form = $form;
        $this->view->title = $this->title;
    }
    
    //set verification code
    public function verificationAction()
    {
        $this->title = 'Mobile Verification Code';
        $corporateModel = new CorporateUser();
        $form = new VerificationMobileForm();
        
        if ($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())){
               if( $this->session->ver_code == $form->getValue('code') )
               {
                    try {
                        $res = $corporateModel->updateMobile($this->session->new_mobile);
                    }
                    catch (Exception $e ) {
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                        $errMsg = $e->getMessage();
                        $this->_helper->FlashMessenger(array('msg-error' => $errMsg,));
                    }  
                  
                    $this->_helper->FlashMessenger(
                        array(
                            'msg-success' => 'Mobile number updated sucessfully in our database.',
                        )
                    );
                    unset($this->session->new_mobile);
                    $this->_redirect($this->formatURL('/setting/index'));
                  
               }else{
                    $this->_helper->FlashMessenger(array('msg-error' => 'Incorrect verification code entered',));
               }
            }
        } 
        $this->view->form = $form;
         
    }
    public function updateemailAction(){
       
        $user = Zend_Auth::getInstance()->getIdentity();
        $this->title = 'Corporate Signup';
        $form = new CorporateUpdateEmailForm();      
        
        if ($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())){
                $corporateModel = new CorporateUser();
                
                
                try {
                    $res = $corporateModel->checkEmail($form->getValue('email'),$form->getValue('new_email'));
                }catch (Exception $e ) {
                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    $errMsg = $e->getMessage();
                    $this->_helper->FlashMessenger(array('msg-error' => $errMsg,));
                }  
                
                if($res =='invalid_user'){
                 
                    $this->_helper->FlashMessenger(array(
                        'msg-error' => 'Invalid current email address.',
                        )
                    );
                }elseif($res =='email_dup'){
                 
                    $this->_helper->FlashMessenger(array(
                        'msg-error' => 'Email address already registered with us',
                        )
                    );
                }else{   
                    $this->_helper->FlashMessenger(
                        array(
                            'msg-success' => 'Please check email get verification code',
                        )
                    );
                    $this->session->new_email=$form->getValue('new_email') ;
                    
        
                    $corporateModel->sendCorporateEmailVerificationCode($this->session->new_email);
                    //$this->_redirect($this->formatURL('/setting/verification/'));
                }
            }
        }
        $this->view->form = $form;
        $this->view->title = $this->title;
    }
    
    public function updatebasicinfoAction(){
       
        $user = Zend_Auth::getInstance()->getIdentity();
        $form = new CorporateUpdateBasicInfoForm();
        $state = new CityList();
        $this->title='';
        if ($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())){
                $corporateModel = new CorporateUser();
                
                
                try {
                     $formData  = $this->_request->getPost();
                        $data = array(
                            'first_name' => $formData['first_name'],
                            'last_name' => $formData['last_name'],
                            'date_updated' => new Zend_Db_Expr('NOW()'),
                        );
                        $stateName =  $state->getStateName($formData['res_state']);
                        $coprporate_info = array(
                            'title' => $formData['title'],
                            'first_name' => $formData['first_name'],
                            'middle_name' => $formData['middle_name'],
                            'last_name' => $formData['last_name'],
                            'res_type' => $formData['res_type'],
                            'res_address1' =>$formData['res_address1'],
                            'res_address2' => $formData['res_address2'],
                            'res_city' =>$formData['res_city'],
                            'res_taluka' => $formData['res_taluka'],
                            'res_district' => $formData['res_district'],
                            'res_state' => $stateName,
                            'res_country' => $formData['res_country'],
                            'res_pincode' => $formData['res_pincode'],
                        );
                        $corporateModel->editCorporateUser($data,$user->id);
                        $corporateModel->updateCorporate($coprporate_info,$user->id);
                        $this->_helper->FlashMessenger(array('msg-success' => 'Personal Details updated successfully.',));
                    
                }catch (Exception $e ) {
                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    $errMsg = $e->getMessage();
                    $this->_helper->FlashMessenger(array('msg-error' => $errMsg,));
                }  
               
            }
        }
       
        $corporateModel = new Corporates();
        $row = $corporateModel->findById($user->id);
        //echo "<pre>";print_r($row->toArray()); //exit;
        $stateName =  $state->getStateCode($row['res_state']);
        $row['res_state'] = $stateName;
        $form->city->setValue($row['res_city']);
        $form->pin->setValue($row['res_pincode']);
        //$row['pin'] =$row['res_pincode'];
         
        $form->populate($row->toArray());
        $this->view->form = $form;
        $this->view->title = $this->title;
    }
 
    
}