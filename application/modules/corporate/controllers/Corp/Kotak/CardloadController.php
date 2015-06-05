<?php

/**
 * HIC Default entry point
 *
 * @author Vikram
 */
class Corp_Kotak_CardloadController extends App_Corporate_Controller {

    //put your code here


    public function init() {
        parent::init();
        $user = Zend_Auth::getInstance()->getIdentity();
        if(!isset($user->id)) {
           $this->_redirect($this->formatURL('/profile/login'));
           exit;
        }
    }

    public function indexAction() {
        
    }

    public function bulkcardloadAction() {
        $this->title = "Bulk Upload of Card Load";
        $page = $this->_getParam('page');
        $form = new Corp_Kotak_BulkCardloadForm();
        $formData = $this->_request->getPost();
        $reqidArr = isset($formData['reqid']) ? $formData['reqid'] : '';
        $submit = isset($formData['submit_success']) ? $formData['submit_success'] : '';
        $this->view->incorrectData = FALSE;
        $cardloadModel = new Corp_Kotak_Cardload();
        $this->view->records = FALSE;
        $balanceError = FALSE;
        $this->view->sample = 1;
        $user = Zend_Auth::getInstance()->getIdentity();
        
        $quryData['by_corporate_id'] = $user->id;
        $customerModel = new Corp_Kotak_Customers();
        $rs = $customerModel->getCardholders($quryData);
        
        $rsCount = count($rs);
        
        if($rsCount == 0)
        {
            $this->view->sample = 0;
        }
                
        $productModel = new Products();
        $productInfo = $productModel->getCorporateProductsInfo($user->id);            
        if(!empty($productInfo)) 
        {
        $productArr = $this->filterProductArrayForForm($productInfo);
        $form->getElement('product_id')->setMultiOptions($productArr);
        }
        else{
         $this->_helper->FlashMessenger( array('msg-error' => 'No product assigned at this moment',) );
                   
        }

        if ($this->getRequest()->isPost() && $submit=='') {
            if ($form->isValid($this->getRequest()->getPost())) {
                $upload = new Zend_File_Transfer_Adapter_Http();
                $upload->receive();
                $name = $upload->getFileName('doc_path');

                $batchName = $upload->getFileName('doc_path', $path = FALSE) ;
                $checkFile = $cardloadModel->checkBatchFilename($batchName);
                
                //check file extension...
                $upload->addValidator('Extension', false, array(FILE_TYPE_CSV, 'case' => false));
                
                $file_ext = Util::getFileExtension($name);
                if(strtolower($file_ext) != strtolower(FILE_TYPE_CSV))
                {
                   $this->_helper->FlashMessenger( array('msg-error' => 'Invalid file uploaded. Allowed Format is csv only.',) );
                   $this->_redirect($this->formatURL('/corp_kotak_cardload/bulkcardload/'));
                }

                try {
                    $validator = new Validator_LimitValidator();
                    $res = $validator->chkAvailableCorporateBalance($user->id, $formData['value']);
                } catch (App_Exception $e) {
                     $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                     $this->_redirect($this->formatURL('/corp_kotak_cardload/bulkcardload/'));
                } catch (Exception $e) {
                    $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                     $this->_redirect($this->formatURL('/corp_kotak_cardload/bulkcardload/'));
                }
                

                //check for same file name
                if (!$checkFile) 
                {
                    $this->_helper->FlashMessenger(
                        array(
                            'msg-error' => "File already exists",
                        )
                     );
                }
                else
                {
                    $cnt = 0;
                    $val = 0;
                    $fp = fopen($name, 'r');
                    while (!feof($fp)) {
                        $line = fgets($fp);
                        if (!empty($line) && strpos($line,CORP_WALLET_END_OF_FILE) === false) {
                            $delimiter = CORP_WALLET_UPLOAD_DELIMITER;
                            $dataArr = str_getcsv($line, $delimiter);
                            $dataArr['product_id'] = $formData['product_id'];
                            $dataArr['count'] = $formData['count'];
                            $dataArr['value'] = $formData['value'];
                            $consolidateArr[] = $dataArr;
                            $arrLength = Util::getArrayLength($dataArr);
                            if (!empty($dataArr)) {
                                if ($arrLength != CORP_WALLET_UPLOAD_COLUMNS)
                                    $this->view->incorrectData = TRUE;
                                try {
                                    // direct insert into rat_corp_cardholders

                                    if ($dataArr[CORP_WALLET_MANDATORY_FIELD_INDEX] == '') {
                                        $status = STATUS_INCOMPLETE;
                                    } else {
                                        $status = STATUS_TEMP;
                                    }
                                    $cnt++;
                                    $val += $dataArr[2];
                                    $cardloadModel->insertLoadrequestBatch($dataArr, $batchName, $status);
                                } catch (Exception $e) {
                                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                                }

                                
                            } else {
                                //$this->_helper->FlashMessenger(array('msg-error' => 'Data format is not correct'));
                            }
                        }
                    }
                    //echo $val."  =   ".$formData['value']; exit;
                    try {
                        $tempVal = Util::convertToPaisa($val);
                        $validator = new Validator_LimitValidator();
                        $res = $validator->chkAvailableCorporateBalance($user->id, $tempVal);
                    } catch (App_Exception $e) {
                         $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                         $data = array('upload_status' => STATUS_FAILED, 
                            'failed_reason' => 'Corporate does not have sufficient fund.',
                            'date_updated' => new Zend_Db_Expr('NOW()'));
                        $cardloadModel->updateLoadBatch($data, $batchName);
                        $this->_helper->FlashMessenger(
                            array(
                                'msg-error' => "You does not have sufficient fund.",
                            )
                        );
                        $balanceError = true;
                    } catch (Exception $e) {
                        $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                        $data = array('upload_status' => STATUS_FAILED, 
                            'failed_reason' => 'Corporate does not have sufficient fund.',
                            'date_updated' => new Zend_Db_Expr('NOW()'));
                        $cardloadModel->updateLoadBatch($data, $batchName);
                        $this->_helper->FlashMessenger(
                            array(
                                'msg-error' => "You does not have sufficient fund.",
                            )
                        );
                        $balanceError = true;
                    }
                    if($balanceError){
                        $this->view->records = FALSE;
                        
                    }elseif($cnt != $formData['count'])
                    {
                        $data = array('upload_status' => STATUS_FAILED, 
                            'failed_reason' => 'Count does not match the entered count',
                            'date_updated' => new Zend_Db_Expr('NOW()'));
                        $cardloadModel->updateLoadBatch($data, $batchName);
                        $this->_helper->FlashMessenger(
                            array(
                                'msg-error' => "Count does not match the entered count",
                            )
                        );
                        $this->view->records = FALSE;
                    }
                    //elseif($val - Util::filterAmount($formData['value']) != 0)
                    elseif(!Util::compareAmount ($val,Util::filterAmount($formData['value'])))
                    {
                      
                        $data = array('upload_status' => STATUS_FAILED, 
                            'failed_reason' => 'Value does not match the entered value of load',
                            'date_updated' => new Zend_Db_Expr('NOW()'));
                        $cardloadModel->updateLoadBatch($data, $batchName);
                        $this->_helper->FlashMessenger(
                            array(
                                'msg-error' => "Value does not match the entered value of load",
                            )
                        );
                        $this->view->records = FALSE;
                    }
                    else
                    {
                        $this->_helper->FlashMessenger(
                            array(
                                'msg-success' => "File uploaded successfully",
                            )
                        );
                        $this->view->records = TRUE;
                        $this->view->paginator = $cardloadModel->showPendingCardloadDetails($batchName, $page, $paginate = NULL);
                        $this->view->failedpaginator = $cardloadModel->showFailedPendingCardloadDetails($batchName, $page, $paginate = NULL);
                    }
                    $this->view->batch_name = $batchName;

                    fclose($fp);
                }
                
                    
                
            }
        }

        if ($submit != '') {


            try {

                $cardloadModel->bulkAddCardload($formData['reqid'], $formData['batch']);
                $this->_helper->FlashMessenger(
                        array(
                            'msg-success' => 'Corporate Wallet details have been updated in our records',
                        )
                );
                $this->_redirect($this->formatURL('/corp_kotak_cardload/bulkcardload/'));
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
        //$this->view->records = TRUE;
        //$this->view->paginator = $cardloadModel->showPendingCardloadDetails('BUPNSDC032714131009.csv', $page, $paginate = NULL);
       // $cardLoadResp = $cardloadModel->doCorporateLoad();
    }

    public function cardloadAction() {
        $this->title = "Card Load";
        $page = $this->_getParam('page');
        $form = new Corp_Kotak_CardLoadForm();
        $formData = $this->_request->getPost();
        $reqidArr = isset($formData['reqid']) ? $formData['reqid'] : '';
        $submit = isset($formData['submit_success']) ? $formData['submit_success'] : '';
        $this->view->incorrectData = FALSE;
        $cardloadModel = new Corp_Kotak_Cardload();
        $this->view->records = FALSE;
        $user = Zend_Auth::getInstance()->getIdentity();
        $custModel = new Corp_Kotak_Customers();
        $balanceError = FALSE;
        $balanceErrorMsg = '';
        
        $productModel = new Products();
        $productInfo = $productModel->getCorporateProductsInfo($user->id);            
        if(!empty($productInfo)) 
        {
            $productArr = $this->filterProductArrayForForm($productInfo);
            $form->getElement('product_id')->setMultiOptions($productArr);
        }
        else{
             $this->_helper->FlashMessenger( array('msg-error' => 'No product assigned at this moment',) );
             $this->view->records = FALSE;
        }
        $dataArr = array();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                
                
            try {
                if(strtolower($formData['txn_identifier_type']) == CORP_WALLET_TXN_IDENTIFIER_CN){	
		   $searchArr = array('card_number' => $formData['identifier_number']); 
		}elseif(strtolower($formData['txn_identifier_type']) == CORP_WALLET_TXN_IDENTIFIER_MI){
		    $searchArr = array('member_id' => $formData['identifier_number']); 
		}elseif(strtolower($formData['txn_identifier_type']) == CORP_WALLET_TXN_IDENTIFIER_EI){
		   $searchArr = array('employee_id' => $formData['identifier_number']); 
		}	
                $cardholderDetails = $custModel->getCardholderInfo($searchArr);
                 
                try {
                    $validator = new Validator_LimitValidator();
                    $res = $validator->chkAvailableCorporateBalance($user->id, $formData['amount']);
                } catch (App_Exception $e) {
                     $balanceErrorMsg = $e->getMessage();
                     $balanceError = TRUE;
                } catch (Exception $e) {
                    $balanceErrorMsg = $e->getMessage();
                    $balanceError = TRUE;
                }
                
                if($formData['amount'] <= 0){
                    
                    $this->_helper->FlashMessenger( array('msg-error' => 'Invalid amount',) );
                }elseif($balanceError){
                    
                    $this->_helper->FlashMessenger( array('msg-error' => $balanceErrorMsg,) );
                }elseif(!isset($cardholderDetails->id) || empty($cardholderDetails->id)){
                    $this->_helper->FlashMessenger( array('msg-error' => 'Cardholder not found',) );
                }else{
                    $dataArr = $formData;
                    $validaeArr = $dataArr;
                    $validaeArr['card_number'] = $formData['identifier_number'];
                    $valid = $cardloadModel->isValid($validaeArr);
                    $dataArr['corporate_id'] = $cardholderDetails->corporate_id;
                    $dataArr['card_number'] =0;
                    if($cardholderDetails->card_number){
                        $dataArr['card_number'] = $cardholderDetails->card_number;
                    }
                    $dataArr['member_id'] =0;
                    if($cardholderDetails->member_id){
                        $dataArr['member_id'] = $cardholderDetails->member_id;
                    }
                    $dataArr['employee_id'] =0;
                    if($cardholderDetails->employee_id){
                        $dataArr['employee_id'] = $cardholderDetails->employee_id;
                    }
                    if(!$valid){ 
                        $this->_helper->FlashMessenger( array('msg-error' => $cardloadModel->getError(),) ); 
                    }else{
                    
                        $resId = $cardloadModel->insertLoadrequestForLog($dataArr);
                        $cardloadModel->addCardload($resId);
                        $this->_helper->FlashMessenger(
                                array(
                                    'msg-success' => 'Load details have been updated in our records',
                                )
                        );
                        $this->_redirect($this->formatURL('/corp_kotak_cardload/cardload/'));
                    }    
                }
            } catch (Exception $e) {
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                $this->_helper->FlashMessenger(
                        array(
                            'msg-error' => $e->getMessage(),
                        )
                );
            }
                            
            }
        }

       
        $this->view->form = $form;
    }
    
   private function filterProductArrayForForm($productInfo) {
        $productArr = array('' =>'Select Product');
        if (!empty($productInfo)) {
            foreach ($productInfo as $product) {
                $productArr[$product['product_id']] = $product['product_name'];
            }
        }
        return $productArr;
    }
}
