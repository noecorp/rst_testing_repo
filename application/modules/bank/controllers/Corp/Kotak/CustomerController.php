<?php

/**
 * HIC Default entry point
 *
 * @author Mini
 */
class Corp_Kotak_CustomerController extends App_Bank_Controller {

    //put your code here


    public function init() {
        parent::init();
    }

    public function indexAction() {
        
    }

       public function searchAction() {
        $this->title = 'Pending Customers List';
        $data['sub'] = $this->_getParam('sub');
        $form = new Corp_Kotak_CardholderSearchForm(array('action' => $this->formatURL('/corp_kotak_customer/search'),
            'method' => 'POST',
        ));
        $cardholdersModel = new Corp_Kotak_Customers();
        $page = $this->_getParam('page');
        $params = array('product_id' => $this->_getParam('product_id'),'state' => $this->_getParam('state'),'pincode' => $this->_getParam('pincode'),
            'date_approval' => $this->_getParam('date_approval'));
        
        if ($data['sub'] != '') {
        $params['pin'] =$params['pincode'];
        if(!empty($params['product_id'])){
        $this->view->paginator = $cardholdersModel->showBankPendingCustomerDetails($page,$params);
        }
        else{
             $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'Please select Product',
                    )
                );
        }
        $form->populate($params);
        }
        $form->populate($params);
        $this->view->form = $form;
        $this->view->sub = $data['sub'];
       }
     
     public function approveAction(){
        $this->title = 'Approve Customer';
        

        $customerLogModel = new Corp_Kotak_CustomersLog();
        $customerModel = new Corp_Kotak_Customers();
        $id = $this->_getParam('id');
        $custInfo = $customerModel->findById($id);
        $form = new Corp_Kotak_ApproveForm();        
        $ip = $customerModel->formatIpAddress(Util::getIP());
        $user = Zend_Auth::getInstance()->getIdentity();
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
                $formData  = $this->_request->getPost();
                
               
                
                try{
                    $data = array('product_customer_id' => $id,'by_type' => BY_AUTHORIZER,'by_id' => $user->id, 
                    'status_bank_old' => STATUS_PENDING,'status_bank_new' => STATUS_APPROVED,'ip' => $ip,'comments' => $formData['remarks']
                    );
               
                    $params = array('status' => STATUS_APPROVED,'date_authorize' => date('Y-m-d h:m:s'),'id' => $id);
                    $res = $customerModel->changeBankStatus($params);
                    $customerLogModel->save($data);
                    
                } catch(Exception $e){
                     App_Logger::log($e->getMessage(), Zend_Log::ERR);
                     $Msg = $e->getMessage();
                }
             
                if($res){
                    $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'Customer successfully Approved.',
                    )
                );
                        $this->_redirect($this->formatURL('/corp_kotak_customer/search'));                                                
                    

                }
                else
                {
                    $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => $Msg,
                    )
                );
                }
                
                
            }
        }else{
            $row = $customerModel->findById($id);
            
            if (empty($row)) {
                $this->_helper->FlashMessenger(
                    array(
                        'msg-warning' => sprintf('We cannot find Customer with id %s', $id),
                    )
                );
                $this->_redirect($this->formatURL('/corp_kotak_customer/search/'));
            }
            
            $form->populate(Util::toArray($row));
            $this->view->item = $row;
        }
        $row = $customerModel->findById($id);
         
        $this->view->item = $row;
        $this->view->form = $form;
    }
    
    public function rejectAction(){
        $this->title = 'Reject Customer';
        

        $customerLogModel = new Corp_Kotak_CustomersLog();
        $customerModel = new Corp_Kotak_Customers();
        $id = $this->_getParam('id');
        $custInfo = $customerModel->findById($id);
        $form = new Corp_Kotak_RejectForm();        
        $ip = $customerModel->formatIpAddress(Util::getIP());
        $user = Zend_Auth::getInstance()->getIdentity();
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
                $formData  = $this->_request->getPost();
                
               
                
                try{
                    $data = array('product_customer_id' => $id,'by_type' => BY_AUTHORIZER,'by_id' => $user->id, 
                    'status_bank_old' => STATUS_PENDING,'status_bank_new' => STATUS_REJECTED,'ip' => $ip,'comments' => $formData['remarks']
                    );
               
                    $params = array('status' => STATUS_REJECTED,'id' => $id,'date_authorize' => date('Y-m-d h:m:s'));
                    $res = $customerModel->changeBankStatus($params);
                    $customerLogModel->save($data);
                    
                } catch(Exception $e){
                     App_Logger::log($e->getMessage(), Zend_Log::ERR);
                     $Msg = $e->getMessage();
                }
             
                if($res){
                    $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'Customer successfully Rejected.',
                    )
                );
                        $this->_redirect($this->formatURL('/corp_kotak_customer/search'));                                                
                    

                }
                else
                {
                    $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => $Msg,
                    )
                );
                }
                
                
            }
        }else{
            $row = $customerModel->findById($id);
            
            if (empty($row)) {
                $this->_helper->FlashMessenger(
                    array(
                        'msg-warning' => sprintf('We cannot find Customer with id %s', $id),
                    )
                );
                $this->_redirect($this->formatURL('/corp_kotak_customer/search/'));
            }
            
            $form->populate(Util::toArray($row));
            $this->view->item = $row;
        }
        $row = $customerModel->findById($id);
         
        $this->view->item = $row;
        $this->view->form = $form;
    }
      public function viewAction() {

        $this->title = 'Customer Details';
        $cardholdersModel = new Corp_Kotak_Customers();
        $documentDetails = array();
        $id = $this->_getParam('id');
        if (!is_numeric($id)) {
            $this->_helper->FlashMessenger(
                    array(
                        'error' => 'The user id you provided is invalid',
                    )
            );

            $this->_redirect($this->formatURL('/corp_kotak_customer/index/'));
        }

        $row = $cardholdersModel->findById($id);
        $documentsId = $cardholdersModel->customerIdDoclist($row->id_proof_doc_id);
        $documentsAdd = $cardholdersModel->customerAddDoclist( $row->address_proof_doc_id);
        $documentsprofile = $cardholdersModel->customerProfileDoclist( $row->photo_doc_id);
        $documentDetails = array('id_proof_doc' => (!empty($documentsId))?$documentsId['file_name']:0,'address_proof_doc' => (!empty($documentsAdd))?$documentsAdd['file_name']:0
            ,'photo_doc' => (!empty($documentsprofile))?$documentsprofile['file_name']:0);
        $this->view->documents = $documentDetails;
        $row->gender = ucfirst($row->gender);
        $row->date_of_birth = Util::returnDateFormatted($row->date_of_birth, "Y-m-d", "d-m-Y", "-");
        $row->date_failed = Util::returnDateFormatted($row->date_failed, "Y-m-d", "d-m-Y", "-");
        //echo '<pre>';print_r($row);exit;
        if (empty($row)) {
            $this->_helper->FlashMessenger(
                    array(
                        'error' => 'User with Id does not exist',
                    )
            );

            $this->_redirect($this->formatURL('/corp_kotak_customer/index/'));
        }
        

        
        //Select Cardholder's Purse
        $this->view->cardholderPurses = array();
        if (isset($row->kotak_customer_id) && $row->kotak_customer_id > 0) {
            $cardHolder = new Corp_Kotak_Customers();
            $this->view->cardholderPurses = $cardHolder->getCardholderPurses($row->kotak_customer_id);
        }

       
         // Get status and comments
        $this->view->cardholderStatus = array();
            $cardHolderObj = new Corp_Kotak_CustomersLog();
            $this->view->cardholderStatus = $cardHolderObj->getCardholderStatus($row->id,$byType = BY_BANK);
            
        
        
        $this->view->backlink = Zend_Controller_Front::getInstance()->getBaseUrl() . '/corp_kotak_customer/search';
        $this->view->item = $row;
    }
    
      public function acceptdocumentAction() {
        $this->title = 'Accept Physical Document';
        $formData = $this->_request->getPost();
        $submit = isset($formData['submit_success']) ? $formData['submit_success'] : '';
        $data['sub'] = $this->_getParam('sub');
        $form = new Corp_Kotak_CardholderAcceptDocForm(array('action' => $this->formatURL('/corp_kotak_customer/acceptdocument'),
            'method' => 'POST',
        ));
        $cardholdersModel = new Corp_Kotak_Customers();
        $page = $this->_getParam('page');
        $params = array('product_id' =>  $this->_getParam('product_id'),'state' => $this->_getParam('state'),'pincode' => $this->_getParam('pincode'),
            'date_authorize' => $this->_getParam('date_authorize'));
        
        if ($data['sub'] != '') {
        $params['pin'] =$params['pincode'];
        if(!empty($params['product_id'])){
        $this->view->paginator = $cardholdersModel->acceptPhysicalDocList($page,$params);
        }
        else{
             $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'Please select Product',
                    )
                );
        }
        $form->populate($params);
        }
        if ($submit != '') {


            try {
                
                $cardholdersModel->updatePhysicalDoc($formData['reqid'], $formData['date_recd_doc']);
                $this->_helper->FlashMessenger(
                        array(
                            'msg-success' => 'Customer Physical document details have been updated in our records',
                        )
                );
            } catch (Exception $e) {
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                $this->_helper->FlashMessenger(
                        array(
                            'msg-error' => $e->getMessage(),
                        )
                );
            }
        }
        $form->populate($params);
        $this->view->form = $form;
        $this->view->sub = $data['sub'];
       }
}
