<?php
/**
 * Ops can view Reports 
 *
 * @package frontend_controllers
 * @copyright company
 */

class Corp_Kotak_ReportsController extends App_Corporate_Controller
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
       
        
    }
    
    /**
     * Controller's entry point
     *
     * @access public
     * @return void
     */
    public function indexAction(){
       
    }
    
//Paytronics load report
       
     public function loadreportAction(){
         $this->title = 'Load Report'; 
         $user = Zend_Auth::getInstance()->getIdentity();
         
         // Get our form and validate it
         $form = new Corp_Kotak_LoadReportForm(array('action' => $this->formatURL('/corp_kotak_reports/loadreport'),
                                                    'method' => 'POST',
                                             )); 
        $bankUnicodeArr = Util::bankUnicodesArray();
        $page = $this->_getParam('page');
        $request = $this->_getAllParams();
        $sub = $this->_getParam('sub');
        $user = Zend_Auth::getInstance()->getIdentity();
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
        //$qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        $qurStr['product_id'] = $this->_getParam('product_id');
        $qurStr['sub'] = $sub;
        $qurStr['to_date']    = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $qurStr['employer_name']  = $this->_getParam('employer_name');
        $qurStr['employer_loc']  = $this->_getParam('employer_loc');
        $qurStr['status']  = $this->_getParam('status');
       
      
         if($sub!=''){ 
              if($form->isValid($qurStr)){ 
                 if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurFrm['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                    $qurFrm['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", 'from');
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", 'to');
                    
                    $this->view->title = 'Load Report for '.$qurStr['from_date'].' To '.$qurStr['to_date'];
                    //$this->view->title .= ' to '.Util::returnDateFormatted($qurFrm['to'], "Y-m-d", "d-m-Y", "-");
                    $this->view->from = $qurFrm['from'];
                    $this->view->to   = $qurFrm['to'];
                }
                 $qurData['product_id'] = $qurStr['product_id'];
                 $qurData['employer_name'] = $qurStr['employer_name'];
                 $qurData['employer_loc'] = $qurStr['employer_loc'];
                 $qurData['status'] = $qurStr['status'];
                 $qurData['by_corporate_id'] = $user->id;
                 if(isset($qurStr['product_id']) && $qurStr['product_id']){
                    $bankModel = new Banks();
                    $bankInfo = $bankModel->getBankbyProductId($qurStr['product_id'],false);
                    $qurData['bank_unicode'] = $bankRatnakarUnicode = $bankInfo->unicode;
                 }else{
                    $bankRatnakar = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR);
                    $qurData['bank_unicode'] = $bankRatnakarUnicode = $bankRatnakar->bank->unicode;
                }
                 
                 //echo "<pre>";print_r($qurData); exit;
                 $objReports = new Reports();
                 $cardload = $objReports->getLoadRequests($qurData);
                 $this->view->paginator = $objReports->paginateByArray($cardload, $page, $paginate = NULL);
                 $this->view->formData = $qurStr;
              }   
             $form->getElement('product')->setValue($qurData['product_id']);
          }
            $this->view->form = $form;
            //$this->view->user_type = $user->user_type;
            
    }
    /* exportloadreportAction function is responsible to create the csv file on fly with agent fund requests report data
     * and let user download that file.
     */
    
     public function exportloadreportAction(){
        
         
         $user = Zend_Auth::getInstance()->getIdentity();
         
         // Get our form and validate it
         $form = new Corp_Kotak_LoadReportForm(array('action' => $this->formatURL('/corp_kotak_reports/loadreport'),
                                                    'method' => 'POST',
                                             )); 
        //$qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $qurStr['product_id'] = $this->_getParam('product_id');
        $qurStr['employer_name']  = $this->_getParam('employer_name');
        $qurStr['employer_loc']  = $this->_getParam('employer_loc');
        $qurStr['status']  = $this->_getParam('status');
    
            
              if(!empty($qurStr)){ 
                  if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-","-", 'to');
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-","-", 'from');
                   
                }
                 $qurData['product_id'] = $qurStr['product_id'];
                 $qurData['employer_name'] = $qurStr['employer_name'];
                 $qurData['employer_loc'] = $qurStr['employer_loc'];
                 $qurData['status'] = $qurStr['status'];
                 $qurData['by_corporate_id'] = $user->id;
                if(isset($qurStr['product_id']) && $qurStr['product_id']){
                    $bankModel = new Banks();
                    $bankInfo = $bankModel->getBankbyProductId($qurStr['product_id'],PROGRAM_TYPE_CORP);
                    $qurData['bank_unicode'] = $bankRatnakarUnicode = $bankInfo->unicode;
                 }else{
                    $bankRatnakar = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR);
                    $qurData['bank_unicode'] = $bankRatnakarUnicode = $bankRatnakar->bank->unicode;
                }
                 $objReports = new Reports();
                 $exportData = $objReports->exportLoadRequests($qurData);
                 
                 $columns = array(
                    'Product Name',
                    'Txn Identifier Type',
                    'Card Number',
                    'Member Id',
                    'Amount',
                    'Cutoff',
                    'Currency',
                    'Narration',
                    'Wallet Code',
                    'Txn Number',
                    'Card Type',
                    'Corporate Id',
                    'Mode',
                    'Txn Reference No.',
                    'Failed Reason',
                    'Status'
                );

                $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'KOTAK_GPR_LOAD_REPORT');exit;
                 }catch (Exception $e) {
                                         App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                         $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                         $this->_redirect($this->formatURL('/corp_kotak_reports/loadreport?sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&product_id='.$qurStr['product_id'].'&bank_unicode='.$qurStr['bank_unicode'].'&status='.$qurStr['status'])); 
                                       }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid Paramaters!') );
                         $this->_redirect($this->formatURL('/corp_kotak_reports/loadreport?sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&product_id='.$qurStr['product_id'].'&bank_unicode='.$qurStr['bank_unicode'].'&status='.$qurStr['status'])); 
                      }             
                  
       }
        
       public function activecardsAction(){
         
            $this->title = 'Kotak Enrollment Report';
            // Get our form and validate it
            $form = new Corp_Kotak_ActivecardsReportForm(array('action' => $this->formatURL('/corp_kotak_reports/activecards'),
                                                    'method' => 'POST',
                                             ));  
            $user = Zend_Auth::getInstance()->getIdentity();
            $request = $this->_getAllParams();
            $sub = $this->_getParam('sub');
            $user = Zend_Auth::getInstance()->getIdentity();
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
            
            $qurStr['dur'] = $this->_getParam('dur');
            $qurStr['to_date']  = $this->_getParam('to_date');
            $qurStr['from_date']  = $this->_getParam('from_date');
            $qurStr['product_id'] = $this->_getParam('product_id');
            $qurStr['employer_name']  = $this->_getParam('employer_name');
            $qurStr['employer_loc']  = $this->_getParam('employer_loc');
            $qurStr['status']  = $this->_getParam('status');
            
        
            $page = $this->_getParam('page');
            $qurStr['sub'] = $sub;
            
             if($sub!=''){ 
                 
                  if($form->isValid($qurStr)){ 
                       $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-","-","to");
                        $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-","-","from");
                        $from = Util::returnDateFormatted($qurData['from'], "Y-m-d", "d-m-Y", "-");
                        $to = Util::returnDateFormatted($qurData['to'], "Y-m-d", "d-m-Y", "-");
                        $fromArr = explode(" ",$from);
                        $toArr = explode(" ",$to);
                        
                        $this->view->from = $qurData['from'];
                        $this->view->to   = $qurData['to'];     
                      $this->view->title = 'Kotak Enrollment Report for '.$qurStr['from_date'].' To '.$qurStr['to_date'];
                     //echo "sdfsdf"; exit;
                     $objCardholders = new Corp_Kotak_Customers();
                     $qurData['product_id'] = $qurStr['product_id'];
                     $qurData['employer_name'] = $qurStr['employer_name'];
                     $qurData['employer_loc'] = $qurStr['employer_loc'];
                     $qurData['status'] = $qurStr['status'];
                
                     $qurData['by_corporate_id'] = $user->id;
                     $fundingDetails = $objCardholders->getCardholders($qurData,TRUE);
                     //echo "<pre>"; print_r($fundingDetails); exit;
                     $this->view->paginator = $objCardholders->paginateByArray($fundingDetails, $page, $paginate=NULL);
                     $this->view->formData = $qurStr; 
                     //$this->view->agent_name = $agentInfo->name;
                     
                  }                  
              }
            $this->view->form = $form;
            $this->view->formData = $qurStr;
            //$this->view->formData = $formData; 
            //$this->view->duration = $duration;
    
            
    }
    /* exportloadreportAction function is responsible to create the csv file on fly with agent fund requests report data
     * and let user download that file.
     */
    
     public function exportactivecardsAction(){
        
         
            $this->title = 'Card Activation Report';
            // Get our form and validate it
            $form = new Corp_Kotak_ActivecardsReportForm(array('action' => $this->formatURL('/corp_kotak_reports/activecards'),
                                                    'method' => 'POST',
                                             ));  
            $user = Zend_Auth::getInstance()->getIdentity();
            $request = $this->_getAllParams();
            $sub = $this->_getParam('sub');
            $qurStr['dur'] = $this->_getParam('dur');
            $qurStr['to_date']  = $this->_getParam('to_date');
            $qurStr['from_date']  = $this->_getParam('from_date');
            $qurStr['product_id'] = $this->_getParam('product_id');
            $qurStr['employer_name']  = $this->_getParam('employer_name');
            $qurStr['employer_loc']  = $this->_getParam('employer_loc');
            $qurStr['status']  = $this->_getParam('status');
            //echo "<pre>"; print_r($qurStr); exit; 
             
              if(!empty($qurStr)){ 
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-","-","to");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-","-","from");
                    $from = Util::returnDateFormatted($qurData['from'], "Y-m-d", "d-m-Y", "-");
                    $to = Util::returnDateFormatted($qurData['to'], "Y-m-d", "d-m-Y", "-");
                    $fromArr = explode(" ",$from);
                    $toArr = explode(" ",$to);
                    
                    $this->view->from = $qurData['from'];
                    $this->view->to   = $qurData['to'];     
                    $this->view->title = 'Load Report '.$bankInfo->name.' for '.Util::returnDateFormatted($qurFrm['from'], "Y-m-d", "d-m-Y", "-");
                    
                    $objCardholders = new Corp_Kotak_Customers();
                    $qurData['product_id'] = $qurStr['product_id'];
                    $qurData['employer_name'] = $qurStr['employer_name'];
                    $qurData['employer_loc'] = $qurStr['employer_loc'];
                    $qurData['status'] = $qurStr['status'];
                
                    $qurData['by_corporate_id'] = $user->id;
                    $cardholderDetails = $objCardholders->getCardholders($qurData,TRUE);
                    //echo "<pre>";print_r($cardholderDetails); exit;
                    $exportData=array();
                    $i=0;
                    foreach($cardholderDetails as $cardholder){
                        $exportData[$i]['date_created']=$cardholder['date_created'];
                        $exportData[$i]['card_number']=$cardholder['card_number'];
                        $exportData[$i]['card_pack_id']=$cardholder['card_pack_id'];
                        $exportData[$i]['cardholder_name']=$cardholder['cardholder_name'];
                        $exportData[$i]['crn']=$cardholder['crn'];
                        $exportData[$i]['mobile']=$cardholder['mobile'];
                        $exportData[$i]['product_name']=$cardholder['product_name'];
                        $exportData[$i]['address_line1']=$cardholder['address_line1'];
                        $exportData[$i]['address_line2']=$cardholder['address_line2'];
                        $exportData[$i]['city']=$cardholder['city'];
                        $exportData[$i]['state']=$cardholder['state'];
                        $exportData[$i]['pincode']=$cardholder['pincode'];
                        $i++;
                    }
                    //echo "<pre>";print_r($exportData); exit;
                    $columns = array(
                        'Date',
                        'Card Number',
                        'Card PackID',
                        'Cardholder Name',
                        'CRN',
                        'Mobile Number',
                        'Product Code',
                        'Address 1',
                        'Address 2',
                        'City',
                        'State',
                        'Pin',
                            
                    );

                    $objCSV = new CSV();
                    try{
                           $resp = $objCSV->export($exportData, $columns, 'KOTAK_ENROLLMENT_REPORT');exit;
                    }catch (Exception $e) {
                                            App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                            $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                            $this->_redirect($this->formatURL('/corp_kotak_reports/activecards?sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&product_id='.$qurStr['product_id'].'&bank_unicode='.$qurStr['bank_unicode'].'&status='.$qurStr['status'])); 
                    }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid Paramaters!') );
                         $this->_redirect($this->formatURL('/corp_kotak_reports/activecards?sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&product_id='.$qurStr['product_id'].'&bank_unicode='.$qurStr['bank_unicode'].'&status='.$qurStr['status'])); 
                      }             
                 
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
    
    public function sampleloadAction(){
         $this->title = 'Load Report'; 
         $user = Zend_Auth::getInstance()->getIdentity();
         
         // Get our form and validate it
         $form = new Corp_Kotak_SampleLoadReportForm(array('action' => $this->formatURL('/corp_kotak_reports/sampleload'),
                                                    'method' => 'POST',
                                             )); 
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
        $bankUnicodeArr = Util::bankUnicodesArray();
        $page = $this->_getParam('page');
        $request = $this->_getAllParams();
        $sub = $this->_getParam('sub');
        
        //$qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        $qurStr['product_id'] = $this->_getParam('product_id');
        $qurStr['sub'] = $sub;
        $qurStr['to_date']    = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $qurStr['status']  = $this->_getParam('status');
        $qurStr['department'] = $this->_getParam('department');
        $qurStr['location'] = $this->_getParam('location');
        $this->view->records = false;
         if($sub!=''){ 
              if($form->isValid($qurStr)){ 
                 if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurFrm['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", 'from');
                    $qurFrm['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", 'to');
                    
                    $this->view->title = 'Report '.$bankInfo->name.' for '.Util::returnDateFormatted($qurStr['from_date'], "Y-m-d", "d-m-Y", "-");
                    $this->view->title .= ' to '.Util::returnDateFormatted($qurStr['to_date'], "Y-m-d", "d-m-Y", "-");
                    $this->view->from = $qurFrm['from'];
                    $this->view->to   = $qurFrm['to'];
                }
                $qurData['from'] = $qurFrm['from'];
                $qurData['to'] = $qurFrm['to'];
                $qurData['product_id'] = $qurStr['product_id'];
                $qurData['by_corporate_id'] = $user->id;
                $qurData['employer_name'] = $qurStr['department'];
                $qurData['employer_loc'] = $qurStr['location'];
                
                if(isset($qurStr['product_id']) && $qurStr['product_id']){
                   $bankModel = new Banks();
                   $bankInfo = $bankModel->getBankbyProductId($qurStr['product_id'],PROGRAM_TYPE_CORP);
                   $qurData['bank_unicode'] = $bankRatnakarUnicode = $bankInfo->unicode;
                }else{
                   $bankKotak = App_DI_Definition_Bank::getInstance(BANK_KOTAK);
                   $qurData['bank_unicode'] = $bankKotakUnicode = $bankKotak->bank->unicode;
                }

                $cardholderModel = new Corp_Kotak_Customers();
                $cardload = $cardholderModel->getCardholders($qurData);
                //echo '<pre>'; print_r($cardload); exit;
                $this->view->paginator = $cardholderModel->paginateByArray($cardload, $page, $paginate = NULL);
                $this->view->formData = $qurStr;
                $this->view->records = true;
              }   
             $form->getElement('product')->setValue($qurData['product_id']);
          }
            $this->view->form = $form;
    }
    
    
    /* exportloadreportAction function is responsible to create the csv file on fly with agent fund requests report data
     * and let user download that file.
     */
    
    public function exportsampleloadAction(){
        
         
         $user = Zend_Auth::getInstance()->getIdentity();
         
         // Get our form and validate it
         $form = new Corp_Kotak_SampleLoadReportForm(array('action' => $this->formatURL('/corp_kotak_reports/sampleload'),
                                                    'method' => 'POST',
                                             )); 

        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $qurStr['product_id'] = $this->_getParam('product_id');
        $qurStr['status']  = $this->_getParam('status');
        $qurStr['department'] = $this->_getParam('department');
        $qurStr['location'] = $this->_getParam('location');
        if(!empty($qurStr))
        { 
                if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $$qurFrm['from_date'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                    $qurFrm['to_date'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                }

                $qurData['product_id'] = $qurStr['product_id'];
                $qurData['status'] = $qurStr['status'];
                $qurData['by_corporate_id'] = $user->id;
                $qurData['employer_name'] = $qurStr['department'];
                $qurData['employer_loc'] = $qurStr['location'];
                if(isset($qurStr['product_id']) && $qurStr['product_id']){
                    $bankModel = new Banks();
                    $bankInfo = $bankModel->getBankbyProductId($qurStr['product_id'],PROGRAM_TYPE_CORP);
                    $qurData['bank_unicode'] = $bankKotakUnicode = $bankInfo->unicode;
                 }else{
                    $bankKotak = App_DI_Definition_Bank::getInstance(BANK_KOTAK);
                    $qurData['bank_unicode'] = $bankKotakUnicode = $bankKotak->bank->unicode;
                }
               
                $cardholderModel = new Corp_Kotak_Customers();
                $sampleData = $cardholderModel->exportSampleLoadRequests($qurData);

                $columns = array(
                    'Txn Identifier Type',
                    'Card Number',
                    'Amount',
                    'Currency',
                    'Narration',
                    'Wallet Code',
                    'Txn Number',
                    'Card Type',
                    'Corporate Id',
                    'Mode'
                );

                $objCSV = new CSV();
                try{
                        $resp = $objCSV->export($sampleData, $columns, 'CARD_LOAD_SAMPLE');exit;
                }catch (Exception $e) {
                    App_Logger::log($e->getMessage() , Zend_Log::ERR);
                    $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                    $this->_redirect($this->formatURL('/corp_kotak_reports/sampleload?sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&product_id='.$qurStr['product_id'].'&bank_unicode='.$qurStr['bank_unicode'].'&status='.$qurStr['status'].'&department='.$qurStr['department'].'&location='.$qurStr['location'])); 
                }
                 
        } else {
            $this->_helper->FlashMessenger( array('msg-error' => 'Invalid Paramaters!') );
            $this->_redirect($this->formatURL('/corp_kotak_reports/sampleload?sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&product_id='.$qurStr['product_id'].'&bank_unicode='.$qurStr['bank_unicode'].'&status='.$qurStr['status'].'&department='.$qurStr['department'].'&location='.$qurStr['location'])); 
        }             
                 
    }
  }