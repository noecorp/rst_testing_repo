<?php
/**
 * Ops can view Reports 
 *
 * @package frontend_controllers
 * @copyright company
 */

class Corp_Ratnakar_ReportsController extends App_Agent_Controller
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
    


    private function getReportTitle($reportTitle, $dur, $agentId = 0, $singleDayOnly=false,$bankUnicode = '')
    {
        
        $title = $reportTitle;
        if($agentId > 0)
        {
            $objAgent = new Agents();
            $agentInfo = $objAgent->findById($agentId);
            $title .= ' For '. $agentInfo->name;
            
        }
        if($bankUnicode != '')
        {
            $objBank = new Banks();
            $bankInfo = $objBank->getBankbyUnicode($bankUnicode);
            $title .= ' of '. $bankInfo->name;
            
        }
        if(!$singleDayOnly){
            $durationArr = Util::getDurationDates($dur);
            $fromDate = explode(' ', Util::returnDateFormatted($durationArr['from'], "d-m-Y", "Y-m-d", "-"));
            $toDate = explode(' ', Util::returnDateFormatted($durationArr['to'], "d-m-Y", "Y-m-d", "-"));
            switch($dur)
            {
                case 'yesterday': 
                    $title .= ' For ' .$toDate[0]; break;
                case 'today': 
                    $title .= ' For ' .$toDate[0]; break;
                case 'week':
                case 'month':
                case 'default':
                    $title .= ' For ' .$fromDate[0]. ' to '.$toDate[0];break;
            }
      } 
         else{
            $dt = explode(' ', Util::returnDateFormatted($dur, "Y-m-d", "d-m-Y", "-"));;
            $title .= ' For '.$dt[0];
              
      }
        
        return $title;
        
    }
    

    // Paytronics customer registration report
        public function customerregistrationAction(){
         $this->title = 'Customer Registration Report'; 
         $user = Zend_Auth::getInstance()->getIdentity();
         $userModel = new AgentUser();
         $agentProduct = $userModel->getAgentBinding($user->id);
         $pursemaster = new MasterPurse();
                 // Get our form and validate it
         $form = new Corp_Ratnakar_CustomerRegistrationForm(array('action' => $this->formatURL('/corp_ratnakar_reports/customerregistration'),
                                                    'method' => 'POST',
                                             )); 
        $bankUnicodeArr = Util::bankUnicodesArray();
        $page = $this->_getParam('page');
        $request = $this->_getAllParams();
        $sub = $this->_getParam('sub');
        $bankRat = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR);
        $bankRatUnicode = $bankRat->bank->unicode;
        $qurStr['bank_unicode'] = $bankRatUnicode;
        
        $qurStr['product_id'] = $agentProduct['product_id'];
        $qurStr['sub'] = $sub;
        $qurStr['to_date']    = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
       
         if($sub!=''){ 
            
              if($form->isValid($qurStr)){ 
                  if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurFrm['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                    $qurFrm['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", 'from');
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", 'to');
                    
                    $objBank = new Banks();
                    $bankInfo = $objBank->getBankbyUnicode($qurStr['bank_unicode']);
                    $this->view->title = 'Customer Registration Report '.$bankInfo->name.' for '.Util::returnDateFormatted($qurFrm['from'], "Y-m-d", "d-m-Y", "-");
                    $this->view->title .= ' to '.Util::returnDateFormatted($qurFrm['to'], "Y-m-d", "d-m-Y", "-");
                    $this->view->from = $qurFrm['from'];
                    $this->view->to   = $qurFrm['to'];
                }
                 
                 $qurData['product_id'] = $qurStr['product_id'];
                 $qurData['agent_id'] = $user->id;
                 $qurData['bank_unicode'] = $qurStr['bank_unicode'];
                 $objReports = new Reports();
                 $cardholders = $objReports->getCardholders($qurData ,$dateCreated = TRUE);
                 $this->view->paginator = $objReports->paginateByArray($cardholders, $page, $paginate = NULL);
                 $this->view->formData = $qurStr;
              }   
             
          }
            $this->view->form = $form;
            $this->view->user_type = $user->user_type;
            
    }
    /* exportcustomerregistrationAction function is responsible to create the csv file on fly with agent fund requests report data
     * and let user download that file.
     */
    
     public function exportcustomerregistrationAction(){
        $user = Zend_Auth::getInstance()->getIdentity();
         // Get our form and validate it
         $form = new Corp_Ratnakar_CustomerRegistrationForm(array('action' => $this->formatURL('/corp_ratnakar_reports/customerregistration'),
                                                    'method' => 'POST',
                                             )); 
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $qurStr['product_id'] = $this->_getParam('product_id');
        $qurStr['agent_id'] = $this->_getParam('agent_id');
        $qurStr['status'] = $this->_getParam('status');
        
         if($qurStr['dur']!=''|| ($qurStr['to_date'] !='' && $qurStr['from_date']!='')){ 
             
              if($form->isValid($qurStr)){ 
                  if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", 'from');
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", 'to');
                    
                }
                 $qurData['product_id'] = $qurStr['product_id'];
                 $qurData['bank_unicode'] = $qurStr['bank_unicode'];
                 $qurData['status'] = $qurStr['status'];
                 $qurData['agent_id'] = $user->id;
                 $objReports = new Reports();
                 $exportData = $objReports->exportgetCardholders($qurData,$dateCreated = TRUE);
// column names & indexes
                $columns = array(
                        'Product Name',
                        'Member Id',
                        'Employee Id',
                        'Customer Type',
                        'Card Number',
                        'Card Pack Id',
                        'Title',
                        'First Name',
                        'Middle Name',
                        'Last Name',
                        'Name on Card',
                        'Gender',
                        'Date of Birth',
                        'Mobile',
                        'Mobile2',
                        'Email',
                        'Landline',
                        'Address Line 1',
                        'Address Line 2',
                        'City',
                        'State',
                        'Country',
                        'Pincode',
                        'Mothers Maiden Name',
                        'Employer Name',
                        'Corporate Id',
                        'Corporate Address Line 1',
                        'Corporate Address Line 2',
                        'State',
                        'Pincode',
                        'Address Proof',
                        'Identity Proof',
                        'Is Card Activated',
                        'Activation Date',
                        'Is Card Dispatched',
                        'Card Dispatch Date',
                        'Wallet Code',
                        'Date',
                        'Status',
                        'Failed Date',
                        'Failed Reason',
                    
                );


                $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'customer_registration');exit;
                 }catch (Exception $e) {
                                         App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                         $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                         $this->_redirect($this->formatURL('/corp_ratnakar_reports/customerregistration?sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&product_id='.$qurStr['product_id'].'&bank_unicode='.$qurStr['bank_unicode'].'&status='.$qurStr['status'].'&agent_id='.$qurStr['agent_id'])); 
                                       }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid Paramaters!') );
                         $this->_redirect($this->formatURL('/corp_ratnakar_reports/customerregistration?sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&product_id='.$qurStr['product_id'].'&bank_unicode='.$qurStr['bank_unicode'].'&status='.$qurStr['status'].'&agent_id='.$qurStr['agent_id'])); 
                      }             
          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Invalid duration!') );
                    $this->_redirect($this->formatURL('/corp_ratnakar_reports/customerregistration?sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&product_id='.$qurStr['product_id'].'&bank_unicode='.$qurStr['bank_unicode'].'&status='.$qurStr['status'].'&agent_id='.$qurStr['agent_id'])); 
                 }    
       }
       
       
       
 //Paytronics load report
       
           public function loadreportAction(){
         $this->title = 'Load Report'; 
         $user = Zend_Auth::getInstance()->getIdentity();
         $userModel = new AgentUser();
         $agentProduct = $userModel->getAgentBinding($user->id);
         $productIds = $user->product_ids;
         $prodConstArr = Util::getArrayBykey($productIds, 'product_const');  
        if(in_array(PRODUCT_CONST_RAT_SMP,$prodConstArr)){ 
            $key = array_search(PRODUCT_CONST_RAT_SMP,$prodConstArr);
            $agentProduct['product_id'] = $productIds[$key]['product_id']; 
        }
       
        
        // Get our form and validate it
         $form = new Corp_Ratnakar_LoadReportForm(array('action' => $this->formatURL('/corp_ratnakar_reports/loadreport'),
                                                    'method' => 'POST',
                                             )); 
        $bankUnicodeArr = Util::bankUnicodesArray();
        $page = $this->_getParam('page');
        $request = $this->_getAllParams();
        $sub = $this->_getParam('sub');
        $bankRat = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR);
        $bankRatUnicode = $bankRat->bank->unicode;
        $qurStr['bank_unicode'] = $bankRatUnicode;
        
        
        $qurStr['product_id'] = $agentProduct['product_id'];
        $qurStr['agent_id'] = $this->_getParam('agent_id');
        $qurStr['sub'] = $sub;
        $qurStr['to_date']    = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        
       
      
         if($sub!=''){ 
             
              if($form->isValid($qurStr)){ 
                 if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurFrm['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                    $qurFrm['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", 'from');
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", 'to');
                    
                    $objBank = new Banks();
                    $bankInfo = $objBank->getBankbyUnicode($qurStr['bank_unicode']);
                    $this->view->title = 'Load Report '.$bankInfo->name.' for '.Util::returnDateFormatted($qurFrm['from'], "Y-m-d", "d-m-Y", "-");
                    $this->view->title .= ' to '.Util::returnDateFormatted($qurFrm['to'], "Y-m-d", "d-m-Y", "-");
                    $this->view->from = $qurFrm['from'];
                    $this->view->to   = $qurFrm['to'];
                }
                 $qurData['product_id'] = $qurStr['product_id'];
                 $qurData['bank_unicode'] = $qurStr['bank_unicode'];
                 $qurData['agent_id'] = $user->id;
                 $objReports = new Reports();
                 $cardload = $objReports->getLoadRequests($qurData);
                 $this->view->paginator = $objReports->paginateByArray($cardload, $page, $paginate = NULL);
                 $this->view->formData = $qurStr;
              }   
             
          }
            $this->view->form = $form;
            $this->view->user_type = $user->user_type;
            
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
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $qurStr['product_id'] = $this->_getParam('product_id');
        $qurStr['agent_id'] = $this->_getParam('agent_id');
        
         if($qurStr['to_date'] !='' && $qurStr['from_date']!=''){ 
             
              if($form->isValid($qurStr)){ 
                  if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", 'from');
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", 'to');
                    
                }
                 $qurData['product_id'] = $qurStr['product_id'];
                 $qurData['bank_unicode'] = $qurStr['bank_unicode'];
                 $qurData['agent_id'] = $user->id;
                 $objReports = new Reports();
                 $exportData = $objReports->exportLoadRequests($qurData);
                  
                $bankUnicodeArr = Util::bankUnicodesArray();
                if ($qurStr['bank_unicode'] == $bankUnicodeArr['2']) {
                     $columns = array(
                    'Product Name',
                    'Bank Name',
                    'Txn Identifier Type',
                    'Partner Reference No',
                    'Card Number',
                    'Member Id',
                    'Amount',
                    'Amount Cutoff',
                    'Fee',
                    'Service Tax',     
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
                    'Load Date',
                    'Channel'
                );
                    
                }else{
               $columns = array(
                    'Product Name',
                    'Bank Name',
                    'Txn Identifier Type',
                    'Partner Reference No',
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
                    'Load Date',
                    'Channel'
                );
                }

                //echo '<pre>'; print_r($exportData); exit('testtttttttttttt');
                

                $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'load_report');exit;
                 }catch (Exception $e) {
                                         App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                         $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                         $this->_redirect($this->formatURL('/corp_ratnakar_reports/loadreport?dur='.$qurStr['dur'].'&sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&product_id='.$qurStr['product_id'].'&bank_unicode='.$qurStr['bank_unicode'].'&status='.$qurStr['status'].'&agent_id='.$qurStr['agent_id'])); 
                                       }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid Paramaters!') );
                         $this->_redirect($this->formatURL('/corp_ratnakar_reports/loadreport?dur='.$qurStr['dur'].'&sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&product_id='.$qurStr['product_id'].'&bank_unicode='.$qurStr['bank_unicode'].'&status='.$qurStr['status'].'&agent_id='.$qurStr['agent_id'])); 
                      }             
          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Invalid duration!') );
                    $this->_redirect($this->formatURL('/corp_ratnakar_reports/loadreport?dur='.$qurStr['dur'].'&sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&product_id='.$qurStr['product_id'].'&bank_unicode='.$qurStr['bank_unicode'].'&status='.$qurStr['status'].'&agent_id='.$qurStr['agent_id'])); 
                 } 
                 
                 
                 
                  
                 
                 
       }
       
    
             
 //Paytronics wallet wise transaction report
       
        public function walletwisetransactionreportAction(){
         $this->title = 'Wallet Wise Transaction Report'; 
         $user = Zend_Auth::getInstance()->getIdentity();
         $userModel = new AgentUser();
         $agentProduct = $userModel->getAgentBinding($user->id);
         // Get our form and validate it
         $form = new Corp_Ratnakar_WalletWiseTransactionForm(array('action' => $this->formatURL('/corp_ratnakar_reports/walletwisetransactionreport'),
                                                    'method' => 'POST',
                                             )); 
        $bankUnicodeArr = Util::bankUnicodesArray();
        $page = $this->_getParam('page');
        $sub = $this->_getParam('sub');
        $request = $this->_getAllParams();
        $bankRat = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR);
        $bankRatUnicode = $bankRat->bank->unicode;
        $qurStr['bank_unicode'] = $bankRatUnicode;
         $productIds = $user->product_ids;
         $prodConstArr = Util::getArrayBykey($productIds, 'product_const');  
        if(in_array(PRODUCT_CONST_RAT_SMP,$prodConstArr)){ 
            $key = array_search(PRODUCT_CONST_RAT_SMP,$prodConstArr);
            $agentProduct['product_id'] = $productIds[$key]['product_id']; 
        }
        
        $qurStr['product_id'] = $agentProduct['product_id'];
        $qurStr['sub'] = $sub;
        $qurStr['to_date']    = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $qurStr['wallet_type'] = $this->_getParam('wallet_type');
        $qurStr['bank_unicode'] = $bankRatUnicode;
        $qurStr['agent_id'] = $user->id;
         if($sub!=''){ 
             
              if($form->isValid($qurStr)){ 
                if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurFrm['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                    $qurFrm['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-","-","from");
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-","-","to");
                    
                    $objBank = new Banks();
                    $bankInfo = $objBank->getBankbyUnicode($qurStr['bank_unicode']);
                    $this->view->title = 'Wallet Wise Transaction Report '.$bankInfo->name.' for '.Util::returnDateFormatted($qurFrm['from'], "Y-m-d", "d-m-Y", "-");
                    $this->view->title .= ' to '.Util::returnDateFormatted($qurFrm['to'], "Y-m-d", "d-m-Y", "-");
                    $this->view->from = $qurFrm['from'];
                    $this->view->to   = $qurFrm['to'];
                }
                 $qurData['product_id'] = $qurStr['product_id'];
                 $qurData['bank_unicode'] = $qurStr['bank_unicode'];
                 $qurData['agent_id'] = $qurStr['agent_id'];
                 $qurData['wallet_type'] = $qurStr['wallet_type'];
                 
                 $objReports = new Reports();
                 $cardload = $objReports->getWalletTxn($qurData);
                
                 $this->view->paginator = $objReports->paginateByArray($cardload, $page, $paginate = NULL);
                 $this->view->formData = $qurStr;
              }   
             
          }
            $this->view->form = $form;
            $this->view->user_type = $user->user_type;
            
    }
    /* exportloadreportAction function is responsible to create the csv file on fly with agent fund requests report data
     * and let user download that file.
     */
    
     public function exportwalletwisetransactionreportAction(){
        
         // Get our form and validate it
         $form = new Corp_Ratnakar_WalletWiseTransactionForm(array('action' => $this->formatURL('/corp_ratnakar_reports/walletwisetransaction'),
                                                    'method' => 'POST',
                                             )); 
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $qurStr['product_id'] = $this->_getParam('product_id');
        $qurStr['wallet_type'] = $this->_getParam('wallet_type');
        
         if($qurStr['to_date'] !='' && $qurStr['from_date']!=''){ 
             
              if($form->isValid($qurStr)){ 
                if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", 'from');
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", 'to');
                    
                   
                }
                 $qurData['product_id'] = $qurStr['product_id'];
                 $qurData['bank_unicode'] = $qurStr['bank_unicode'];
                 $qurData['wallet_type'] = $qurStr['wallet_type'];
                 $objReports = new Reports();
                 $exportData = $objReports->exportgetWalletTxn($qurData);
                 
                $bankUnicodeArr = Util::bankUnicodesArray();
                if ($qurStr['bank_unicode'] == $bankUnicodeArr['2']) {
                 $columns = array(
                    'Transaction Date and Time',
		    'Product Name',
		    'Bank Name',
		    'Card Number',
		    'Card Pack Id',
		    'Member ID',
		    'Transaction Type',
		    'Transaction Status',
		    'Wallet Code',
		    'Mode',
		    'Amount',
		    'Transaction Amount',
		    'Fee',
		    'Service Tax',
		    'RRNO',
		    'Acknowledge No.',
		    'Decline Reason',
		    'MCC',
		    'TID',
		    'MID',
		    'Channel',
		    'Reversal flag',
		    'Transaction Narration',
		    'Block Date',
		    'Unblock Date',
                );
                 
                }
                else{
                       $columns = array(
                    'Transaction Date and Time',
                    'Product Name',
                    'Bank Name',
                    'Card Number',
                    'Card Pack Id',
                    'Member ID',
                    'Transaction Type',
                    'Transaction Status',
                    'Wallet A Dr',
                    'Wallet A Cr',
                    'Wallet B Dr',
                    'Wallet B Cr',
                    'Wallet C Dr',
                    'Wallet C Cr',
                    'RRNO',
                    'Acknowledge No.',
                    'Decline Reason',
                    'MCC',
                    'TID',
                    'MID',
                    'Channel',
                    'Reversal flag',
                    'Mode',
                    'Transaction Narration'
                );
                   
                }

                $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'wallet_wise_transaction');exit;
                 }catch (Exception $e) {
                                         App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                         $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                         $this->_redirect($this->formatURL('/corp_ratnakar_reports/walletwisetransactionreport?sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&product_id='.$qurStr['product_id'].'&purse_master_id='.$qurStr['purse_master_id'])); 
                                       }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid Paramaters!') );
                         $this->_redirect($this->formatURL('/corp_ratnakar_reports/walletwisetransactionreport?sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&product_id='.$qurStr['product_id'].'&purse_master_id='.$qurStr['purse_master_id'])); 
                      }             
          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Invalid duration!') );
                    $this->_redirect($this->formatURL('/corp_ratnakar_reports/walletwisetransactionreport?sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&product_id='.$qurStr['product_id'].'&purse_master_id='.$qurStr['purse_master_id'])); 
                 }    
       }
       
   
       
  
       
       
  }