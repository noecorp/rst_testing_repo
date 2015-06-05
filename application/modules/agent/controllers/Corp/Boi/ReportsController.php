<?php
/**
 * Ops can view Reports 
 *
 * @package frontend_controllers
 * @copyright company
 */

class Corp_Boi_ReportsController extends App_Agent_Controller
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
    

    
        public function customerregistrationAction(){
         $this->title = 'Application Status Report'; 
         $user = Zend_Auth::getInstance()->getIdentity();
         
         // Get our form and validate it
         $form = new Corp_Boi_CustomerRegistrationForm(array('action' => $this->formatURL('/corp_boi_reports/customerregistration'),
                                                    'method' => 'POST',
                                             )); 
        $bankUnicodeArr = Util::bankUnicodesArray();
        $page = $this->_getParam('page');
        $request = $this->_getAllParams();
        $sub = $this->_getParam('sub');
        $bankBoi = App_DI_Definition_Bank::getInstance(BANK_BOI);
        $bankBoiUnicode = $bankBoi->bank->unicode;
        $qurStr['bank_unicode'] = $bankBoiUnicode;
        $productNSDC = App_DI_Definition_BankProduct::getInstance(BANK_BOI_NDSC);
        $productNSDCUnicode = $productNSDC->product->unicode;
        $productModel = new Products();
        $productId = $productModel->getProductInfoByUnicode($productNSDCUnicode);
        
        $qurStr['product_id'] = $productId['id'];
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['agent_id'] = $this->_getParam('agent_id');
        $qurStr['ref_num'] = $this->_getParam('ref_num');
        $qurStr['nsdc_enrollment_no'] = $this->_getParam('nsdc_enrollment_no');
        $qurStr['status'] = $this->_getParam('status');
        $qurStr['ifsc_code'] = $this->_getParam('ifsc_code');
        $qurStr['sub'] = $sub;
        $qurStr['to_date']    = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
      
         if($sub!=''){ 
             
              if($form->isValid($qurStr)){ 
                 if ($qurStr['dur'] != '') {
                 $durationArr = Util::getDurationDates($qurStr['dur']);
                 $qurData['from'] = $durationArr['from'];
                 $qurData['to'] = $durationArr['to'];
                 $fromDate = explode(' ', Util::returnDateFormatted($durationArr['from'], "d-m-Y", "Y-m-d", "-"));
                 $toDate = explode(' ', Util::returnDateFormatted($durationArr['to'], "d-m-Y", "Y-m-d", "-"));
                 $this->view->from = $fromDate[0];
                 $this->view->to   = $toDate[0];                 
                 $this->view->title = $this->getReportTitle('Application Status Report', $qurStr['dur'],0,FALSE,$qurStr['bank_unicode']);
                 
                } else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurFrm['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                    $qurFrm['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", 'from');
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", 'to');
                    
                    $objBank = new Banks();
                    $bankInfo = $objBank->getBankbyUnicode($qurStr['bank_unicode']);
                    $this->view->title = 'Application Status Report '.$bankInfo->name.' for '.Util::returnDateFormatted($qurFrm['from'], "Y-m-d", "d-m-Y", "-");
                    $this->view->title .= ' to '.Util::returnDateFormatted($qurFrm['to'], "Y-m-d", "d-m-Y", "-");
                    $this->view->from = $qurFrm['from'];
                    $this->view->to   = $qurFrm['to'];
                }
                 $qurData['product_id'] = $qurStr['product_id'];
                 $qurData['bank_unicode'] = $qurStr['bank_unicode'];
                 $qurData['agent_id'] = $qurStr['agent_id'];
                 $qurData['ref_num'] = $qurStr['ref_num'];
                 $qurData['ifsc_code'] = $qurStr['ifsc_code'];
                 $qurData['nsdc_enrollment_no'] = $qurStr['nsdc_enrollment_no'];
                 $qurData['status'] = $qurStr['status'];
                 $objReports = new Reports();
                 $cardholders = $objReports->getCardholders($qurData);
                 $this->view->paginator = $objReports->paginateByArray($cardholders, $page, $paginate = NULL);
                 $this->view->formData = $qurStr;
              }   
              $form->populate($qurStr);
          }
           
            $this->view->form = $form;
            $this->view->user_type = $user->user_type;
            
    }
    /* exportcustomerregistrationAction function is responsible to create the csv file on fly with agent fund requests report data
     * and let user download that file.
     */
    
     public function exportcustomerregistrationAction(){
        
         // Get our form and validate it
         $form = new Corp_Boi_CustomerRegistrationForm(array('action' => $this->formatURL('/corp_boi_reports/customerregistration'),
                                                    'method' => 'POST',
                                             )); 
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $qurStr['product_id'] = $this->_getParam('product_id');
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['agent_id'] = $this->_getParam('agent_id');
        $qurStr['ref_num'] = $this->_getParam('ref_num');
        $qurStr['nsdc_enrollment_no'] = $this->_getParam('nsdc_enrollment_no');
        $qurStr['status'] = $this->_getParam('status');
        
         if($qurStr['dur']!=''|| ($qurStr['to_date'] !='' && $qurStr['from_date']!='')){ 
             
              if($form->isValid($qurStr)){ 
                  if ($qurStr['dur'] != '') {
                 $durationArr = Util::getDurationDates($qurStr['dur']);
                 $qurData['from'] = $durationArr['from'];
                 $qurData['to'] = $durationArr['to'];
                 
                } else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
//                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
//                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                    
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", 'from');
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", 'to');
                   
                }
                 $qurData['product_id'] = $qurStr['product_id'];
                 $qurData['bank_unicode'] = $qurStr['bank_unicode'];
                 $qurData['ref_num'] = $qurStr['ref_num'];
                 $qurData['nsdc_enrollment_no'] = $qurStr['nsdc_enrollment_no'];
                 $qurData['status'] = $qurStr['status'];
                 $qurData['agent_id'] = $qurStr['agent_id'];
                 $objReports = new Reports();
                 $exportData = $objReports->exportgetCardholders($qurData);
// column names & indexes
                $columns = array(
                    'Product Name',
                    'AOF Reference Number',
                    'Name (of the Trainee)',
                    'Aadhaar Number',
                    'NSDC Enrollment Number',
                    'Sol ID',
                    'Checker Status',
                    'Authorizer Status',
                    'Account No.',
                    'IFSC Code',
                    'Card No.',
                    'Traning Center BC Name',
                    'Debit Mandate Amount',
                    'Training Center ID',
                    'Traning Center Name',
                    'Training Partner Name',
                    'Application Date',
                    
                );


                $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'nsdc_cardholder_registration');exit;
                 }catch (Exception $e) {
                                         App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                         $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                         $this->_redirect($this->formatURL('/corp_boi_reports/customerregistration?dur='.$qurStr['dur'].'&sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&product_id='.$qurStr['product_id'].'&bank_unicode='.$qurStr['bank_unicode'].'&status='.$qurStr['status'].'&ref_num='.$qurStr['ref_num'].'&agent_id='.$qurStr['agent_id'].'&nsdc_enrollment_no='.$qurStr['nsdc_enrollment_no'])); 
                                       }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid Paramaters!') );
                         $this->_redirect($this->formatURL('/corp_boi_reports/customerregistration?dur='.$qurStr['dur'].'&sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&product_id='.$qurStr['product_id'].'&bank_unicode='.$qurStr['bank_unicode'].'&status='.$qurStr['status'].'&ref_num='.$qurStr['ref_num'].'&agent_id='.$qurStr['agent_id'].'&nsdc_enrollment_no='.$qurStr['nsdc_enrollment_no'])); 
                      }             
          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Invalid duration!') );
                    $this->_redirect($this->formatURL('/corp_boi_reports/customerregistration?dur='.$qurStr['dur'].'&sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&product_id='.$qurStr['product_id'].'&bank_unicode='.$qurStr['bank_unicode'].'&status='.$qurStr['status'].'&ref_num='.$qurStr['ref_num'].'&agent_id='.$qurStr['agent_id'].'&nsdc_enrollment_no='.$qurStr['nsdc_enrollment_no'])); 
                 }    
       }
       
      public function consolidatedreportAction(){
         $this->title = 'Consolidated Report';              
         // Get our form and validate it
         $form = new Corp_Boi_ConsolidatedReportForm(array('action' => $this->formatURL('/corp_boi_reports/consolidatedreport'),
                                                    'method' => 'POST',
                                             )); 
        
        $bankUnicodeArr = Util::bankUnicodesArray();
        $page = $this->_getParam('page');
        $request = $this->_getAllParams();
        $sub = $this->_getParam('sub');
        $bankBoi = App_DI_Definition_Bank::getInstance(BANK_BOI);
        $bankBoiUnicode = $bankBoi->bank->unicode;
        $qurStr['bank_unicode'] = $bankBoiUnicode;
        $productNSDC = App_DI_Definition_BankProduct::getInstance(BANK_BOI_NDSC);
        $productNSDCUnicode = $productNSDC->product->unicode;
        $productModel = new Products();
        $productId = $productModel->getProductInfoByUnicode($productNSDCUnicode);
        
        
        $qurStr['product_id'] = $productId['id'];
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['agent_id'] = $this->_getParam('agent_id');
//        $qurStr['date_approval'] = $this->_getParam('date_approval');
        $qurStr['ref_num'] = $this->_getParam('ref_num');
        $qurStr['account_no'] = $this->_getParam('account_no');
        $qurStr['aadhaar_no'] = $this->_getParam('aadhaar_no');
        $qurStr['nsdc_enrollment_no'] = $this->_getParam('nsdc_enrollment_no');
        $qurStr['status'] = $this->_getParam('status');
//        $qurStr['wallet_load_status'] = $this->_getParam('wallet_load_status');
        $qurStr['sub'] = $sub;
        $qurStr['to_date']    = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
      
         if($sub!=''){ 
              if($form->isValid($qurStr)){ 
                
                 $objBank = new Banks();
                 $bankInfo = $objBank->getBankbyUnicode($qurStr['bank_unicode']);
                 if ($qurStr['dur'] != '') {
                 $durationArr = Util::getDurationDates($qurStr['dur']);
                 $qurData['from'] = $durationArr['from'];
                 $qurData['to'] = $durationArr['to'];
                 $fromDate = explode(' ', Util::returnDateFormatted($durationArr['from'], "d-m-Y", "Y-m-d", "-"));
                 $toDate = explode(' ', Util::returnDateFormatted($durationArr['to'], "d-m-Y", "Y-m-d", "-"));
                 $this->view->from = $fromDate[0];
                 $this->view->to   = $toDate[0];                 
                 $this->view->title = $this->getReportTitle('Consolidated Application Status Report', $qurStr['dur'],0,FALSE,$qurStr['bank_unicode']);
                 
                } else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurFrm['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                    $qurFrm['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", 'from');
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", 'to');
                    
                    
                    $this->view->title = 'Consolidated Report '.$bankInfo->name.' for '.Util::returnDateFormatted($qurFrm['from'], "Y-m-d", "d-m-Y", "-");
                    $this->view->title .= ' to '.Util::returnDateFormatted($qurFrm['to'], "Y-m-d", "d-m-Y", "-");
                    $this->view->from = $qurFrm['from'];
                    $this->view->to   = $qurFrm['to'];
                }
                else{
                    $this->view->title = 'Consolidated Report of '.$bankInfo->name;
                    
                }
                 $qurData['product_id'] = $qurStr['product_id'];
                 $qurData['bank_unicode'] = $qurStr['bank_unicode'];
//                 $qurData['date_approval'] = Util::returnDateFormatted($qurStr['date_approval'], "d-m-Y", "Y-m-d", "-");
                 $qurData['ref_num'] = $qurStr['ref_num'];
                 $qurData['account_no'] = $qurStr['account_no'];
                 $qurData['aadhaar_no'] = $qurStr['aadhaar_no'];
                 $qurData['nsdc_enrollment_no'] = $qurStr['nsdc_enrollment_no'];
                 $qurData['status'] = $qurStr['status'];
//                 $qurData['wallet_load_status'] = $qurStr['wallet_load_status'];
                 $qurData['agent_id'] = $qurStr['agent_id'];
                 $objReports = new Corp_Boi_Customers();
                 $cardholders = $objReports->getConsolidatedDetails($qurData);
                 
                 $this->view->paginator = $objReports->paginateByArray($cardholders, $page, $paginate = NULL);
                 $this->view->formData = $qurStr;
              }   
             
          }
            $this->view->form = $form;
            
    }
    /* exportcustomerregistrationAction function is responsible to create the csv file on fly with agent fund requests report data
     * and let user download that file.
     */
   
    public function exportconsolidatedreportAction(){
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();
         // Get our form and validate it
         $form = new Corp_Boi_ConsolidatedReportForm(array('action' => $this->formatURL('/corp_boi_reports/exportconsolidatedreport'),
                                                    'method' => 'POST',
                                             )); 
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['agent_id'] = $this->_getParam('agent_id');
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $qurStr['product_id'] = $this->_getParam('product_id');
        $qurStr['account_no'] = $this->_getParam('account_no');
        $qurStr['aadhaar_no'] = $this->_getParam('aadhaar_no');
        $qurStr['ref_num'] = $this->_getParam('ref_num');
        $qurStr['nsdc_enrollment_no'] = $this->_getParam('nsdc_enrollment_no');
        $qurStr['status'] = $this->_getParam('status');
//        $qurStr['wallet_load_status'] = $this->_getParam('wallet_load_status');
//        $qurStr['date_approval'] = $this->_getParam('date_approval');
//         if($qurStr['dur']!=''|| ($qurStr['to_date'] !='' && $qurStr['from_date']!='') || $qurStr['date_approval']!=''){ 
             
              if($form->isValid($qurStr)){ 
                  if ($qurStr['dur'] != '') {
                 $durationArr = Util::getDurationDates($qurStr['dur']);
                 $qurData['from'] = $durationArr['from'];
                 $qurData['to'] = $durationArr['to'];
                 
                } else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", 'from');
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", 'to');
                }
//                else{
//                    $qurData['date_approval'] = Util::returnDateFormatted($qurStr['date_approval'], "d-m-Y", "Y-m-d", "-"); 
//                }
                 $qurData['account_no'] = $qurStr['account_no'];
                 $qurData['aadhaar_no'] = $qurStr['aadhaar_no'];
                 $qurData['product_id'] = $qurStr['product_id'];
                 $qurData['bank_unicode'] = $qurStr['bank_unicode'];
                 $qurData['ref_num'] = $qurStr['ref_num'];
                 $qurData['nsdc_enrollment_no'] = $qurStr['nsdc_enrollment_no'];
                 $qurData['status'] = $qurStr['status'];
//                 $qurData['wallet_load_status'] = $qurStr['wallet_load_status'];
                 $qurData['agent_id'] = $qurStr['agent_id'];
                 $objReports = new Corp_Boi_Customers();
                 $exportData = $objReports->getConsolidatedDetails($qurData);
// column names & indexes
                $columns = array(
                    'AOF Reference Number',
                    'Application Date',
                    'Name (of the Trainee)',
                    'NSDC Enrollment Number',
                    'Aadhaar No.',
                    'Linked Branch ID',
                    'Transerv Status',
                    'Bank Status',
                    'Account No.',
                    'IFSC Code',
                    'Card No.',
                    'Debit Mandate Amount',
                    'NSDC Wallet Load Date',
                    'NSDC Load Amount',
//                    'Wallet Balance as on End Date',
                    'Available Balance on Wallet',
                    'Amount debited through POS',
                    'Wallet Auto Debit Date',
                    'Wallet Auto Debit Amount',
                    'Traning Center BC Name',
                    'Training Center ID',
                    'Training Center Name',
                    'Training Partner Name',
//                    'Current Balance on Wallet',
                );


                $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'consolidated_report');exit;
                 }catch (Exception $e) {
                                         App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                         $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                         $this->_redirect($this->formatURL('/corp_boi_reports/exportconsolidatedreport?dur='.$qurStr['dur'].'&sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&product_id='.$qurStr['product_id'].'&bank_unicode='.$qurStr['bank_unicode'].'&status='.$qurStr['status'].'&ref_num='.$qurStr['ref_num'].'&agent_id='.$qurStr['agent_id'].'&nsdc_enrollment_no='.$qurStr['nsdc_enrollment_no'].'&account_no='.$qurStr['account_no'].'&aadhaar_no='.$qurStr['aadhaar_no'])); 
                                       }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid Paramaters!') );
                         $this->_redirect($this->formatURL('/corp_boi_reports/exportconsolidatedreport?dur='.$qurStr['dur'].'&sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&product_id='.$qurStr['product_id'].'&bank_unicode='.$qurStr['bank_unicode'].'&status='.$qurStr['status'].'&ref_num='.$qurStr['ref_num'].'&agent_id='.$qurStr['agent_id'].'&nsdc_enrollment_no='.$qurStr['nsdc_enrollment_no'].'&account_no='.$qurStr['account_no'].'&aadhaar_no='.$qurStr['aadhaar_no'])); 
                      }             
//          } else {
//                    $this->_helper->FlashMessenger( array('msg-error' => 'Invalid duration!') );
//                    $this->_redirect($this->formatURL('/corp_boi_reports/exportconsolidatedreport?dur='.$qurStr['dur'].'&sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&product_id='.$qurStr['product_id'].'&bank_unicode='.$qurStr['bank_unicode'].'&status='.$qurStr['status'].'&ref_num='.$qurStr['ref_num'].'&agent_id='.$qurStr['agent_id'].'&nsdc_enrollment_no='.$qurStr['nsdc_enrollment_no'].'&date_approval='.$qurStr['date_approval'].'&aadhaar_no='.$qurStr['aadhaar_no'].'&wallet_load_status='.$qurStr['wallet_load_status'])); 
//                 }    
       }
         
    
    public function tpmisreportAction(){
        
        $user = Zend_Auth::getInstance()->getIdentity();
       
        $this->title = 'Payment Status Report';              
         // Get our form and validate it
        $form = new Corp_Boi_TpmisReportForm(array('action' => $this->formatURL('/corp_boi_reports/tpmisreport'),
                                                    'method' => 'POST',
                                             )); 
        
        $bankUnicodeArr = Util::bankUnicodesArray();
        $page = $this->_getParam('page');
        $request = $this->_getAllParams();
        $sub = $this->_getParam('sub');
        $bankBoi = App_DI_Definition_Bank::getInstance(BANK_BOI);
        $bankBoiUnicode = $bankBoi->bank->unicode;
        $qurStr['bank_unicode'] = $bankBoiUnicode;
        $productNSDC = App_DI_Definition_BankProduct::getInstance(BANK_BOI_NDSC);
        $productNSDCUnicode = $productNSDC->product->unicode;
        $productModel = new Products();
        $productId = $productModel->getProductInfoByUnicode($productNSDCUnicode);
        $tpName = $user->first_name.' '.$user->last_name;
        $qurStr['product_id'] = $productId['id'];
        $qurStr['agent_code'] = $this->_getParam('agent_code');
        $qurStr['ref_num'] = $this->_getParam('ref_num');
        $qurStr['aadhaar_no'] = $this->_getParam('aadhaar_no');
        $qurStr['account_no'] = $this->_getParam('account_no');
        $qurStr['sub'] = $sub;
        $qurStr['tp_name'] = $tpName;
        $qurStr['to_date'] = $this->_getParam('to_date');
        $qurStr['from_date'] = $this->_getParam('from_date');
        $pos = TRUE;
        $form->getElement('tp_name')->setValue($tpName);
      
         if($sub!=''){ 
              if($form->isValid($qurStr)){ 
                
                
                 $this->view->title = 'Payment Status Report';
                 
                 $agentModel = new Agents();
                 $agentId = $agentModel->findagentByAgentCode($qurStr['agent_code']);
                 
                 $checkBC = $agentModel->getBCListing(
                 array('status' => STATUS_UNBLOCKED, 'enroll_status' => STATUS_APPROVED, 'user_id' => $user->id, 'user_type' => $user->user_type, 'ret_type' => 'arr'));
                
                if($qurStr['agent_code'] != '' && is_array($checkBC)){
                $pos = in_array($agentId['id'], $checkBC);
              }
                if ($pos === false) {
                    $this->_helper->FlashMessenger(
                            array(
                                'msg-error' => 'Please enter valid BC code',
                            )
                    );
                 } else {
                  
                 if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurFrm['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                    $qurFrm['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", 'from');
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", 'to');
                    
                    
                    $this->view->title = 'Payment Status Report '.$bankInfo->name.' for '.Util::returnDateFormatted($qurFrm['from'], "Y-m-d", "d-m-Y", "-");
                    $this->view->title .= ' to '.Util::returnDateFormatted($qurFrm['to'], "Y-m-d", "d-m-Y", "-");  
                 
                 }
                 $qurData['tp_id'] = $user->id;
                 $qurData['agent_code'] = $qurStr['agent_code'];
                 $qurData['aadhaar_no'] = $qurStr['aadhaar_no'];
                 $qurData['account_no'] = $qurStr['account_no'];
                 $qurData['product_id'] = $qurStr['product_id'];
                 $objReports = new Corp_Boi_Customers();
                 $cardholders = $objReports->getTPMisDetails($qurData);
                 
                 $this->view->paginator = $objReports->paginateByArray($cardholders, $page, $paginate = NULL);
                 $this->view->formData = $qurStr;
                 }
                 $form->populate($qurStr);
                 
              }   
             $form->populate($qurStr);
          }
            $this->view->form = $form;
            
    }
     public function exporttpmisreportAction(){
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();
        $user = Zend_Auth::getInstance()->getIdentity();
         // Get our form and validate it
         $form = new Corp_Boi_TpmisReportForm(array('action' => $this->formatURL('/corp_boi_reports/exporttpmisreport'),
                                                    'method' => 'POST',
                                             )); 
        $qurStr['product_id'] = $this->_getParam('product_id');
        $qurStr['aadhaar_no'] = $this->_getParam('aadhaar_no');
        $qurStr['account_no'] = $this->_getParam('account_no');
        $qurStr['to_date'] = $this->_getParam('to_date');
        $qurStr['from_date'] = $this->_getParam('from_date');
        $qurStr['agent_code'] = $this->_getParam('agent_code');
              if($form->isValid($qurStr)){ 
                 if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", 'from');
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", 'to');
                }
                 $qurData['tp_id'] = $user->id;
                 $qurData['aadhaar_no'] = $qurStr['aadhaar_no'];
                 $qurData['account_no'] = $qurStr['account_no'];
                 $qurData['product_id'] = $qurStr['product_id'];
                 $qurData['agent_code'] = $qurStr['agent_code'];
                 $objReports = new Corp_Boi_Customers();
                 $exportData = $objReports->getTPMisDetails($qurData);
                // column names & indexes
                $columns = array(
                    'AOF Reference Number',
                    'Application Date',
                    'Name (of the Trainee)',
                    'NSDC Enrollment Number',
                    'Aadhaar No.',
                    'Linked Branch ID',
                    'Transerv Status',
                    'Bank Status',
                    'Account No.',
                    'IFSC Code',
                    'Card No.',
                    'Debit Mandate Amount',
                    'NSDC Wallet Load Date',
                    'NSDC Load Amount',
                    'Available Balance on Wallet',
                    'Amount debited through POS',
                    'Wallet Auto Debit Date',
                    'Wallet Auto Debit Amount',
                    'Traning Center BC Name',
                    'Training Center ID',
                    'Training Center Name',
                    'Training Partner Name',
                );


                $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'payment_status_report');exit;
                 }catch (Exception $e) {
                                         App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                         $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                         $this->_redirect($this->formatURL('/corp_boi_reports/exporttpmisreport?sub=1&product_id='.$qurStr['product_id'].'&bank_unicode='.$qurStr['bank_unicode'].'&aadhaar_no='.$qurStr['aadhaar_no'].'&agent_code='.$qurStr['agent_code'].'&account_no='.$qurStr['account_no'])); 
                                       }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid Paramaters!') );
                         $this->_redirect($this->formatURL('/corp_boi_reports/exporttpmisreport?sub=1&product_id='.$qurStr['product_id'].'&bank_unicode='.$qurStr['bank_unicode'].'&aadhaar_no='.$qurStr['aadhaar_no'].'&agent_code='.$qurStr['agent_code'].'&account_no='.$qurStr['account_no'])); 
                      }             
     
       }
       
  }
