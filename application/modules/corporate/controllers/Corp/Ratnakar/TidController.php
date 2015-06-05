<?php
/**
 * @category backoffice
 * @package backoffice_controllers
 * @copyright company
 */

class Corp_Ratnakar_TidController extends App_Corporate_Controller
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
         if(!isset($user->id)) {
            $this->_redirect($this->formatURL('/profile/login'));
            exit;
         }
     }
     
     public function uploadtidAction() {
        $this->title = "Upload TID";
        $page = $this->_getParam('page');
        $form = new Corp_Ratnakar_TiduploadForm();
        
        $formData = $this->_request->getPost();
        $submit = isset($formData['submit_success']) ? $formData['submit_success'] : '';
        $this->view->incorrectData = FALSE;
        $tidModel = new TidMaster();
        $this->view->records = FALSE;
        $user = Zend_Auth::getInstance()->getIdentity();
        $productModel = new Products();
        

        if ($this->getRequest()->isPost() && $submit=='') {
            if ($form->isValid($this->getRequest()->getPost())) {
               $upload = new Zend_File_Transfer_Adapter_Http();
               $upload->receive();
               $batchName = $upload->getFileName('doc_path');
               
               $filename = $upload->getFileName('doc_path', $path = FALSE);
               
               //Add Validators for uploaded file's extesion , mime type and size
               $upload->addValidator('Extension', false, array('txt'));
               
               //check file extension...
                $upload->addValidator('Extension', false, array(FILE_TYPE_TXT, 'case' => false));
                
                $file_ext = Util::getFileExtension($batchName);
                if(strtolower($file_ext) != strtolower(FILE_TYPE_TXT))
                {
                   $this->_helper->FlashMessenger( array('msg-error' => 'Invalid file uploaded. Allowed Format is txt only.',) );
                   $this->_redirect($this->formatURL('/corp_ratnakar_tid/uploadtid/'));
                }
               
                $failed_count = 0;
                $success_count = 0;
                
                $fp = fopen($batchName, 'r');
                while (!feof($fp)) {
                    $line = fgets($fp);
                    if (!empty($line)) {

                        $delimiter = CORP_CARDHOLDER_UPLOAD_DELIMITER;
                        $dataArr = str_getcsv($line, $delimiter);
                        $arrLength = Util::getArrayLength($dataArr);
                        if (!empty($dataArr)) {
                            //if ($arrLength != CORP_CARDHOLDER_UPLOAD_COLUMNS)
                              //  $this->view->incorrectData = TRUE;
                            try {
                                // direct insert into rat_corp_cardholders

                                $return = $tidModel->insertTidMaster($dataArr, $filename);
                                if($return == FALSE)
                                {
                                    $failed_count += 1;
                                } else {
                                    $success_count += 1;
                                }
                            } catch (Exception $e) {
                               echo $e->getMessage();
                                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                            }
                        } else {
                            $this->_helper->FlashMessenger(array('msg-error' => 'Data format is not correct'));
                        }
                    }
                }

                $this->view->successrecords = $success_count;
                $this->view->failedrecords = $failed_count;
                $this->view->records = TRUE;
                $this->view->batch_name = $filename;

                fclose($fp);          
            }     
        }
        $this->view->form = $form;
    }
    
    public function bindtidpurseAction()
    {
        $this->title = "Bind TID to Purse";
        $form = new Corp_Ratnakar_BindTidPurseForm();
        $page = $this->_getParam('page');
        $purse = $this->_getParam('purse');
        $formData = $this->_request->getPost();
        $submit = isset($formData['submit_success']) ? $formData['submit_success'] : '';
        $this->view->incorrectData = FALSE;
        $tidModel = new TidMaster();
        $this->view->records = FALSE;
        
        $productModel = new Products();
        $pid = $productModel->getProductIDbyConst(PRODUCT_CONST_RAT_CTY);
        
        $purseModel = new MasterPurse();
        $productInfo = $purseModel->getPurseList($pid);    

        if(!empty($productInfo)) 
        {
            $form->getElement('purse_id')->setMultiOptions($productInfo);
        }
        else{
         $this->_helper->FlashMessenger( array('msg-error' => 'No product assigned at this moment') );
                   
        }
        
        $this->view->paginator = $tidModel->showPendingTidDetails($page, $paginate = NULL,TRUE);
        
        if ($submit != '') {            
            try {                
                $tidModel->bindTidToPurse($formData['reqid'], $purse);
                $this->_helper->FlashMessenger(
                        array(
                            'msg-success' => 'TID details have been updated in our records',
                        )
                );
                $this->_redirect($this->formatURL('/corp_ratnakar_tid/bindtidpurse/'));
            } catch (Exception $e) {
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                $this->_helper->FlashMessenger(
                        array(
                            'msg-error' => $e->getMessage(),
                        )
                );
            }
        }
        $this->view->form = $form;
    }
    
    public function changestatusAction()
    {
        $this->title = "Change Status";
        $form = new Corp_Ratnakar_ChangeStatusForm();
        $page = $this->_getParam('page');
        $purse = $this->_getParam('purse_id');
        $status = $this->_getParam('status');
        $formData = $this->_request->getPost();
        $submit = isset($formData['submit_success']) ? $formData['submit_success'] : '';
        $this->view->incorrectData = FALSE;
        $tidModel = new TidMaster();
        $this->view->records = FALSE;
        
        $productModel = new Products();
        $pid = $productModel->getProductIDbyConst(PRODUCT_CONST_RAT_CTY);
        
        $purseModel = new MasterPurse();
        $productInfo = $purseModel->getPurseList($pid);    

        if(!empty($productInfo)) 
        {
            $form->getElement('purse_id')->setMultiOptions($productInfo);
        }
        else{
         $this->_helper->FlashMessenger( array('msg-error' => 'No product assigned at this moment') );
                   
        }

        if ($this->getRequest()->isPost() && $submit=='') {
            if ($form->isValid($this->getRequest()->getPost())) {
                $this->view->paginator = $tidModel->getPurseTid($purse);
                $this->view->records = TRUE;
            }
        }
        
        if ($submit != '') {            
            try {                
                $tidModel->changestatus($formData['reqid'], $status);
                $this->_helper->FlashMessenger(
                        array(
                            'msg-success' => 'Status changed successfully',
                        )
                );
                $this->_redirect($this->formatURL('/corp_ratnakar_tid/changestatus/'));
            } catch (Exception $e) {
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                $this->_helper->FlashMessenger(
                        array(
                            'msg-error' => $e->getMessage(),
                        )
                );
            }
        }
        $this->view->form = $form;
    }
}