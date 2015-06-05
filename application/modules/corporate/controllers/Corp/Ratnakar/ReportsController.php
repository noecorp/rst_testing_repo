<?php
/**
 * Ops can view Reports 
 *
 * @package frontend_controllers
 * @copyright company
 */

class Corp_Ratnakar_ReportsController extends App_Operation_Controller
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
         $form = new Corp_Ratnakar_LoadReportForm(array('action' => $this->formatURL('/corp_ratnakar_reports/loadreport'),
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
                    $qurFrm['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                    $qurFrm['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", 'from');
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", 'to');
                    
                    $this->view->title = 'Load Report for '.Util::returnDateFormatted($qurFrm['from'], "Y-m-d", "d-m-Y", "-");
                    $this->view->title .= ' to '.Util::returnDateFormatted($qurFrm['to'], "Y-m-d", "d-m-Y", "-");
                    $this->view->from = $qurFrm['from'];
                    $this->view->to   = $qurFrm['to'];
                }
                $qurData['product_id'] = $qurStr['product_id'];
                $qurData['department'] = $qurStr['department'];
                $qurData['location'] = $qurStr['location'];
                       
                $qurData['by_corporate_id'] = $user->id;
                if(isset($qurStr['product_id']) && $qurStr['product_id']){
                   $bankModel = new Banks();
                   $bankInfo = $bankModel->getBankbyProductId($qurStr['product_id'],PROGRAM_TYPE_CORP);
                   $qurData['bank_unicode'] = $bankRatnakarUnicode = $bankInfo->unicode;
                }else{
                   $bankRatnakar = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR);
                   $qurData['bank_unicode'] = $bankRatnakarUnicode = $bankRatnakar->bank->unicode;
                }
                $qurData['status'] = $qurStr['status'];
                //echo "<pre>";print_r($qurData); exit;
                $objReports = new Reports();
                $cardload = $objReports->getLoadRequests($qurData);
                //echo '<pre>'; print_r($cardload); exit;
                $this->view->paginator = $objReports->paginateByArray($cardload, $page, $paginate = NULL);
                $this->view->formData = $qurStr;
                $this->view->records = true;
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
         $form = new Corp_Ratnakar_LoadReportForm(array('action' => $this->formatURL('/corp_ratnakar_reports/loadreport'),
                                                    'method' => 'POST',
                                             )); 
        //$qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        //$qurStr['dur'] = $this->_getParam('dur');
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $qurStr['product_id'] = $this->_getParam('product_id');
        $qurStr['status']  = $this->_getParam('status');
        $qurStr['department'] = $this->_getParam('department');
        $qurStr['location'] = $this->_getParam('location');
           
    
        if(!empty($qurStr))
        { 
                if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-","-", 'to');
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-","-", 'from');
                   
                }
                $qurData['product_id'] = $qurStr['product_id'];
                $qurData['status'] = $qurStr['status'];
                $qurData['department'] = $qurStr['department'];
                $qurData['location'] = $qurStr['location'];
                       
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
                $csvData = array();
                $columnIndex = array(
                    'product_name',
                    'txn_identifier_type',
                    'card_number',
                    'medi_assist_id',
                    'amount',
                    'amount_cutoff',
                    'currency',
                    'narration',
                    'wallet_code',
                    'txn_no',
                    'card_type',
                    'corporate_id',
                    'mode',
                    'txn_code',
                    'failed_reason',
                    'status',
                    'channel'
                );
                foreach($exportData as $key => $data){
                    foreach($columnIndex as $index){
                        $csvData[$key][$index] = $data[$index];
                    }
                }
                  
                 $columns = array(
                    'Product Name',
                    'Txn Identifier Type',
                    'Card Number',
                    'Member Id',
                    'Amount',
                    'Amount Cutoff',
                    'Currency',
                    'Narration',
                    'Wallet Code',
                    'Txn Number',
                    'Card Type',
                    'Corporate Id',
                    'Mode',
                    'Txn Reference No.',
                    'Failed Reason',
                    'Status',
                    'Channel'
                );

                $objCSV = new CSV();
                try{
                        $resp = $objCSV->export($csvData, $columns, 'RATNAKAR_LOAD_REPORT');exit;
                }catch (Exception $e) {
                    App_Logger::log($e->getMessage() , Zend_Log::ERR);
                    $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                    $this->_redirect($this->formatURL('/corp_ratnakar_reports/loadreport?sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&product_id='.$qurStr['product_id'].'&bank_unicode='.$qurStr['bank_unicode'].'&status='.$qurStr['status'])); 
                }
                 
        } else {
            $this->_helper->FlashMessenger( array('msg-error' => 'Invalid Paramaters!') );
            $this->_redirect($this->formatURL('/corp_ratnakar_reports/loadreport?sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&product_id='.$qurStr['product_id'].'&bank_unicode='.$qurStr['bank_unicode'].'&status='.$qurStr['status'])); 
        }             
                 
    }

       
    public function activecardsAction(){
         
            $this->title = 'Card Activation Report';
            // Get our form and validate it
            $form = new Corp_Ratnakar_ActivecardsReportForm(array('action' => $this->formatURL('/corp_ratnakar_reports/activecards'),
                                                    'method' => 'POST',
                                             ));  
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
            
            $request = $this->_getAllParams();
            $sub = $this->_getParam('frmsum');
            $qurStr['dur'] = $this->_getParam('dur');
            $qurStr['to_date']  = $this->_getParam('to_date');
            $qurStr['from_date']  = $this->_getParam('from_date');
            $qurStr['product_id'] = $this->_getParam('product_id');
            $qurStr['status']  = $this->_getParam('status');
            $qurStr['department'] = $this->_getParam('department');
            $qurStr['location'] = $this->_getParam('location');
           
            
            $this->view->records = false;
             if($sub!=''){ 
                 $qurStr['frmsum'] = $sub;
                if($form->isValid($qurStr)){
                        if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {    
                            $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-","-","to");
                            $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-","-","from");
                            $from = Util::returnDateFormatted($qurData['from'], "Y-m-d", "d-m-Y", "-");
                            $to = Util::returnDateFormatted($qurData['to'], "Y-m-d", "d-m-Y", "-");
                            $fromArr = explode(" ",$from);
                            $toArr = explode(" ",$to);
                            $this->view->from = $qurData['from'];
                            $this->view->to   = $qurData['to'];     
                            $this->view->title = 'Card Activation Report for '.$qurStr['from_date'].' To '.$qurStr['to_date'];
                       
                         }
                       
                        $objCardholders = new Corp_Ratnakar_Cardholders();
                        $qurData['product_id'] = $qurStr['product_id'];
                        $qurData['status'] = $qurStr['status'];
                        $qurData['by_corporate_id'] = $user->id;
                        $qurData['department'] = $qurStr['department'];
                        $qurData['location'] = $qurStr['location'];
                        $fundingDetails = $objCardholders->getCardholders($qurData,TRUE);
                        //echo "<pre>"; print_r($fundingDetails); exit;
                        $this->view->paginator = $objCardholders->paginateByArray($fundingDetails, $page, $paginate);
                        //echo "<pre>"; print_r($fundingDetails); exit;
                        $this->view->formData = $qurStr;
                        $this->view->records = true;
                        //$this->view->agent_name = $agentInfo->name;
                     
                }                  
              }
            $this->view->form = $form;
            $this->view->formData = $qurStr;
            $form->populate($qurStr);
    
            
    }
    /* exportloadreportAction function is responsible to create the csv file on fly with agent fund requests report data
     * and let user download that file.
     */
    
    public function exportactivecardsAction(){
        
        $this->title = 'Card Activation Report';
        // Get our form and validate it
        $form = new Corp_Ratnakar_ActivecardsReportForm(array('action' => $this->formatURL('/corp_ratnakar_reports/activecards'),
                                                'method' => 'POST',
                                         ));  
        $user = Zend_Auth::getInstance()->getIdentity();
        $request = $this->_getAllParams();
        $sub = $this->_getParam('sub');
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $qurStr['product_id'] = $this->_getParam('product_id');
        $qurStr['status'] = $this->_getParam('status');
        $qurStr['department'] = $this->_getParam('department');
        $qurStr['location'] = $this->_getParam('location');

         
        if(!empty($qurStr)){
                if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {   
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-","-","to");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-","-","from");
                    $from = Util::returnDateFormatted($qurData['from'], "Y-m-d", "d-m-Y", "-");
                    $to = Util::returnDateFormatted($qurData['to'], "Y-m-d", "d-m-Y", "-");
                    $fromArr = explode(" ",$from);
                    $toArr = explode(" ",$to);
                    
                    $this->view->from = $qurData['from'];
                    $this->view->to   = $qurData['to'];     
                    $this->view->title = 'Load Report '.$bankInfo->name.' for '.Util::returnDateFormatted($qurFrm['from'], "Y-m-d", "d-m-Y", "-");
                }
                $objCardholders = new Corp_Ratnakar_Cardholders();
                $qurData['product_id'] = $qurStr['product_id'];
                $qurData['by_corporate_id'] = $user->id;
                $qurData['status'] = $qurStr['status'];
                $qurData['department'] = $qurStr['department'];
                $qurData['location'] = $qurStr['location'];
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
                    $exportData[$i]['country']=$cardholder['country'];
		    $exportData[$i]['channel']= ucfirst($cardholder['channel']);
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
                    'Country',    
		    'Channel'
                );

                $objCSV = new CSV();
                try{
                       $resp = $objCSV->export($exportData, $columns, 'RATNAKAR_CARDHOLDER_ENROLLMENT');exit;
                }catch (Exception $e) {
                                        App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                        $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                        $this->_redirect($this->formatURL('/corp_ratnakar_reports/activecards?sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&product_id='.$qurStr['product_id'].'&bank_unicode='.$qurStr['bank_unicode'].'&status='.$qurStr['status'].'&department='.$qurStr['department'].'&location='.$qurStr['location'])); 
                }
             
        } else {
            $this->_helper->FlashMessenger( array('msg-error' => 'Invalid Paramaters!') );
            $this->_redirect($this->formatURL('/corp_ratnakar_reports/activecards?sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&product_id='.$qurStr['product_id'].'&bank_unicode='.$qurStr['bank_unicode'].'&status='.$qurStr['status'].'&department='.$qurStr['department'].'&location='.$qurStr['location'])); 
        }             
                 
    }
    
    public function sampleloadAction(){
         $this->title = 'Load Report'; 
         $user = Zend_Auth::getInstance()->getIdentity();
         
         // Get our form and validate it
         $form = new Corp_Ratnakar_SampleLoadReportForm(array('action' => $this->formatURL('/corp_ratnakar_reports/sampleload'),
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
                    $qurFrm['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                    $qurFrm['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                    $qurData['apprvfrom'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", 'from');
                    $qurData['apprvto'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", 'to');
                    
                    $this->view->title = 'Report '.$bankInfo->name.' for '.Util::returnDateFormatted($qurFrm['from'], "Y-m-d", "d-m-Y", "-");
                    $this->view->title .= ' to '.Util::returnDateFormatted($qurFrm['to'], "Y-m-d", "d-m-Y", "-");
                    $this->view->from = $qurFrm['from'];
                    $this->view->to   = $qurFrm['to'];
                }
                $qurData['product_id'] = $qurStr['product_id'];
                $qurData['by_corporate_id'] = $user->id;
                $qurData['department'] = $qurStr['department'];
                $qurData['location'] = $qurStr['location'];
                
                if(isset($qurStr['product_id']) && $qurStr['product_id']){
                   $bankModel = new Banks();
                   $bankInfo = $bankModel->getBankbyProductId($qurStr['product_id'],PROGRAM_TYPE_REMIT);
                   $qurData['bank_unicode'] = $bankRatnakarUnicode = $bankInfo->unicode;
                }else{
                   $bankRatnakar = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR);
                   $qurData['bank_unicode'] = $bankRatnakarUnicode = $bankRatnakar->bank->unicode;
                }
                //$qurData['status'] = $qurStr['status'];
                //echo "<pre>";print_r($qurData); exit;
                $cardholderModel = new Corp_Ratnakar_Cardholders();
                $cardload = $cardholderModel->getCardholders($qurData);
                //echo '<pre>'; print_r($cardload); exit;
                $this->view->paginator = $cardholderModel->paginateByArray($cardload, $page, $paginate = NULL);
                $this->view->formData = $qurStr;
                $this->view->records = true;
              }   
             $form->getElement('product')->setValue($qurData['product_id']);
          }
            $this->view->form = $form;
            
            //$this->view->user_type = $user->user_type;
            
    }
    /* exportloadreportAction function is responsible to create the csv file on fly with agent fund requests report data
     * and let user download that file.
     */
    
    public function exportsampleloadAction(){
        
         
         $user = Zend_Auth::getInstance()->getIdentity();
         
         // Get our form and validate it
         $form = new Corp_Ratnakar_SampleLoadReportForm(array('action' => $this->formatURL('/corp_ratnakar_reports/sampleload'),
                                                    'method' => 'POST',
                                             )); 
        //$qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        //$qurStr['dur'] = $this->_getParam('dur');
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
                    $qurData['apprvfrom'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", 'from');
                    $qurData['apprvto'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", 'to');
                   
                }
                //echo "<pre>vijay"; print_r($qurData); exit; 
                $qurData['product_id'] = $qurStr['product_id'];
                $qurData['status'] = $qurStr['status'];
                $qurData['by_corporate_id'] = $user->id;
                $qurData['department'] = $qurStr['department'];
                $qurData['location'] = $qurStr['location'];
                if(isset($qurStr['product_id']) && $qurStr['product_id']){
                    $bankModel = new Banks();
                    $bankInfo = $bankModel->getBankbyProductId($qurStr['product_id'],PROGRAM_TYPE_REMIT);
                    $qurData['bank_unicode'] = $bankRatnakarUnicode = $bankInfo->unicode;
                 }else{
                    $bankRatnakar = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR);
                    $qurData['bank_unicode'] = $bankRatnakarUnicode = $bankRatnakar->bank->unicode;
                }
               
                $cardholderModel = new Corp_Ratnakar_Cardholders();
                $sampleData = $cardholderModel->exportSampleLoadRequests($qurData);
                //echo "<pre>vijay"; print_r($sampleData); exit; 
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
                    'Mode',
                );

                $objCSV = new CSV();
                try{
                        $resp = $objCSV->export($sampleData, $columns, 'CARD_LOAD_SAMPLE');exit;
                }catch (Exception $e) {
                    App_Logger::log($e->getMessage() , Zend_Log::ERR);
                    $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                    $this->_redirect($this->formatURL('/corp_ratnakar_reports/sampleload?sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&product_id='.$qurStr['product_id'].'&bank_unicode='.$qurStr['bank_unicode'].'&status='.$qurStr['status'].'&department='.$qurStr['department'].'&location='.$qurStr['location'])); 
                }
                 
        } else {
            $this->_helper->FlashMessenger( array('msg-error' => 'Invalid Paramaters!') );
            $this->_redirect($this->formatURL('/corp_ratnakar_reports/sampleload?sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&product_id='.$qurStr['product_id'].'&bank_unicode='.$qurStr['bank_unicode'].'&status='.$qurStr['status'].'&department='.$qurStr['department'].'&location='.$qurStr['location'])); 
        }             
                 
    }
    
    
    public function corporatefundingAction(){
         $this->title = 'Corporate Funding Report';              
         // Get our form and validate it
         $form = new CorporateFundingReportForm(array('action' => $this->formatURL('/corp_ratnakar_reports/corporatefunding'),
                                                    'method' => 'POST',
                                             ));
        $user = Zend_Auth::getInstance()->getIdentity();
        $page = $this->_getParam('page');
        $request = $this->_getAllParams();
        $qurStr['to_date'] = $this->_getParam('to_date');
        $qurStr['from_date'] = $this->_getParam('from_date');
        $qurStr['sub'] = $this->_getParam('sub');
        
        
         if($qurStr['sub']!=''){ 
              $formData  = $this->_request->getPost();
              if($form->isValid($qurStr)){ 
                  if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
	            $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
	            $durationDates = Util::getDurationRangeAllDates($qurData);$from = Util::returnDateFormatted($qurData['from'], "Y-m-d", "d-m-Y", "-");
                    $to = Util::returnDateFormatted($qurData['to'], "Y-m-d", "d-m-Y", "-");
                    $fromArr = explode(" ",$from);
                    $toArr = explode(" ",$to);
                    $this->view->title = 'Corporate Funding Report for '.$fromArr[0];
	            $this->view->title .= ' to '.$toArr[0];
                    $this->view->from = $qurData['from'];
                    $this->view->to   = $qurData['to'];
                }
                 $objReports = new CorporateFunding();
                 $fundingDetails = $objReports->getCorporateFunding($durationDates, $user->id);
                 $this->view->paginator = $objReports->paginateByArray($fundingDetails, $page, $paginate);
              }
                 $this->view->formData = $qurStr;
                 
          }
            $this->view->form = $form;
            
    }
    
    
    /* exportcorporatefundrequestsAction function is responsible to create the csv file on fly with agent fund requests report data
     * and let user download that file.
     */
    
    public function exportcorporatefundingAction(){
        
        // Get our form and validate it
         $form = new CorporateFundingReportForm(array('action' => $this->formatURL('/corp_ratnakar_reports/corporatefunding'),
                                                    'method' => 'POST',
                                             )); 
         
        $user = Zend_Auth::getInstance()->getIdentity();
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['to_date'] = $this->_getParam('to_date');
        $qurStr['from_date'] = $this->_getParam('from_date');
        
        
             
              if($form->isValid($qurStr)){ 
                if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-",'to');
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-",'from');
                    $durationDates = Util::getDurationRangeAllDates($qurData);$from = Util::returnDateFormatted($qurData['from'], "Y-m-d", "d-m-Y", "-");
                    
                }
                 $qurData['agent_id'] = $user->id;
                 
                 $objReports = new CorporateFunding();
                 $fundingDetails = $objReports->getCorporateFunding($durationDates, $user->id);
                
                 $columns = array(
                    'Transaction Date',
                    'Transfer Type',
                    'Txn Code',
                    'Amount',
                    'Status',
                    'Remarks',
                );

                $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($fundingDetails, $columns, 'corporate_funding');exit;
                 } 
                 catch (Exception $e) {
                    App_Logger::log($e->getMessage() , Zend_Log::ERR);
                    $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                    $this->_redirect($this->formatURL('/corp_ratnakar_reports/corporatefunding?sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'])); 
                }
                 
               } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Invalid data found!') );
                    $this->_redirect($this->formatURL('/corp_ratnakar_reports/corporatefunding?sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'])); 
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
  }