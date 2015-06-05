<?php
/**
 * MVC Axis Bank Import CRN Cardholder
 *
 * @package frontend_controllers
 * @copyright company
 */

class Mvc_Axis_ImportCRNController extends App_Operation_Controller
{
    /**
     * Overrides Zend_Controller_Action::init()
     *
     * @access public
     * @return void
     */
    public function init()
    {
        
        // init the parent
        parent::init();
        
        //$this->_addCommand(new App_Command_SendEmail());
        
    }
    
    /* adddetailsAction is responsible for handling taking crn details like file, product name etc..       
     */
    
    public function adddetailsAction() {

        $this->title = 'Import CRN';
        $user = Zend_Auth::getInstance()->getIdentity();
        $session = new Zend_Session_Namespace('App.Operation.Controller');
        $bankId = $this->_getParam('bank_id');
        $config = App_DI_Container::get('ConfigObject');
        $isError = false;
        $objBanks = new Banks();

        // Get our form and validate it
        $form = new Mvc_Axis_ImportCRNAdddetailsForm(array(
                    'action' => $this->formatURL('/mvc_axis_importcrn/adddetails'),
                    'method' => 'post',
                    'name' => 'frmImportcrn',
                    'id' => 'frmImportcrn'
                ));


        $this->view->form = $form;
        $formData = $this->_request->getPost();

        $btnSubmit = isset($formData['submit']) ? $formData['submit'] : '';
        $products = new Products();
        $productList = $products->getBankProducts($bankId, PROGRAM_TYPE_MVC);
        $productListArr = array_merge(array('' => 'All Products'), $productList);
        $form->getElement("product_id")->setMultiOptions($productListArr);
        //$form->getElement("bank_id")->setValue($bankId);
        $bankName='';
        $bankInfo='';
        if($bankId>0){
            $bankInfo = $objBanks->getBankInfo($bankId);
            $bankInfo = $bankInfo->toArray();
            $bankName = $bankInfo['name'];
            $form->getElement("bank_id")->setValue($bankId);
            $form->getElement("bank_name")->setValue($bankName);
            //echo  $form->getElement("bank_name"); exit;
        }

        $uploadlimit = $config->operation->uploadimportcsvfile->size;

        // adding details in db
        if ($btnSubmit) {

            if ($form->isValid($this->getRequest()->getPost())) {
                
               //$bankId = isset($formData['bank_id'])?$formData['bank_id']:'';
               $unicode   = isset($bankInfo['unicode'])?$bankInfo['unicode']:'';
               $crnPaths  = Zend_Registry::get('BANK_UNICODE_IMPORTCRN_PATH');   
              
               $redirPath = isset($crnPaths[$unicode])?$crnPaths[$unicode]:'';
               if($redirPath==''){
                   $isError =true;                  
                   $bankName = isset($bankInfo['name'])?$bankInfo['name']:'';
                   $this->_helper->FlashMessenger( array( 'msg-error' => 'Import CRN feature not available for '.$bankName));
               }
               
               if(!$isError){
                //print '<pre>';print_r($_POST);exit;
                $fileName = isset($_FILES['crn_file']['name']) ? $_FILES['crn_file']['name'] : '';
                if ($fileName != '') {
                    $fileNameArr = explode('.', $fileName);
                    $fileNameArr[0] = time();
                    $_FILES['crn_file']['name'] = implode('.', $fileNameArr);
                }

                $crnFileName = $_FILES['crn_file']['name'];
                if (trim($crnFileName) != '') {
                    //upload files
                    $upload = new Zend_File_Transfer_Adapter_Http();
                    // $uploadInfo = $upload->getFileInfo();
                    // Add Validators for uploaded file's extesion , mime type and size
                    $upload->addValidator('Extension', false, array('txt', 'case' => false))
                            ->addValidator('FilesSize', false, array('min' => '1kB', 'max' => $uploadlimit));

                    $upload->setDestination(UPLOAD_IMPORTCRN_PATH . '/');

                    try {

                        //All validations correct then upload file
                        if ($upload->isValid()) {
                            // upload received file(s)
                            $upload->receive();
                        } else {
                            $this->_helper->FlashMessenger(
                                    array(
                                        'msg-error' => 'CRN file could not be uploaded. Allowed Format is txt only.',
                                    )
                            );
                            $isError = TRUE;
                        }
                    } catch (Zend_File_Transfer_Exception $e) {
                        //echo '----------------------';exit;
                        $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage(),));
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                        $isError = TRUE;
                    } 

                    if (empty($e) && !$isError) {
                        $session->crn_file_name         = $crnFileName;
                        $session->ecs_crn_bank_id       = $this->_getParam('bank_id');
                        $session->ecs_crn_product_id    = $this->_getParam('product_id');
                        $this->_redirect($this->formatURL('/mvc_axis_importcrn/previewcrn/'));
                    }
                }
            }
          }
        }
    }


    /* previewcrnAction is responsible for to show the crn list for preview and confirmation       
     */
    
    public function previewcrnAction()
    {   
        $this->title = 'Preview CRN List'; 
        $user = Zend_Auth::getInstance()->getIdentity();        
        $session = new Zend_Session_Namespace('App.Operation.Controller'); 
        $page = $this->_getParam('page');
        if (!isset($session->ecs_crn_bank_id) || $session->ecs_crn_bank_id == '' || !isset($session->ecs_crn_product_id)) {
            $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'Invalid Bank or Product.'
                    )
            );
            $this->_redirect($this->formatURL('/mvc_axis_importcrn/adddetails/'));
        }

        $form = new Mvc_Axis_PreviewCRNForm(array(
                                                    'action' => $this->formatURL('/mvc_axis_importcrn/savecrn'),
                                                    'method' => 'post',
                                                    'name'=>'frmPreviewcrn',
                                                    'id'=>'frmPreviewcrn'
                                                  ));   
      
        $crnFilename = isset($session->crn_file_name)?$session->crn_file_name:'';
        $this->view->form = $form;
        $objCsv = new CSV();
        $isError = false;
       
        if(trim($crnFilename)!=''){
            // getting crn array from txt file of crns
            $crnArr = $objCsv->getCRNList($crnFilename);
            //echo '<pre>';print_r($crnArr);exit;            
            $paginator = $objCsv->paginateByArray($crnArr, $page, $paginate = NULL);
            //echo '<pre>';print_r($paginator);exit;
            $this->view->paginator = $paginator;
            //$this->view->crn = $crn;
        } else {
                $isError = true;
                $this->_helper->FlashMessenger(array('msg-error' => 'CRN file missing!',));
                App_Logger::log('CRN file missing', Zend_Log::ERR);
        }
        
    }
    
    
    /* savecrnAction is responsible for save the all crns in db       
     */
    
    public function savecrnAction()
    {   
        
        $this->title = 'Save CRN'; 
        $session = new Zend_Session_Namespace('App.Operation.Controller'); 
        $crnFilename = isset($session->crn_file_name)?$session->crn_file_name:'';
        $banks = new Banks();
        $product = new Products();        
        $csv = new CSV();
        $isError = false;
       
        if(trim($crnFilename)!=''){
            try{
                // getting crn array from txt file of crns
                $crn = $csv->getCRNList($crnFilename);
                // adding crns to db
                //$objECS = new ECS();
                $productArr = $product->findById($session->crn_crn_product_id );
                $bankArr = $banks->findById($session->ecs_crn_bank_id);                
                $objUnicode = new Unicode();
                $objUnicode->_PRODUCT_UNICODE = $productArr['unicode'] ;
                $objUnicode->_BANK_UNICODE = $bankArr['unicode'];                
                $flg = $objUnicode->addUnicodeCRN($crn);
                if(!$flg) {
                    $isError = true;
                    $this->_helper->FlashMessenger(array('msg-error' => 'Invalid/Duplicate CRN Provided'));                    
                } else {
                    $this->_helper->FlashMessenger(array('msg-success' => 'CRN Imported successfully'));                                        
                }
            } catch (Exception $e) {
                    $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage()));
                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
            }
        } else {
                $isError = true;
                $this->_helper->FlashMessenger(array('msg-error' => 'CRN file missing!',));
                App_Logger::log('CRN file missing', Zend_Log::ERR);
        }
        
        $this->_redirect($this->formatURL('/mvc_axis_importcrn/success/'));             
        
    }
    
    
    public function successAction()
    {
        
    }
    
    public function cancelAction(){
        $session = new Zend_Session_Namespace('App.Operation.Controller');         
        unset($session->crn_file_name);
        unset($session->ecs_crn_bank_id);
        unset($session->crn_crn_product_id);        
        $this->_helper->FlashMessenger(
                array(
                    'msg-error' => 'Cancelled the import crn process.'
                )
        );
        $this->_redirect($this->formatURL('/mvc_axis_importcrn/adddetails/'));        
    }
    
}


    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    