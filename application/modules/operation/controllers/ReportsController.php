<?php
/**
 * Ops can view Reports 
 *
 * @package frontend_controllers
 * @copyright company
 */

class ReportsController extends App_Operation_Controller
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
    


    public function agentfundrequestsAction(){
         $this->title = 'Agent Authorized Funding Report';              
         // Get our form and validate it
         $form = new RptAgentFundRequestsForm(array('action' => $this->formatURL('/reports/agentfundrequests'),
                                                    'method' => 'POST',
                                             )); 
  
        $request = $this->_getAllParams();
        $sub = $this->_getParam('sub');
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['sub'] = $sub;
        $qurStr['to_date']  = $this->_getParam('to_date');
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
                 $this->view->title = $this->getReportTitle('Agent Authorized Funding Report', $qurStr['dur']);
                 
                } else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                    $this->view->title = 'Agent Authorized Funding Report for '.Util::returnDateFormatted($qurData['from'], "Y-m-d", "d-m-Y", "-");
                    $this->view->title .= ' to '.Util::returnDateFormatted($qurData['to'], "Y-m-d", "d-m-Y", "-");
                    $this->view->from = $qurData['from'];
                    $this->view->to   = $qurData['to'];
                }
                 
                 $objReports = new Reports();
                 $this->view->paginator = $objReports->getAgentFundRequests($qurData, $this->_getPage());
                 $this->view->formData = $qurStr;
              }                  
          }
            $this->view->form = $form;
            
    }
    
    
    
    public function agentwisefundrequestsAction(){
         $this->title = 'Agent Wise Authorized Funding Report';                           
         // Get our form and validate it
         $form = new RptAgentWiseFundRequestsForm(array('action' => $this->formatURL('/reports/agentwisefundrequests'),
                                                    'method' => 'POST',
                                             )); 
  
        $request = $this->_getAllParams();
        $sub = $this->_getParam('sub');
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['id'] = $this->_getParam('id');
        $qurData['agent_id'] = $qurStr['id'];
        $qurStr['sub'] = $sub;
        
        
         if($sub!=''){ 
              $formData  = $this->_request->getPost();
              if($form->isValid($qurStr)){ 
                 $durationArr = Util::getDurationDates($qurStr['dur']);
                 $qurData['from'] = $durationArr['from'];
                 $qurData['to'] = $durationArr['to'];
                 $fromDate = explode(' ', Util::returnDateFormatted($durationArr['from'], "d-m-Y", "Y-m-d", "-"));
                 $toDate = explode(' ', Util::returnDateFormatted($durationArr['to'], "d-m-Y", "Y-m-d", "-"));
                 $this->view->from = $fromDate[0];
                 $this->view->to   = $toDate[0];                 
                 $this->view->title = $this->getReportTitle('Authorized Funding Report', $qurStr['dur'], $qurData['agent_id']);
                 $this->view->id = $qurData['agent_id'] = $qurData['agent_id'];
                 
                 $objReports = new Reports();
                 $this->view->paginator = $objReports->getAgentWiseFundRequests($qurData, $this->_getPage());
                 
              }
                 $this->view->formData = $qurStr;
                 
          }
            $this->view->form = $form;
            
            
    }
    
    /* agentactivationAction function will search agent to cardholder loads and show 
     * the result to ops 
     */
    public function agentactivationAction()
    {    
        $this->title = 'Agent Activation Report';           
         // Get our form and validate it
         $form = new RptAgentActivationForm(array('action' => $this->formatURL('/reports/agentactivation'),
                                                    'method' => 'POST',
                                             )); 
        $citylist = new CityList();
        $request = $this->_getAllParams();
        $qurStr['date_duration'] = $this->_getParam('date_duration');
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['state'] = $this->_getParam('state');
        $qurStr['city'] = $this->_getParam('city');
        $qurStr['sub'] = $this->_getParam('sub');
        $form->getElement('cty')->setValue($qurStr['city']);
        $isError=false;
        
         if($qurStr['sub']!=''){              

              if($form->isValid($qurStr)){ 
                $form->getElement('cty')->setValue($qurStr['city']);
                                 
                 $qurData['state'] = $citylist->getStateName($qurStr['state']);
                 $qurData['city'] = $qurStr['city'];
                 if($qurStr['dur']!='' && $qurStr['date_duration']==''){
                    $dur = explode(' ', Util::returnDateFormatted($qurStr['dur'], "d-m-Y", "Y-m-d", "-"));
                    //$qurData['from'] = $dur[0].' 00:00:00';
                    $qurData['to'] = $dur[0];
                    $singleDateOnly=TRUE;
                    $durationForTitle = $qurStr['dur'];
                 } else if($qurStr['date_duration']!=''){
                    $duration_dates_arr = Util::getDurationDates($qurStr['date_duration']);
                    $fromArr = explode(' ', $duration_dates_arr['from']);
                    $qurData['from'] = $fromArr[0];
                    $toArr = explode(' ', $duration_dates_arr['to']);
                    $qurData['to'] = $toArr[0];
                    $singleDateOnly=FALSE;
                    $durationForTitle = $qurStr['date_duration'];
                 } else {
                     $isError = true;
                     $this->_helper->FlashMessenger( array('msg-error' => 'Please select either duration or date field value',) );
                 }
                 
                 $this->view->state = $qurStr['state'];
                 
                 if(!$isError){
                    $this->view->title = $this->getReportTitle('Agent Activation Report '.$qurStr['city'], $durationForTitle,'',$singleDateOnly);
                    $objReports = new Reports();
                    $this->view->paginator = $objReports->getAgentActivation($qurData, $this->_getPage());
                 }
                
              }                  
          }
             $this->view->form = $form;
             $this->view->formData = $qurStr;
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
    

    /* exportagentfundrequestsAction function is responsible to create the csv file on fly with agent fund requests report data
     * and let user download that file.
     */
    
     public function exportagentfundrequestsAction(){
        
         // Get our form and validate it
         $form = new RptAgentFundRequestsForm(array('action' => $this->formatURL('/reports/agentfundrequests'),
                                                    'method' => 'POST',
                                             )); 
  
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        
         if($qurStr['dur']!=''|| ($qurStr['to_date'] !='' && $qurStr['from_date']!='')){ 
             
              if($form->isValid($qurStr)){ 
                  if ($qurStr['dur'] != '') {
                 $durationArr = Util::getDurationDates($qurStr['dur']);
                 $qurData['from'] = $durationArr['from'];
                 $qurData['to'] = $durationArr['to'];
                 
                } else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                   
                }
                 
                 $objReports = new Reports();
                 $exportData = $objReports->exportAgentFundRequests($qurData);
               
                 $columns = array(
                                    'Transaction Date',
                                    'Agent Code',
                                    'Agent Name',
                                    'Opening Balance (for the day)',
                                    'Total Agent Funding Amount (Authorized)',
                                    'Authorized By',
                                    'Remarks (Agent Comments)',
                                    'Closing Balance (for the day)',   
                                 );
                                  
                 $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'agent_fund_requests');exit;
                 }catch (Exception $e) {
                                         App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                         $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                         $this->_redirect($this->formatURL('/reports/agentfundrequests?dur='.$qurStr['dur'].'&sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'])); 
                                       }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid duration!') );
                         $this->_redirect($this->formatURL('/reports/agentfundrequests?dur='.$qurStr['dur'].'&sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'])); 
                      }             
          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Invalid duration!') );
                    $this->_redirect($this->formatURL('/reports/agentfundrequests?dur='.$qurStr['dur'].'&sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'])); 
                 }    
       }
       
       
       
       
    /* exportagentwisefundrequestsAction function is responsible to create the csv file on fly with agent wise fund requests report data
     * and let user download that file.
     */
    
     public function exportagentwisefundrequestsAction(){
        
        // Get our form and validate it
         $form = new RptAgentWiseFundRequestsForm(array('action' => $this->formatURL('/reports/agentwisefundrequests'),
                                                    'method' => 'POST',
                                             )); 
  
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['id'] = $this->_getParam('id');
        $qurData['agent_id'] = $qurStr['id'];
        
         if($qurStr['dur']!='' && $qurStr['id']>0){ 
             
              if($form->isValid($qurStr)){ 
                 $durationArr = Util::getDurationDates($qurStr['dur']);
                 $qurData['from'] = $durationArr['from'];
                 $qurData['to'] = $durationArr['to'];              
                 
                 $objReports = new Reports();
                 $exportData = $objReports->exportAgentWiseFundRequests($qurData);
                
                 $columns = array(
                                    'Transaction Date',
                                    'Agent Code',
                                    'Agent Name',
                                    'Opening Balance (for the day)',
                                    'Total Agent Funding Amount (Authorized)',
                                    'Authorized By',
                                    'Remarks (Agent Comments)',
                                    'Closing Balance', 
                                 );
                                  
                 $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'agent_wise_fund_requests');exit;
                 } catch (Exception $e) {
                                            App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                            $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                            $this->_redirect($this->formatURL('/reports/agentwiseload?dur='.$qurStr['dur'].'&sub=1&id='.$qurStr['id'])); 
                                        }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid data found') );
                         $this->_redirect($this->formatURL('/reports/agentwiseload?dur='.$qurStr['dur'].'&sub=1&id='.$qurStr['id'])); 
                      }             
          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Data missing') );
                    $this->_redirect($this->formatURL('/reports/agentwiseload?dur='.$qurStr['dur'].'&sub=1&id='.$qurStr['id'])); 
                 }    
       }
       
       
    /* exportagentactivationAction function is responsible to create the csv file on fly with agent activation report data
     * and let user download that file.
     */
    
     public function exportagentactivationAction(){
        
        // Get our form and validate it
         $form = new RptAgentActivationForm(array('action' => $this->formatURL('/reports/agentactivation'),
                                                    'method' => 'POST',
                                             )); 
        $citylist = new CityList();
        $request = $this->_getAllParams();
        $sub = $this->_getParam('sub');
        //$from = explode(' ',$this->_getParam('from'));
        //$to = explode(' ',$this->_getParam('to'));
        $qurStr['date_duration'] = $this->_getParam('date_duration');
        $qurStr['state'] = $this->_getParam('state');
        $qurStr['city'] = $this->_getParam('city');
        
        if($qurStr['date_duration']!='' && $qurStr['state']!=''){ 
             
              if($form->isValid($qurStr)){ 
                 $duration_dates_arr = Util::getDurationDates($qurStr['date_duration']);
                 $fromArr = explode(' ', $duration_dates_arr['from']);
                 $qurData['from'] = $fromArr[0];
                 $toArr = explode(' ', $duration_dates_arr['to']);
                 $qurData['to'] = $toArr[0];
                 $qurData['state'] = $citylist->getStateName($qurStr['state']);    
                 $qurData['city'] = $qurStr['city'];                
                 $objReports = new Reports();
                 
                 $exportData = $objReports->exportAgentActivation($qurData);
                
                 $columns = array(
                                    'Agent Name',
                                    'Agent Code',
                                    'Agent Email',
                                    'Agent Mobile',   
                                    'Agent Address',   
                                    'Agent City',   
                                    'Agent Limit Name',   
                                    'Current Status',
                                    'Application Date',
                                    'Rejected Reason',
                                    'Commission Name',   
                                    'Bank Name'
                                 );
                                  
                 $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'agent_activation');exit;
                 } catch (Exception $e) {
                                            App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                            $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                            $this->_redirect($this->formatURL('/reports/agentactivation?date_duration='.$qurStr['date_duration'].'&sub=1&state='.$qurStr['state'].'&city='.$qurStr['city'])); 
                                        }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid data found') );
                         $this->_redirect($this->formatURL('/reports/agentactivation?date_duration='.$qurStr['date_duration'].'&sub=1&state='.$qurStr['state'].'&city='.$qurStr['city'])); 
                      }             
          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Data missing') );
                    $this->_redirect($this->formatURL('/reports/agentactivation?date_duration='.$qurStr['date_duration'].'&sub=1&state='.$qurStr['state'].'&city='.$qurStr['city'])); 
                 }    
       }
       
       
       /* agentbalancesheetAction function is responsible to generate agent balance report
        * on duration basis
        */
       
        public function agentbalancesheetAction(){
ini_set('max_execution_time', 120);
         $this->title = 'Agent Balance Sheet Report';                         
         // Get our form and validate it
         $form = new RptAgentBalanceSheetForm(array('action' => $this->formatURL('/reports/agentbalancesheet'),
                                                    'method' => 'POST',
                                             )); 
  
        $request = $this->_getAllParams();
        //$sub = $this->_getParam('sub');
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['sub'] = $this->_getParam('sub');
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $page = $this->_getParam('page');
        
        if($qurStr['sub']!=''){
              if($form->isValid($qurStr)){ 
                 if ($qurStr['dur'] != '') {
                 $durationArr = Util::getDurationDates($qurStr['dur']);
                 $qurData['from'] = $durationArr['from'];
                 $qurData['to'] = $durationArr['to'];
                 $fromDate = explode(' ', Util::returnDateFormatted($durationArr['from'], "d-m-Y", "Y-m-d", "-"));
                 $toDate = explode(' ', Util::returnDateFormatted($durationArr['to'], "d-m-Y", "Y-m-d", "-"));
                 $this->view->from = $fromDate[0];
                 $this->view->to   = $toDate[0];                 
                 $this->view->title = $this->getReportTitle('Agent Balance Sheet Report', $qurStr['dur']);
                 $durationDates = Util::getDurationAllDates($qurStr['dur']);
                } else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                    $durationDates = Util::getDurationRangeAllDates($qurData);
                    $this->view->title = 'Agent Balance Sheet Report for '.Util::returnDateFormatted($qurData['from'], "Y-m-d", "d-m-Y", "-");
                    $this->view->title .= ' to '.Util::returnDateFormatted($qurData['to'], "Y-m-d", "d-m-Y", "-");
                    $this->view->from = $qurData['from'];
                    $this->view->to   = $qurData['to'];
                }
                  
                 
                 $objReports = new Reports();
                 $rptData = $objReports->getAgentBalanceSheet($durationDates);
                 $paginator = $objReports->paginateByArray($rptData, $page, $paginate = NULL);
                 $this->view->paginator = $paginator;
                 $this->view->formData = $qurStr;
              } 
        }
         // }
            $this->view->form = $form;
            
    }
    
    
    /* exportagentbalancesheetAction function is responsible to create the csv file on fly with agent balance sheet report data
     * and let user download that file.
     */
    
     public function exportagentbalancesheetAction(){
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $qurStr['dur'] = $this->_getParam('dur');
        $form = new RptAgentBalanceSheetForm(array('action' => $this->formatURL('/reports/agentbalancesheet'),
                                              'method' => 'POST',
                                       )); 
        
         //if($qurStr['dur']!=''){    
             
              if($form->isValid($qurStr)){ 
               if ($qurStr['dur'] != '') {
                $durationDates = Util::getDurationAllDates($qurStr['dur']);
                } else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                     $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                    $durationDates = Util::getDurationRangeAllDates($qurData);
                    
                }
                 
                 $objReports = new Reports();
                 $exportData = $objReports->exportAgentBalanceSheetForOps($durationDates);
                
                        
                 $columns = array(
                                    'Transaction Date',
                                    'Agent/Distributor/Super Distributor Name',
                                    'Agent/Distributor/Super Distributor Code',
                                    'Bank Name',
                                    'Opening Balance',
                                    'Total Agent Funding Amount (Authorized)',
                                    'Unauthorized Fund Request Amount',
                                    'Total Transaction Amount', 
                                    'Total Refund Transaction Amount', 
                                    'Total Fee', 
                                    'Total Service Tax', 
                                    'Total Service Tax Reversal', 
                                    'Total Fee Reversal', 
                                    'Debits to Payable A/c',
                                    'Debit Reversals',
					'Commission',
                 			'Commission Reversal',
                                    'Closing Balance',
                                 );
                                  
                 $objCSV = new CSV();
                 try{
                     
                        $resp = $objCSV->export($exportData, $columns, 'agent_balance_sheet');exit;
                 }catch (Exception $e) {
                                         App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                         $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                         $this->_redirect($this->formatURL('/reports/agentbalancesheet?sub=1&dur='.$qurStr['dur'].'&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'])); 
                                       }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid duration!') );
                         $this->_redirect($this->formatURL('/reports/agentbalancesheet?sub=1&dur='.$qurStr['dur'].'&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'])); 
                      }             
    
       }
   
       
     /*
     * Agent wise Consolidated Fee report for
     */
    public function agentwisefeereportAction(){
        $this->title = 'Agent Wise Fee Report';           
         // Get our form and validate it
        $form = new AgentwiseFeeReportForm(array('action' => $this->formatURL('/reports/agentwisefeereport'),
                                                    'method' => 'POST',
                                         )); 
        $qurStr['duration'] = $this->_getParam('duration');
        $qurStr['agent_id'] = $this->_getParam('agent_id');
        $qurStr['btn_submit'] = $this->_getParam('btn_submit');
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        $qurStr['submit'] = $this->_getParam('submit');
        $page = $this->_getParam('page');
        
         
         if($qurStr['btn_submit']){             
              if($form->isValid($qurStr)){ 
                 
                 $pageTitle = $this->getReportTitle('Fee Report', $qurStr['duration'], $qurStr['agent_id'],FALSE,$qurStr['bank_unicode']);
                 
                 $durationArr = Util::getDurationDates($qurStr['duration']);
                 $qurData['from'] = $durationArr['from'];
                 $qurData['to'] = $durationArr['to'];  
                 $qurData['agent_id'] = $qurStr['agent_id'];  
                 $qurData['bank_unicode'] =  $qurStr['bank_unicode'];   
                 $qurData['check_fee'] = FALSE;
                 $objFeeReport = new FeePlan();
                
                 $feeArr = $objFeeReport->getAgentWiseFeeArray($qurData);
                 $paginator = $objFeeReport->paginateByArray($feeArr, $page, $paginate = NULL);
                 $form->getElement('id')->setValue($qurData['agent_id']);
                 $this->view->paginator = $paginator;
                 $this->view->pageTitle = $pageTitle;
                 $this->view->btnSubmit = $qurStr['btn_submit'];
               }   
               
          }
            $this->view->form = $form;
            $this->view->formData = $qurStr; 
            
    }
       
       
    
     /*
     * Consolidated Fee report for all agents
     */
    public function feereportAction(){
    ini_set("memory_limit","400M");
    set_time_limit(300);
        $this->title = 'Fee Report';           
         // Get our form and validate it
        $form = new FeeReportForm(array('action' => $this->formatURL('/reports/feereport'),
                                                    'method' => 'POST',
                                         )); 
        $qurStr['duration'] = $this->_getParam('duration');
        $qurStr['btn_submit'] = $this->_getParam('btn_submit');
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $qurStr['submit'] = $this->_getParam('submit');
        $page = $this->_getParam('page');
        
         
        if($qurStr['btn_submit']){             
            if($form->isValid($qurStr)){ 
                if ($qurStr['duration'] != '') {
                    $pageTitle = $this->getReportTitle('Fee Report', $qurStr['duration'],0,FALSE,$qurStr['bank_unicode']); 
                    $durationArr = Util::getDurationDates($qurStr['duration']);
                    $qurData['to'] = $durationArr['to'];
                    $qurData['from'] = $durationArr['from']; 
                } else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') { 
                    $objBank = new Banks();
                    $bankInfo = $objBank->getBankbyUnicode($qurStr['bank_unicode']); 
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", "to");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", "from");
                    $pageTitle  = 'Fee Report of '. $bankInfo->name;
                    $pageTitle .= ' for '. $qurStr['from_date'] .' to '. $qurStr['to_date'] ;
                }
                
                $qurData['check_fee'] = FALSE;
                $qurData['bank_unicode'] =  $qurStr['bank_unicode']; 
                $objFeeReport = new FeePlan();
                $feeArr = $objFeeReport->getAgentFee($qurData);

                $paginator = $objFeeReport->paginateByArray($feeArr, $page, $paginate = NULL);
                $this->view->paginator = $paginator;
                $this->view->pageTitle = $pageTitle;
                $this->view->btnSubmit = $qurStr['btn_submit'];
            }   
               
        }
        $this->view->form = $form;
        $this->view->formData = $qurStr;     
    }
    
    
    
    /* exportfeereportAction() is responsible to create the csv file on fly with fee data of agents
     * and let user download that file.
     */
    
     public function exportfeereportAction(){
ini_set("memory_limit","400M");
set_time_limit(300);
        $qurStr['duration'] = $this->_getParam('duration');
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $form = new FeeReportForm(array('action' => $this->formatURL('/reports/feereport'),
                                              'method' => 'POST',
                                       )); 
        
         if($qurStr['duration']!='' || $qurStr['to_date'] !='' && $qurStr['from_date']!=''){    
            
              if($form->isValid($qurStr)){ 
                  if($qurStr['duration']!=''){
                 $durationArr = Util::getDurationDates($qurStr['duration']);
                 $qurData['from'] = $durationArr['from'];
                 $qurData['to'] = $durationArr['to']; 
              }else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", "to");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", "from");
                   
                }

                 
                 $qurData['check_fee'] = FALSE;
                 $qurData['bank_unicode'] =  $qurStr['bank_unicode'];   
                 $objFeeReport = new FeePlan();
                 $exportData = $objFeeReport->exportAgentFee($qurData);
               
                 $columns = array(
                                    'Date',
                                    'Super Distributor Code',
                                    'Super Distributor Name',
                                    'Distributor Code',
                                    'Distributor Name',
                                    'Agent Code',
                                    'Agent Name',
                                    'Agent City',
                                    'Agent Pincode', 
                                    'Transaction Ref Number',
                                    'Reversal/Refund Transaction Reference Number',
                                    'Transaction Narration',
                                    'Transaction Amount',
                                    'Fee',
                                    'Service Tax',
                                    'Reversal Fee',
                                    'Reversal Service Tax',
                                    'UTR No',
                                    'Transaction Status',
                                 );
                                  
                 $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'fee');exit;
                 }catch (Exception $e) {
                                         App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                         $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                         $this->_redirect($this->formatURL('/reports/feereport?duration='.$qurStr['duration'].'&sub=1&bank_unicode='.$qurStr['bank_unicode'].'&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'])); 
                                       }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid data') );
                         $this->_redirect($this->formatURL('/reports/feereport?duration='.$qurStr['duration'].'&sub=1&bank_unicode='.$qurStr['bank_unicode'].'&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'])); 
                      }             
          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Invalid duration') );
                    $this->_redirect($this->formatURL('/reports/feereport?duration='.$qurStr['duration'].'&sub=1&bank_unicode='.$qurStr['bank_unicode'].'&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'])); 
                 }    
       }
       
    
     /* Agent Transaction Report: load / reload / all remittance txns report for all agents
     * takes duration as argument, currently yesterday / today / WTD / MTD
     */
    public function agenttransactionAction(){
                
       $this->title = 'Agent Transaction Summary Report';  
       // Get our form and validate it
        $form = new AgentTransactionForm(); 
        $request = $this->getRequest();  
        $sub = $this->_getParam('sub');
        $qurStr['dur'] = $this->_getParam('dur');
//        $qurStr['sub'] = $sub;
                 
         if($sub!=''){    
             
              if($form->isValid($qurStr)){ 
                 $durationArr = Util::getDurationDates($qurStr['dur']);
                 
                 $qurData['duration'] = $qurStr['dur'];
                 $fromDate = explode(' ', Util::returnDateFormatted($durationArr['from'], "d-m-Y", "Y-m-d", "-"));
                 $toDate = explode(' ', Util::returnDateFormatted($durationArr['to'], "d-m-Y", "Y-m-d", "-"));
                 $this->view->from = $fromDate[0];
                 $this->view->to   = $toDate[0];                 
                 $this->view->title = $this->getReportTitle('Agent Transaction Summary', $qurStr['dur']);
                 $page = $this->_getParam('page');
                 $objReports = new Reports();
                 $agentTxns = $objReports->getAgentTransactions($qurData);
                 $paginator = $objReports->paginateByArray($agentTxns, $page, $paginate = NULL);
                
                 $this->view->paginator=$paginator;
               }   
               
          }
            $this->view->form = $form;
            $this->view->formData = $qurStr; 
    }
    
    
    
    /* exportagenttransactionAction function is responsible to create the csv file on fly with agent load/reload/remittance txns report data
     * and let user download that file.
     */
    
     public function exportagenttransactionAction(){
        
        $qurStr['dur'] = $this->_getParam('dur');
        $form = new AgentTransactionForm(array('action' => $this->formatURL('/reports/exportagenttransaction'),
                                              'method' => 'POST',
                                       )); 
        
         if($qurStr['dur']!=''){    
             
              if($form->isValid($qurStr)){ 
                 $durationArr = Util::getDurationDates($qurStr['dur']);
                 $qurData['duration'] = $qurStr['dur'];
                 $objReports = new Reports();
                 $exportData = $objReports->exportAgentTransactions($qurData);
               
                 $columns = array(
                                    'Transaction Date',
                                    'Agent Code',
                                    'Agent Name',
                                    'Agent City',
                                    'Agent Pincode',
                                    'Transaction Code',
                                    'Transaction Amount',
                                    'CRN',
                                    'Customer Mobile Number',
                                    'Product Code',
                                    'Transaction Reference Number',   
                                 );
                                  
                 $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'agent_transactions');exit;
                 }catch (Exception $e) {
                                         App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                         $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                         $this->_redirect($this->formatURL('/reports/agenttransaction?dur='.$qurStr['dur'].'&sub=1')); 
                                       }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid duration!') );
                         $this->_redirect($this->formatURL('/reports/agenttransaction?dur='.$qurStr['dur'].'&sub=1')); 
                      }             
          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Invalid duration!') );
                    $this->_redirect($this->formatURL('/reports/agenttransaction?dur='.$qurStr['dur'].'&sub=1')); 
                 }    
       }
   

    /*exportagentwisefeereportAction function is responsible to create the csv file on fly with agent fee data
     * and let user download that file.
     */
    
     public function exportagentwisefeereportAction(){
        $qurStr['duration'] = $this->_getParam('duration');
        $qurStr['agent_id'] = $this->_getParam('agent_id');
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        $form = new AgentwiseFeeReportForm(array('action' => $this->formatURL('/reports/agentwisefeereport'),
                                              'method' => 'POST',
                                       )); 
        
         if($qurStr['duration']!=''){
            
              if($form->isValid($qurStr)){ 
                 $durationArr = Util::getDurationDates($qurStr['duration']);
                 $qurData['from'] = $durationArr['from'];
                 $qurData['to'] = $durationArr['to'];  
                 $qurData['bank_unicode'] =  $qurStr['bank_unicode'];
                 $qurData['agent_id'] = $this->_getParam('agent_id');
                 $qurData['check_fee'] = FALSE;
                 $objFeeReport = new FeePlan();
                 $exportData = $objFeeReport->getAgentWiseFeeArray($qurData);
               
                 $columns = array(
                                    'Date',
                                    'Agent Code',
                                    'Agent Name',
                                    'Agent City',
                                    'Agent Pincode', 
                                    'Transaction Ref Number', 
                                    'Transaction Narration',
                                    'Transaction Amount',
                                    //'Fee Plan',
                                    'Fee',
                                    'Service Tax', 
                                    'Reversal Fee',
                                    'Reversal Service Tax',
                                    'Reversal/Refund Transaction Reference Number',
                                    'Transaction Status',
                                    'UTR No',
                                    'Super Distributor Code',
                                    'Super Distributor Name',
                                    'Distributor Code',
                                    'Distributor Name'
                                 );
                                  
                 $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'agentwise_fee');exit;
                 }catch (Exception $e) {
                                         App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                         $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                         $this->_redirect($this->formatURL('/reports/agentwisefeereport?duration='.$qurStr['duration'].'&sub=1&agent_id='.$qurStr['agent_id'].'&bank_unicode='.$qurStr['bank_unicode'])); 
                                       }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid data') );
                         $this->_redirect($this->formatURL('/reports/agentwisefeereport?duration='.$qurStr['duration'].'&sub=1&agent_id='.$qurStr['agent_id'].'&bank_unicode='.$qurStr['bank_unicode'])); 
                      }             
          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Invalid duration') );
                    $this->_redirect($this->formatURL('/reports/agentwisefeereport?duration='.$qurStr['duration'].'&sub=1&agent_id='.$qurStr['agent_id'].'&bank_unicode='.$qurStr['bank_unicode'])); 
                 }    
       }

       
    /* agentwisetransactionsAction function will show the agent load,reload, remittance, refund, refund fee, service tax
     */
    public function agentwisetransactionsAction()
    {   
        $this->title = 'Agent Wise Transactions Report';
        // Get our form and validate it
        $form = new AgentWiseTransactionForm(); 
        $request = $this->getRequest();  
        //$formData  = $this->_request->getPost();//$form->getValues();//
        $qurStr['id'] = $this->_getParam('id');
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['sub'] = $this->_getParam('sub');
        $src = $this->_getParam('src');
               
         if($qurStr['sub']!=''){             
              if($form->isValid($qurStr)){ 
                 $qurData['agent_id'] = $qurStr['id'];
                 $durationArr = Util::getDurationDates($qurStr['dur']);
                 $qurData['duration'] = $qurStr['dur'];
                 
                 
                 $objAgent = new Agents();
                 $agentInfo = $objAgent->findById($qurData['agent_id']);
                 
                 $fromDate = explode(' ', Util::returnDateFormatted($durationArr['from'], "d-m-Y", "Y-m-d", "-"));
                 $toDate = explode(' ', Util::returnDateFormatted($durationArr['to'], "d-m-Y", "Y-m-d", "-"));
                
                 //$this->view->agent_name = $agentInfo->name;
                 $this->view->agentInfo = $agentInfo;
                 
                 $this->view->from = $fromDate[0];
                 $this->view->to   = $toDate[0];  
                 $this->view->title = $this->getReportTitle('Transactions Report', $qurStr['dur'], $qurData['agent_id']);
                 $page = $this->_getParam('page');
                
                 $objReports = new Reports();
                 $agentWiseTxns = $objReports->getAgentWiseTransactions($qurData);
                 $paginator = $objReports->paginateByArray($agentWiseTxns, $page, $paginate = NULL);
                 $this->view->paginator=$paginator;
                
              } 
          }
            $this->view->form = $form;
            $this->view->formData = $qurStr; 
            //$this->view->btnSubmit = $btnSubmit;
            $this->view->callingRprtDur = $qurStr['dur'];
            $this->view->src = $src;
        } 
        
        
        
        
     /* exportagentwisetransactionsAction function is responsible to create the csv file on fly with agent wise load,reload,remittances,
     * refund, remitter fee, service tax report data and let user download that file.
     */
    
     public function exportagentwisetransactionsAction(){
        
        // Get our form and validate it
        $form = new AgentWiseTransactionForm(array('action' => $this->formatURL('/reports/agentwisetransactions'),
                                                              'method' => 'POST',
                                             )); 
        //$formData  = $this->_request->getPost();//$form->getValues();//
        $qurStr['id'] = $this->_getParam('id');
        $qurStr['dur'] = $this->_getParam('dur');
               
         if($qurStr['id']>0 && $qurStr['dur']!=''){             
              if($form->isValid($qurStr)){ 
                 $qurData['agent_id'] = $qurStr['id'];
                 $durationArr = Util::getDurationDates($qurStr['dur']);
                 $qurData['duration'] = $qurStr['dur'];
                 //$qurData['from'] = $durationArr['from'];
                 //$qurData['to'] = $durationArr['to'];                 
                 
                 $objReports = new Reports();
                 $exportData = $objReports->exportAgentWiseTransactions($qurData);
               
                 $columns = array(
                                    'Date',                                    
                                    'Agent Name',
                                    'Agent Code',
                                    'Agent City',
                                    'Agent Pincode',
                                    'Transaction Naration',
                                    'Transaction Amount',                           
                                    'CRN',
                                    'Mobile Number',
                                    'Product Code', 
                                    'Transaction Ref Number',   
                                   );
                                  
                 $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'agent_wise_transactions');exit;
                 } catch (Exception $e) {
                                            App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                            $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                            $this->_redirect($this->formatURL('/reports/agentwisetransactions?dur='.$qurStr['dur'].'&sub=1&id='.$qurStr['id'])); 
                                        }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid data found') );
                         $this->_redirect($this->formatURL('/reports/agentwisetransactions?dur='.$qurStr['dur'].'&sub=1&id='.$qurStr['id'])); 
                      }             
          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Data missing') );
                    $this->_redirect($this->formatURL('/reports/agentwisetransactions?dur='.$qurStr['dur'].'&sub=1&id='.$qurStr['id'])); 
                 }    
       }
       
       
    /*
     * agentsummaryAction function will show 
     * the agent load, reload, remitter, remittance, refund, 
     * including service tax & fee and counts for all
     */
    public function agentsummaryAction() {   
        $this->title = 'Agent Summary Report';        
       // Get our form and validate it
        $form = new AgentSummaryForm(array(
            'action' => $this->formatURL('/reports/agentsummary'),
            'method' => 'POST',
        ));
        
        $request = $this->getRequest();  
        //$formData  = $this->_request->getPost();//$form->getValues();//
        $qurStr['id'] = $this->_getParam('id');
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['to_date'] = $this->_getParam('to_date');
        $qurStr['from_date'] = $this->_getParam('from_date');
        $qurStr['sub'] = $this->_getParam('sub');
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        //$src = $this->_getParam('src');
        
        if($qurStr['sub']!=''){             
            if($form->isValid($qurStr)){
                $qurData['agent_id'] = $qurStr['id']; 
                $qurData['bank_unicode'] = $qurStr['bank_unicode'];  
                
                if ($qurStr['dur'] != '') {
                    $durationArr = Util::getDurationDates($qurStr['dur']);
                    $qurData['duration'] = $qurStr['dur'];
                    $qurData['from'] = $durationArr['from'];
                    $qurData['to'] = $durationArr['to'];
                    $this->view->title = $this->getReportTitle('Agent Summary Report', $qurStr['dur'], $qurData['agent_id'],FALSE,$qurStr['bank_unicode']);
                } else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", "from");
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", "to"); 
                    $pageTitle  = 'Agent Summary Report ';
                    if($qurData['agent_id'] > 0) {
                        $objAgent = new Agents();
                        $agentInfo = $objAgent->findById($qurData['agent_id']);
                        $pageTitle .= ' For '. $agentInfo->name;
                    }
                    if($qurStr['bank_unicode'] != '') {
                        $objBank = new Banks();
                        $bankInfo = $objBank->getBankbyUnicode($qurStr['bank_unicode']);
                        $pageTitle .= ' of '. $bankInfo->name;
                    }
                    $pageTitle .= ' for '. $qurStr['from_date'] .' to '. $qurStr['to_date'] ;
                    $this->view->title = $pageTitle;
                }
                $this->view->sub = $qurStr['sub']; 
                $page = $this->_getParam('page'); 
                $objReports = new Reports();
                $agentSummary = $objReports->getAgentSummary($qurData);
                $form->getElement('agent_id')->setValue($qurData['agent_id']);
                $paginator = $objReports->paginateByArray($agentSummary, $page, $paginate = NULL);
                $this->view->paginator=$paginator;  
            } 
        }
        $this->view->form = $form;
        $this->view->formData = $qurStr; 
        //$this->view->callingRprtDur = $qurStr['dur'];
        //$this->view->src = $src;
    }
       
       
        
    /* exportagentsummaryAction function is responsible 
     * to create the csv file on fly with agent load,reload, remitter, remittance, refund, 
     * with service tax and fee and counts for all report data and let user download that file.
     */
    
     public function exportagentsummaryAction(){
        
        // Get our form and validate it
        $form = new AgentSummaryForm(); 
        $qurStr['id'] = $this->_getParam('id');
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['to_date'] = $this->_getParam('to_date');
        $qurStr['from_date'] = $this->_getParam('from_date');
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');         
        if($qurStr['dur']!=''|| ($qurStr['to_date'] !='' && $qurStr['from_date']!='')){
              if($form->isValid($qurStr)){ 
                 $qurData['agent_id'] = $qurStr['id']; 
                if ($qurStr['dur'] != '') {
                    $durationArr = Util::getDurationDates($qurStr['dur']);
                    $qurData['duration'] = $qurStr['dur'];
                    $qurData['from'] = $durationArr['from'];
                    $qurData['to'] = $durationArr['to'];
                } else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", "from");
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", "to");  
                } 
                $qurData['bank_unicode'] = $qurStr['bank_unicode'];
                $agentInfo = Array();
                
                 $objReports = new Reports();
                 $exportData = $objReports->exportAgentSummary($qurData);
                 $columns = array(
                    'Transaction Date',
                    'Super Distributor Code',
                    'Super Distributor Name',
                    'Distributor Code',
                    'Distributor Name',
                    'Agent Name',
                    'Agent Code',
                    'Email',
                    'Mobile',
                    'City',
                    'Pin Code',
                    'Card Loads Count',
                    'Card Loads Amount',
//                    'Card Reloads Count',
//                    'Card Reloads Amount',
                    'Remitters Registration Count',
                    'Remitters Registration Amount',
                    'Remittance Count',
                    'Remittance Amount',
                    'Remittance Refund Count',
                    'Remittance Refund Amount',   
                     'Product Name'
                                 );
                 

                 $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'agent_summary');exit;
                 } catch (Exception $e) {
                                            App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                            $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                            $this->_redirect($this->formatURL('/reports/agentsummary?dur='.$qurStr['dur'].'&sub=1&id='.$qurStr['id'].'&bank_unicode='.$qurStr['bank_unicode'].'&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'])); 
                                        }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid data found') );
                         $this->_redirect($this->formatURL('/reports/agentsummary?dur='.$qurStr['dur'].'&sub=1&id='.$qurStr['id'].'&bank_unicode='.$qurStr['bank_unicode'].'&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'])); 
                      }             
          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Data missing') );
                    $this->_redirect($this->formatURL('/reports/agentsummary?dur='.$qurStr['dur'].'&sub=1&id='.$qurStr['id'].'&bank_unicode='.$qurStr['bank_unicode'].'&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'])); 
                 }    
       }
       
       
       
       /* agentcommissionsummaryAction function is responsible to generate agent commission summary report
        * on duration and agent id(optional) basis
        */
       
        public function agentcommissionsummaryAction(){
         $this->title = 'Agent Commission Summary Report';                         
         // Get our form and validate it
         $form = new AgentCommissionSummaryForm(array('action' => $this->formatURL('/reports/agentcommissionsummary'),
                                                    'method' => 'POST',
                                             )); 
  
        $request = $this->_getAllParams();
        //$sub = $this->_getParam('sub');
        $qurStr['id']  = $this->_getParam('id');
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['sub'] = $this->_getParam('sub');
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        $page = $this->_getParam('page');
        
        if($qurStr['sub']!=''){
              if($form->isValid($qurStr)){ 
                 $queryParam = array(
                                        'agent_id'=>$qurStr['id'],
                                        'duration'=>$qurStr['dur'],
                                        'bank_unicode' => $qurStr['bank_unicode']
                                    );
                 
                 $this->view->title = $this->getReportTitle('Agent Commission Summary Report', $qurStr['dur'], $queryParam['agent_id']);
                 
                 $objReports = new Reports();
                 $rptData = $objReports->getAgentCommissionSummary($queryParam);
                 
                 $paginator = $objReports->paginateByArray($rptData, $page, $paginate = NULL);
                 $this->view->paginator = $paginator;
                 $this->view->formData = $qurStr;
                 $cronDuration = App_DI_Container::get('ConfigObject')->cron->commission_report;
                 $cronId = CRON_COMMISSION_REPORT_ID;
                 $form->getElement('agent_id')->setValue($queryParam['agent_id']);
                 $this->view->noteText = $this->getReportLastUpdateMessage($cronDuration, $cronId);
              } 
        }
         // }
            $this->view->form = $form;
            
    }
        
        /* getReportLastUpdateMessage function is responsible to generate agent commission summary report update message
        *  param:- cron duration and cron id
        */
    
        private function getReportLastUpdateMessage($cronDuration, $cronId=0){
          if($cronDuration!='' && $cronId>=1){
              $objCrons = new Crons();
              
              $cronInfo = $objCrons->getCronInfo(array('cron_id'=>$cronId));
              if(!empty($cronInfo)){
                        $cronUpdatedDate = Util::returnDateFormatted($cronInfo->date_updated, "Y-m-d", "d-m-Y", "-");
                        
                        if($cronInfo->status_cron==STATUS_COMPLETED) {
                            $currentDateTime = strtotime($cronUpdatedDate);
                            $futureDateTime = $currentDateTime+(60*$cronDuration);
                            $nextUpdateTime = date("d-m-Y H:i:s", $futureDateTime);
                            $retText = "Commission Summary till ".$cronUpdatedDate.". Next update scheduled at: ".$nextUpdateTime;
                        } else 
                            $retText = "Commission Summary till ".$cronUpdatedDate.".";
              } else 
                   $retText = 'Records will be updated shortly';
              
              return $retText;
              
          } else return '';
        }
    
        /* exportagentcommissionsummaryAction function is responsible to generate agent commission summary report export csv data
        *  param :- duration and agent id(optional) 
        */
    
     public function exportagentcommissionsummaryAction(){
        
        // Get our form and validate it
        $form = new AgentCommissionSummaryForm(array('action' => $this->formatURL('/reports/agentcommissionsummary'),
                                                    'method' => 'POST',
                                             )); 
   
        $qurStr['id'] = $this->_getParam('id');
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');      
         if($qurStr['dur']!=''){             
              if($form->isValid($qurStr)){ 
                 $qurData['agent_id'] = $qurStr['id'];
                 $durationArr = Util::getDurationDates($qurStr['dur']);
                 $qurData['duration'] = $qurStr['dur'];
                 $qurData['bank_unicode'] =  $qurStr['bank_unicode']; 
                 $agentInfo = Array();
                 //$qurData['from'] = $durationArr['from'];
                 //$qurData['to'] = $durationArr['to'];                 
                 
                        
                 $objReports = new Reports();
                 $exportData = $objReports->exportAgentCommissionSummary($qurData);
                 $columns = array(
                                    'Transaction Date',
                                    'Super Distributor Code',
                                    'Super Distributor Name',
                                    'Distributor Code',
                                    'Distributor Name',
                                    'Agent/Distributor/Super Distributor Name',
                                    'Agent/Distributor/Super Distributor Code',       
                                    'City',
                                    'Pin Code',
                                    'Card Load/Reload Amount',
                                    'Card Load/Reload Commission Amount',        
                                    'Remittance Amount',
                                    'Remittance Commission Amount',
                                    'Commission Plan',
                                    'Remittance Fee'
                                 );
                 

                 $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'agent_commission_summary');exit;
                 } catch (Exception $e) {
                                            App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                            $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                            $this->_redirect($this->formatURL('/reports/agentcommissionsummary?dur='.$qurStr['dur'].'&sub=1&id='.$qurStr['id'].'&bank_unicode='.$qurStr['bank_unicode'])); 
                                        }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid data found') );
                         $this->_redirect($this->formatURL('/reports/agentcommissionsummary?dur='.$qurStr['dur'].'&sub=1&id='.$qurStr['id'].'&bank_unicode='.$qurStr['bank_unicode'])); 
                      }             
          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Data missing') );
                    $this->_redirect($this->formatURL('/reports/agentcommissionsummary?dur='.$qurStr['dur'].'&sub=1&id='.$qurStr['id'].'&bank_unicode='.$qurStr['bank_unicode'])); 
                 }    
       }
   /**
    * pendingagentfundrequestsAction fetches all fund request for agents which are pending
    */
    public function pendingagentfundrequestsAction(){
         $this->title = 'Agent Unauthorized Funding Report';              
         // Get our form and validate it
         $form = new PendingAgentFundRequestsForm(array('action' => $this->formatURL('/reports/pendingagentfundrequests'),
                                                    'method' => 'POST',
                                             )); 
        $page = $this->_getParam('page');
        $request = $this->_getAllParams();
        $sub = $this->_getParam('sub');
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['sub'] = $sub;
        
         if($sub!=''){ 
             
              if($form->isValid($qurStr)){ 
                 $durationArr = Util::getDurationDates($qurStr['dur']);
                 $qurData['from'] = $durationArr['from'];
                 $qurData['to'] = $durationArr['to'];
                 $fromDate = explode(' ', Util::returnDateFormatted($durationArr['from'], "d-m-Y", "Y-m-d", "-"));
                 $toDate = explode(' ', Util::returnDateFormatted($durationArr['to'], "d-m-Y", "Y-m-d", "-"));
                 $this->view->from = $fromDate[0];
                 $this->view->to   = $toDate[0];                 
                 $this->view->title = $this->getReportTitle('Agent Unauthorized Funding Report', $qurStr['dur']);
                 
                 $objReports = new Reports();
                 $rptData = $objReports->getPendingAgentFundRequests($qurData);
                 $paginator = $objReports->paginateByArray($rptData, $page, $paginate = NULL);
                 $this->view->paginator = $paginator;
                 $this->view->formData = $qurStr;
              }                  
          }
            $this->view->form = $form;
            
    }
    
     /* exportpendingagentfundrequestsAction function is responsible to create the csv file on fly with agent fund requests report data
     * and let user download that file.
     */
    
     public function exportpendingagentfundrequestsAction(){
        
         // Get our form and validate it
         $form = new PendingAgentFundRequestsForm(array('action' => $this->formatURL('/reports/pendingagentfundrequests'),
                                                    'method' => 'POST',
                                             )); 
  
        $qurStr['dur'] = $this->_getParam('dur');
        
         if($qurStr['dur']!=''){ 
             
              if($form->isValid($qurStr)){ 
                 $durationArr = Util::getDurationDates($qurStr['dur']);
                 $qurData['from'] = $durationArr['from'];
                 $qurData['to'] = $durationArr['to'];
                 
                 $objReports = new Reports();
                 $exportData = $objReports->exportPendingAgentFundRequests($qurData);
               
                 $columns = array(
                                    'Agent Name',
                                    'Agent code',
                                    'Current Bank Assigned',
                                    'Transaction Date',
                                    'Fund transfer Type',
                                    'Amount',
                                    'Remarks'  
                                 );
                                  
                 $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'pending_fund_requests');exit;
                 }catch (Exception $e) {
                                         App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                         $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                         $this->_redirect($this->formatURL('reports/pendingagentfundrequests?dur='.$qurStr['dur'].'&sub=1')); 
                                       }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid duration!') );
                         $this->_redirect($this->formatURL('reports/pendingagentfundrequests?dur='.$qurStr['dur'].'&sub=1')); 
                      }             
          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Invalid duration!') );
                    $this->_redirect($this->formatURL('reports/pendingagentfundrequests?dur='.$qurStr['dur'].'&sub=1')); 
                 }    
       }
       
    /**
    * userloginAction fetches all login related details for User type 'agent' or 'operation
    */
    public function userloginAction(){
         $this->title = 'Login Report';              
         // Get our form and validate it
         $form = new UserLoginForm(array('action' => $this->formatURL('/reports/userlogin'),
                                                    'method' => 'POST',
                                             )); 
        $user = 'User';
        $page = $this->_getParam('page');
        $request = $this->_getAllParams();
        $sub = $this->_getParam('sub');
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['user_type'] = $this->_getParam('user_type');
        $qurStr['sub'] = $sub;
        if ($qurStr['user_type'] == DbTable::TABLE_AGENTS){
            $user = USER_TYPE_AGENT;
        }
        else{
            $user = USER_TYPE_OPERATION;
        }
         if($sub!=''){ 
             
              if($form->isValid($qurStr)){ 
                 $durationArr = Util::getDurationDates($qurStr['dur']);
                 $qurData['from'] = $durationArr['from'];
                 $qurData['to'] = $durationArr['to'];
                 $qurData['user_type'] = $qurStr['user_type'];
                 $fromDate = explode(' ', Util::returnDateFormatted($durationArr['from'], "d-m-Y", "Y-m-d", "-"));
                 $toDate = explode(' ', Util::returnDateFormatted($durationArr['to'], "d-m-Y", "Y-m-d", "-"));
                 $this->view->from = $fromDate[0];
                 $this->view->to   = $toDate[0];                 
                 $this->view->title = $this->getReportTitle($user.' Login Report', $qurStr['dur']);
                 $statusandReason = array();
                 $objReports = new Reports();
                 $rptData = $objReports->getUserLogin($qurData);
                 foreach($rptData as $rpt){
                     $statusandReason[] = $this->getStatusAndReason($rpt);
                 }
                 for($i=0; $i<count($rptData);$i++) {
           
                $finalArr[] = array_merge($rptData[$i],$statusandReason[$i]);
              }
                 $paginator = $objReports->paginateByArray($finalArr, $page, $paginate = NULL);
                 $this->view->paginator = $paginator;
                 $this->view->formData = $qurStr;
              }                  
          }
            $this->view->form = $form;
            
    }
    
     /* exportpendingagentfundrequestsAction function is responsible to create the csv file on fly with agent fund requests report data
     * and let user download that file.
     */
    
     public function exportuserloginAction(){
        
         // Get our form and validate it
         $form = new UserLoginForm(array('action' => $this->formatURL('/reports/userlogin'),
                                                    'method' => 'POST',
                                             )); 
  
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['user_type'] = $this->_getParam('user_type');
         if ($qurStr['user_type'] == DbTable::TABLE_AGENTS){
            $user = 'agent_user';
        }
        else{
            $user = 'operation_user';
        }
        if($qurStr['dur']!=''){ 
             
              if($form->isValid($qurStr)){ 
                 $durationArr = Util::getDurationDates($qurStr['dur']);
                 $qurData['from'] = $durationArr['from'];
                 $qurData['to'] = $durationArr['to'];
                 $qurData['user_type'] = $this->_getParam('user_type');
                 $objReports = new Reports();
                 $rptData = $objReports->getUserLogin($qurData);
                foreach($rptData as $rpt){
                     $statusandReason[] = $this->getStatusAndReason($rpt);
                 }
                 for($i=0; $i<count($rptData);$i++) {
           
                $finalArr[] = array_merge($rptData[$i],$statusandReason[$i]);
                }
                foreach($finalArr as $key=>$data){
  
                    $exportData[$key]['user_name']        = $data['user_name'];
                    $exportData[$key]['user_code']        =  $data['user_code'];
                    $exportData[$key]['username']        =  $data['logusername'];
                    $exportData[$key]['system_ip']        = Util::restoreIpAddressFromat($data['system_ip']);
                    $exportData[$key]['login1_datetime']   = Util::returnDateFormatted($data['login1_datetime']);
                    $exportData[$key]['login2_datetime']   = Util::returnDateFormatted($data['login2_datetime']);
                    $exportData[$key]['datetime_logout']  = Util::returnDateFormatted($data['datetime_logout']);
                    $exportData[$key]['login_status']     = ucfirst($data['login_status'])  ;
                    $exportData[$key]['failed_reason']    = $data['failed_reason'] ;
                    $exportData[$key]['pwd_change_date']  = Util::returnDateFormatted($data['pwd_change_date']) ;
                    $exportData[$key]['user_status']     = $data['user_status'] ;
                   
          }
                 $columns = array(
                    'User Name',
                    'User Code',
                    'Username', 
                    'System IP',
                    'Login Step 1 Date & Time',
                    'Login Step 2 Date & Time',
                    'Log Out Date & Time',
                    'Login status',
                    'Failed Reason',
                    'Last Password Change Date',
                    'User Status'
                );
                                  
                 $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, $user.'_login');exit;
                 }catch (Exception $e) {
                                         App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                         $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                         $this->_redirect($this->formatURL('reports/userlogin?dur='.$qurStr['dur'].'&sub=1'.'&user_type='.$qurstr['type'])); 
                                       }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid duration!') );
                         $this->_redirect($this->formatURL('reports/userlogin?dur='.$qurStr['dur'].'&sub=1'.'&user_type='.$qurstr['type'])); 
                      }             
          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Invalid duration!') );
                    $this->_redirect($this->formatURL('reports/userlogin?dur='.$qurStr['dur'].'&sub=1'.'&user_type='.$qurstr['type'])); 
                 }    
       }  
      public function getStatusAndReason($agentData){
               $reasonStr = '';
               $agent = array();
               $numLoginAttempts =  $agentData['num_login_attempts'];
               $login1 = $agentData['login1_datetime'];
               $login2 = $agentData['login2_datetime'];
               //Username is incorrect
               if( $agentData['comment_username'] == STATUS_FAILURE){
                    $reasonStr = 'Incorrect Username';
               }
               else {
               //Login step 1 failure reason
               if($login1 != ''){
                  
                  if ($agentData['comment_password'] == STATUS_FAILURE){
                      $reasonStr = 'Incorrect Password';
                  }
                  else{
                  $explodeReason = explode(":",$agentData['comment_username']);
                
                      $msg = explode("=",$explodeReason[1]);
                    
                    $statusType = $msg[0];
                   
                  switch(trim($msg[1])){
               case STATUS_LOCKED:
                   $reasonMsg = 'User Locked after incorrect login attempts';
               break;
               case STATUS_BLOCKED:
               case STATUS_INACTIVE:
                   $reasonMsg = 'Blocked by operation user';
                   break;
               default:
                   $reasonMsg = ucfirst($msg[1]);
               }  
               if (trim($msg[1]) == STATUS_UNBLOCKED || trim($msg[1]) == STATUS_ACTIVE)
                   $reasonStr = '';
               else if ($statusType == 'email_status' && trim($msg[1])== STATUS_PENDING)
                   $reasonStr = 'Email Id Not Verified';
              else if ( $statusType == 'enroll_status' && trim($msg[1])!= STATUS_APPROVED)
                   $reasonStr = 'Account Not Approved';
               else
                   $reasonStr = ucfirst($reasonMsg); 
                  }
               }
               //Login step 2 failure reason
               else if($login2 != ''){
                 
                   if ($agentData['comment_auth'] == STATUS_FAILURE){
                      $reasonStr = 'Incorrect Auth Code';
                  }
                  else{
                  $explodeReason = explode(":",$agentData['comment_auth']);
                 if(count($explodeReason) > 1){
                      $msg = explode("=",$explodeReason[1]);
                    
                  $statusType = ucfirst(trim($msg[0]));
                  
                  switch(trim($msg[1])){
               case STATUS_LOCKED:
                   $reasonMsg = 'User Locked after incorrect login attempts';
               break;
               case STATUS_BLOCKED:
               case STATUS_INACTIVE:
                   $reasonMsg = 'Blocked by operation user';
                   break;
               default:
                   $reasonMsg = ucfirst($msg[1]);
               }  
                  
                 
               if (trim($agentData['comment_auth']) == STATUS_UNBLOCKED || trim($agentData['comment_auth']) == STATUS_ACTIVE)
                   $reasonStr = '';
              else if ($statusType == 'email_status' && trim($agentData['comment_auth'])== STATUS_PENDING)
                   $reasonStr = 'Email Id Not Verified';
              else if ($statusType == 'enroll_status' && trim($agentData['comment_auth'])!= STATUS_APPROVED)
                   $reasonStr = 'Account Not Approved';
               else
                   $reasonStr = ucfirst($reasonMsg); 
               }
                  }
               }
               }
               $agent['failed_reason'] = $reasonStr;
               return $agent;
      }
      
      
      
      
    /*
     * Login Summary report for agent / operation login logs for success / failure
     */
    public function loginsummaryAction(){
        
        $this->title = 'Login Summary Report';           
         // Get our form and validate it
        $form = new LoginSummaryForm(array('action' => $this->formatURL('/reports/loginsummary'),
                                                    'method' => 'POST',
                                         )); 
        $qurStr['from_date'] = $this->_getParam('from_date');
        $qurStr['to_date'] = $this->_getParam('to_date');
        $qurStr['user_type'] = $this->_getParam('user_type');
        $qurStr['btn_submit'] = $this->_getParam('btn_submit');
        //$qurStr['submit'] = $this->_getParam('submit');
        $page = $this->_getParam('page');
        
         
         if($qurStr['btn_submit']){             
              if($form->isValid($qurStr)){ 
                 if ($qurStr['user_type'] == DbTable::TABLE_AGENTS){
                    $user = USER_TYPE_AGENT;
                 }
                 else{
                     $user = USER_TYPE_OPERATION;
                 }
                 
                 $pageTitle = Util::getListingTitle('Login Summary Report for '.strtoupper($user), $qurStr['duration']);
                
                 
                 $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-","-","from");
                 $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-","-","to");
                 $qurData['user_type'] = $qurStr['user_type'];
                 
                 $objReport = new Reports();
                 $loginSummaryArr = $objReport->getLoginSummary($qurData);
                 
                 $paginator = $objReport->paginateByArray($loginSummaryArr, $page, $paginate = NULL);
                 $this->view->paginator = $paginator;
                 $this->view->pageTitle = $pageTitle;
                 $this->view->btnSubmit = $qurStr['btn_submit'];
               }   
               
          }
            $this->view->form = $form;
            $this->view->formData = $qurStr; 
           
            
    }
    
    
    
    /* exportloginsummaryAction function is responsible to create the csv file on fly 
     * for agent / operation login logs for success / failure and let user download that file.
     */
    
     public function exportloginsummaryAction(){
        
        // Get our form and validate it
        $form = new LoginSummaryForm(array('action' => $this->formatURL('/reports/exportloginsummary'),
                                                    'method' => 'POST',
                                    ));
        $qurStr['from_date'] = $this->_getParam('from_date');
        $qurStr['to_date'] = $this->_getParam('to_date');
        $qurStr['user_type'] = $this->_getParam('user_type');
            
         if($qurStr['user_type']!='' && $qurStr['from_date']!='' && $qurStr['to_date']!=''){             
              if($form->isValid($qurStr)){ 
                
                 $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-","-","from");
                 $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-","-","to");
                 
                 $qurData['user_type'] = $qurStr['user_type'];
                 
                 if($qurData['user_type']==DbTable::TABLE_AGENTS){
                    $columns = array(
                                    'User Code',
                                    'User Name',
                                    'Mobile',
                                    'Email',
                                    'Date',
                                    'Login step1 failed attempts count',
                                    'Login step2 failed attempts count',
                                    'Successful login count' ,
                                    'Status'
                                    );
                 }
                 else if($qurData['user_type']==DbTable::TABLE_OPERATION_USERS){
                        $columns = array(
                                        'User Name',
                                        'Mobile',
                                        'Email',
                                        'Date',
                                        'Login step1 failed attempts count',
                                        'Login step2 failed attempts count',
                                        'Successful login count' ,
                                        'Status'
                                        );
                 }
                 
                 $objReports = new Reports();
                 $exportData = $objReports->exportLoginSummary($qurData);
                 
                 

                 $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'login_summary');exit;
                 } catch (Exception $e) {
                                            App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                            $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                            $this->_redirect($this->formatURL('/reports/loginsummary?from_date='.$qurStr['from_date'].'&to_date='.$qurStr['to_date'].'&user_type='.$qurStr['user_type'])); 
                                        }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid data found') );
                         $this->_redirect($this->formatURL('/reports/loginsummary?from_date='.$qurStr['from_date'].'&to_date='.$qurStr['to_date'].'&user_type='.$qurStr['user_type'])); 
                      }             
          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Data missing') );
                    $this->_redirect($this->formatURL('/reports/loginsummary?from_date='.$qurStr['from_date'].'&to_date='.$qurStr['to_date'].'&user_type='.$qurStr['user_type'])); 
                 }    
       }
       
        public function customerregistrationAction(){
         $this->title = 'Customer Registration Report';              
         // Get our form and validate it
         $form = new CustomerRegistrationForm(array('action' => $this->formatURL('/reports/customerregistration'),
                                                    'method' => 'POST',
                                             )); 
        $page = $this->_getParam('page');
        $request = $this->_getAllParams();
        $sub = $this->_getParam('sub');
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        $qurStr['product_id'] = $this->_getParam('product_id');
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['sub'] = $sub;
        $qurStr['to_date']    = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
      
         if($sub!=''){ 
             
              if($form->isValid($qurStr)){ 
                 if ($qurStr['dur'] != '') {
                 $durationArr = Util::getDurationDates($qurStr['dur']);
                 $qurData['from'] = $durationArr['from'];
                 $qurData['to'] = $durationArr['to'];
                 $qurData['product_id'] = $qurStr['product_id'];
                 $fromDate = explode(' ', Util::returnDateFormatted($durationArr['from'], "d-m-Y", "Y-m-d", "-"));
                 $toDate = explode(' ', Util::returnDateFormatted($durationArr['to'], "d-m-Y", "Y-m-d", "-"));
                 $this->view->from = $fromDate[0];
                 $this->view->to   = $toDate[0];                 
                 $this->view->title = $this->getReportTitle('Customer Registration Report', $qurStr['dur'],0,FALSE,$qurStr['bank_unicode']);
                 
                } else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurFrm['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                    $qurFrm['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", 'from');
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", 'to');
                    $qurData['product_id'] = $qurStr['product_id'];
                    $objBank = new Banks();
                    $bankInfo = $objBank->getBankbyUnicode($qurStr['bank_unicode']);
                    $this->view->title = 'Customer Registration Report of '.$bankInfo->name.' for '.Util::returnDateFormatted($qurFrm['from'], "Y-m-d", "d-m-Y", "-");
                    $this->view->title .= ' to '.Util::returnDateFormatted($qurFrm['to'], "Y-m-d", "d-m-Y", "-");
                    $this->view->from = $qurFrm['from'];
                    $this->view->to   = $qurFrm['to'];
                }
                 $qurData['bank_unicode'] = $qurStr['bank_unicode'];
                 $bankUnicodeArr = Util::bankUnicodesArray();
                 $objReports = new Reports();
                 if ($qurStr['bank_unicode'] == $bankUnicodeArr['1']) {
                     $objCardholders = new Corp_Boi_Customers();
                     $cardholders = $objCardholders->getRegisteredCardholders($qurData);
                 }else{
                 
                 $cardholders = $objReports->getCardholders($qurData);
                 }
                 $this->view->paginator = $objReports->paginateByArray($cardholders, $page, $paginate = NULL);
                 $this->view->formData = $qurStr;
                 $form->getElement('product')->setValue($qurStr['product_id']);
                 $form->getElement('product_id')->setValue($qurStr['product_id']);
              }   
             
          }
           $form->getElement('product')->setValue($qurStr['product_id']);
           $form->getElement('product_id')->setValue($qurStr['product_id']);
            $this->view->form = $form;
            
    }
    /* exportcustomerregistrationAction function is responsible to create the csv file on fly with agent fund requests report data
     * and let user download that file.
     */
    
     public function exportcustomerregistrationAction(){
        
         // Get our form and validate it
         $form = new CustomerRegistrationForm(array('action' => $this->formatURL('/reports/customerregistration'),
                                                    'method' => 'POST',
                                             )); 
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $qurStr['product_id'] = $this->_getParam('product_id');
        
         if($qurStr['dur']!=''|| ($qurStr['to_date'] !='' && $qurStr['from_date']!='')){ 
             
              if($form->isValid($qurStr)){ 
                  if ($qurStr['dur'] != '') {
                 $durationArr = Util::getDurationDates($qurStr['dur']);
                 $qurData['from'] = $durationArr['from'];
                 $qurData['to'] = $durationArr['to'];
                 
                } else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", 'to');
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", 'from');
                   
                }
                 $qurData['product_id'] = $qurStr['product_id'];
                 $qurData['bank_unicode'] = $qurStr['bank_unicode'];
                 $bankUnicodeArr = Util::bankUnicodesArray();
                 if ($qurStr['bank_unicode'] == $bankUnicodeArr['1']) {
                     $objCardholders = new Corp_Boi_Customers();
                     $exportData = $objCardholders->exportRegisteredCardholders($qurData);
                 }else{
                  $objReports = new Reports();
                  
                  $exportData = $objReports->exportgetCardholders($qurData);
                 }
                 
                if ($qurStr['bank_unicode'] == $bankUnicodeArr['2']) {
// column names & indexes
                    

                  $columns = array(
                        'Product Name',
                        'Medi Assist Id',
                        'Partner Ref No.',
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
                        'Is Card Dispatch',
                        'Card Dispatch Date',
                        'Wallet Code',
                        'Date',
                        'Status',
                        'Failed Date',
                        'Failed Reason',
			'Channel'
                    );
                } 
                else if ($qurStr['bank_unicode'] == $bankUnicodeArr['1']) {
                     
                        $columns = array(
                        'Product Name',
                        'NSDC Enrollment No',
                        'Ref No.',
                        'Card Number',
                        'First Name',
                        'Middle Name',
                        'Last Name',
                        'Name on Card',
                        'Gender',
                        'Date of Birth',
                        'Mobile',
                        'Email',
                        'Landline',
                        'Address Line 1',
                        'Address Line 2',
                        'City',
                        'State',
                        'Pincode',
                        'Mothers Maiden Name',
                        'Employer Name',
                        'Corporate Id',
                        'Corporate Address Line 1',
                        'Corporate Address Line 2',
                        'City',
                        'State',
                        'Pincode',
                        'Date',
                        'Status',
                        'Failed Date',
                        'Failed Reason',
                    );
                } else {
                    // column names & indexes
                   $columns = array(
                        'Product Name',
                        'Member Id',
                        'Employee Id',
                        'Card Number',
                        'First Name',
                        'Middle Name',
                        'Last Name',
                        'Name on Card',
                        'Gender',
                        'Date of Birth',
                        'Mobile',
                        'Email',
                        'Landline',
                        'Address Line 1',
                        'Address Line 2',
                        'State',
                        'Pincode',
                        'Mothers Maiden Name',
                        'Employer Name',
                        'Corporate Id',
                        'Corporate Address Line 1',
                        'Corporate Address Line 2',
                        'State',
                        'Pincode',
                        'Date',
                        'Status',
                        'Failed Date',
                        'Failed Reason',
                    );
                }

                $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'cardholder_registration');exit;
                 }catch (Exception $e) {
                                         App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                         $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                         $this->_redirect($this->formatURL('/reports/customerregistration?dur='.$qurStr['dur'].'&sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&product_id='.$qurStr['product_id'].'&bank_unicode='.$qurStr['bank_unicode'])); 
                                       }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid Paramaters!') );
                         $this->_redirect($this->formatURL('/reports/customerregistration?dur='.$qurStr['dur'].'&sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&product_id='.$qurStr['product_id'].'&bank_unicode='.$qurStr['bank_unicode'])); 
                      }             
          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Invalid duration!') );
                    $this->_redirect($this->formatURL('/reports/customerregistration?dur='.$qurStr['dur'].'&sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&product_id='.$qurStr['product_id'].'&bank_unicode='.$qurStr['bank_unicode'])); 
                 }    
       }
       
       public function wallettxnAction(){
         $this->title = 'Transaction Report Wallet-wise';              
         // Get our form and validate it
         $form = new WalletTxnForm(array('action' => $this->formatURL('/reports/wallettxn'),
                                                    'method' => 'POST',
                                             )); 
        $page = $this->_getParam('page');
        $sub = $this->_getParam('sub');
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        $qurStr['wallet_type'] = $this->_getParam('wallet_type');
        $qurStr['product_id'] = $this->_getParam('product_id');
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['sub'] = $sub;
        $qurStr['to_date']    = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
      
         if($sub!=''){ 
             
              if($form->isValid($qurStr)){ 
                 if ($qurStr['dur'] != '') {
                 $durationArr = Util::getDurationDates($qurStr['dur']);
                 $qurData['from'] = $durationArr['from'];
                 $qurData['to'] = $durationArr['to'];
                 $qurData['product_id'] = $qurStr['product_id'];
                 $qurData['bank_unicode'] = $qurStr['bank_unicode'];
                 $qurData['wallet_type'] = $qurStr['wallet_type'];
                 $fromDate = explode(' ', Util::returnDateFormatted($durationArr['from'], "d-m-Y", "Y-m-d", "-"));
                 $toDate = explode(' ', Util::returnDateFormatted($durationArr['to'], "d-m-Y", "Y-m-d", "-"));
                 $this->view->from = $fromDate[0];
                 $this->view->to   = $toDate[0];                 
                 $this->view->title = $this->getReportTitle('Transaction Report Wallet-wise', $qurStr['dur'],0,FALSE,$qurStr['bank_unicode']);
                 
                } else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurFrm['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                    $qurFrm['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", 'from');
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", 'to');
                    $qurData['product_id'] = $qurStr['product_id'];
                    $qurData['bank_unicode'] = $qurStr['bank_unicode'];
                    $qurData['wallet_type'] = $qurStr['wallet_type'];
                    $objBank = new Banks();
                    $bankInfo = $objBank->getBankbyUnicode($qurData['bank_unicode']);
                    $this->view->title = 'Transaction Report Wallet-wise of '. $bankInfo->name.' for '.Util::returnDateFormatted($qurFrm['from'], "Y-m-d", "d-m-Y", "-");
                    $this->view->title .= ' to '.Util::returnDateFormatted($qurFrm['to'], "Y-m-d", "d-m-Y", "-");
                    $this->view->from = $qurFrm['from'];
                    $this->view->to   = $qurFrm['to'];
                }
                 
                 $objReports = new Reports();
                 $custTxns = $objReports->getWalletTxn($qurData);
                 $this->view->paginator = $objReports->paginateByArray($custTxns, $page, $paginate = NULL);
                 
                 $this->view->formData = $qurStr;
                 $form->getElement('product')->setValue($qurStr['product_id']);
              }   
             
          }
          $form->getElement('product')->setValue($qurStr['product_id']);
          $productModel = new Products();
          $productPaytronic = App_DI_Definition_BankProduct::getInstance(BANK_RATNAKAR_PAT);
          $productPatUnicode = $productPaytronic->product->unicode;
          $productPat = $productModel->getProductInfoByUnicode($productPatUnicode);
          $this->view->productPat = $productPat->id;
          $this->view->productId = $qurStr['product_id'];
          $this->view->form = $form;
            
    }
    
    public function exportwallettxnAction(){
        
         // Get our form and validate it
         $form = new WalletTxnForm(array('action' => $this->formatURL('/reports/wallettxn'),
                                                    'method' => 'POST',
                                             )); 
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $qurStr['product_id'] = $this->_getParam('product_id');
        $qurStr['wallet_type'] = $this->_getParam('wallet_type');
        
         if($qurStr['dur']!=''|| ($qurStr['to_date'] !='' && $qurStr['from_date']!='')){ 
             
              if($form->isValid($qurStr)){ 
                  if ($qurStr['dur'] != '') {
                 $durationArr = Util::getDurationDates($qurStr['dur']);
                 $qurData['from'] = $durationArr['from'];
                 $qurData['to'] = $durationArr['to'];
                 
                } else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", 'from');
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", 'to');
                    
                   
                }
                 $qurData['product_id'] = $qurStr['product_id'];
                 $qurData['bank_unicode'] = $qurStr['bank_unicode'];
                 $qurData['wallet_type'] = $qurStr['wallet_type'];
                 $objReports = new Reports();
                 $exportData = $objReports->getWalletTxn($qurData);
                 
                $bankUnicodeArr = Util::bankUnicodesArray();
                if ($qurStr['bank_unicode'] == $bankUnicodeArr['2']) {
                 $columns = array(
                    'Transaction Date and Time',
                    'Product Name',
                    'Bank Name',
                    'Card Number',
                    'Card Pack Id',
                    'Medi Assist ID / Partner Ref No',
                    'Transaction Type',
                    'Transaction Status',
                    'Wallet Code',
                    'Mode',
                    'Amount',                    
                    'Transaction Amount',
                    'Fee',
                    'Service Tax', 
                    'RRNO / Transaction Ref No.',
                    'Acknowledge No.',
                    'Decline Reason',
                    'MCC',
                    'TID',
                    'MID',
                    'Channel',
                    'Reversal Flag',
                    'Reversal Date',
                    'Original Txn No',
                    'Original Transaction Date & Time',                     
                    'Transaction Narration',
                    'Settlement Flag',
                    'Settlement Date',
                    'Benf. A/c No',
                    'Benf. A/c Name',
                    'Response file Reference Number',
		    'Block Date',
		    'Unblock Date' 
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
                    'Wallet Code',
                    'Mode',
                    'Wallet A Dr',
                    'Wallet A Cr',
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
                        $resp = $objCSV->export($exportData, $columns, 'wallet_txn');exit;
                 }catch (Exception $e) {
                                         App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                         $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                         $this->_redirect($this->formatURL('/reports/wallettxn?dur='.$qurStr['dur'].'&sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&product_id='.$qurStr['product_id'].'&bank_unicode='.$qurStr['bank_unicode'].'&wallet_type='.$qurStr['wallet_type'])); 
                                       }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid Paramaters!') );
                         $this->_redirect($this->formatURL('/reports/wallettxn?dur='.$qurStr['dur'].'&sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&product_id='.$qurStr['product_id'].'&bank_unicode='.$qurStr['bank_unicode'].'&wallet_type='.$qurStr['wallet_type'])); 
                      }             
          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Invalid duration!') );
                    $this->_redirect($this->formatURL('/reports/wallettxn?dur='.$qurStr['dur'].'&sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&product_id='.$qurStr['product_id'].'&bank_unicode='.$qurStr['bank_unicode'].'&wallet_type='.$qurStr['wallet_type'])); 
                 }    
       }
       
       
         public function wallettrialbalanceAction(){
         $this->title = 'Wallet Trial Balance';              
         // Get our form and validate it
         $form = new WalletTrialBalanceForm(array('action' => $this->formatURL('/reports/wallettrialbalance'),
                                                    'method' => 'POST',
                                             )); 
        $page = $this->_getParam('page');
        $request = $this->_getAllParams();
        $sub = $this->_getParam('sub');
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        $qurStr['product_id'] = $this->_getParam('product_id');
        $qurStr['wallet_type'] = $this->_getParam('wallet_type');
        $qurStr['sub'] = $sub;
        $qurStr['on_date']    = $this->_getParam('on_date');
      
         if($sub!=''){ 
             
              if($form->isValid($qurStr)){ 
               if ($qurStr['on_date'] != '') {
                    $qurData['from'] = Util::returnDateFormatted($qurStr['on_date'], "d-m-Y", "Y-m-d", "-", "-", 'from');
                    $qurData['to'] = Util::returnDateFormatted($qurStr['on_date'], "d-m-Y", "Y-m-d", "-", "-", 'to');
                    $qurData['product_id'] = $qurStr['product_id'];
                    $qurData['wallet_type'] = $qurStr['wallet_type'];
                    $qurData['bank_unicode'] = $qurStr['bank_unicode'];
                    $objBank = new Banks();
                    $bankInfo = $objBank->getBankbyUnicode($qurData['bank_unicode']);
                    $this->view->title = 'Wallet Trial Balance Report of '. $bankInfo->name.' for '.Util::returnDateFormatted($qurStr['on_date'], "Y-m-d", "d-m-Y", "-");
                    $this->view->on_date = $qurStr['on_date'];
                }
                 
                 $objReports = new Reports();
               
                 $custTxns = $objReports->getWalletTrialBalance($qurData);
                 
                 $this->view->reportdata = $custTxns;
                 
                 $this->view->formData = $qurStr;
                 $form->getElement('product')->setValue($qurStr['product_id']);
              }   
             
          }
          $form->getElement('product')->setValue($qurStr['product_id']);
          $this->view->form = $form;
            
    }
    
    public function exportwallettrialbalanceAction(){
        
         // Get our form and validate it
         $form = new WalletTrialBalanceForm(array('action' => $this->formatURL('/reports/wallettrialbalance'),
                                                    'method' => 'POST',
                                             )); 
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        $qurStr['on_date']  = $this->_getParam('on_date');
        $qurStr['product_id'] = $this->_getParam('product_id');
        $qurStr['wallet_type'] = $this->_getParam('wallet_type');
        
             
              if($form->isValid($qurStr)){ 
               if ($qurStr['on_date'] != '' ) {
                    $qurData['from'] = Util::returnDateFormatted($qurStr['on_date'], "d-m-Y", "Y-m-d", "-", "-", 'from');
                    $qurData['to'] = Util::returnDateFormatted($qurStr['on_date'], "d-m-Y", "Y-m-d", "-", "-", 'to');
               }
                   
                
                 $qurData['product_id'] = $qurStr['product_id'];
                 $qurData['bank_unicode'] = $qurStr['bank_unicode'];
                 $qurData['wallet_type'] = $qurStr['wallet_type'];
                 $objReports = new Reports();
                 $exportData = $objReports->getWalletTrialBalance($qurData);
               
               $csvDataArr = array(
                   
                    array('Particulars', 'Composition', 'DR / CR', 'Values in INR'),
                    array('Previous Day Balance', 'Opening Balance as on previous day', 'CR', $exportData['opening_bal']),
                    array('Wallet load/ Reload/ API Credit', 'Cummulative amount for the day', 'CR', $exportData['loads']),
                    array('Misc Wallet Credit', 'Manual Credit processed successfully for the day', 'CR', '0'),
                    array('Transaction Reversal Fee for as on date', 'Reversal Fee Break-up', 'CR', '0'),
                    array('Service Tax Reversal for as on date', 'Reversal Service Tax', 'CR', '0'),
                    array('Break Up', '', 'CR', '0'),
                    array('Wallet Load Credit (Shmart)', 'Total wallet credits excluding api credits', 'CR', '0'),
                    array('Remittance Refund Credit', '', 'CR', $exportData['refund_loads']),
//                    array('Unsettled Release Credit', '', 'CR', $exportData['reverted_loads']),
                    array('Intra Wallet Credit', '', 'CR', $exportData['intrawallet_loads']),
                    array('Fee', '', 'CR', '0'),
                    array('Service Tax', '', 'CR', '0'), 
                   
                    array('Total Transaction Credits', '', '', $exportData['total_txn_cr']),
                    array('Transaction Fee', 'Fee Income', 'DR', '0'),
                    array('Service Tax for as on date', 'Service Tax', 'DR', '0'),
                    array('Misc Wallet Debit', 'Manual Debit processed successfully for the day', 'DR', '0'),
                    array('Break Up', '', 'DR', '0'),
                    array('API Debit', '', 'DR', $exportData['debit_loads']),
                    array('Wallet Debit (Shmart)', 'Total wallet debits excluding api debits', 'DR', '0'),
                    array('Remittance Debit', '', 'DR', $exportData['debit_remit_loads']),
                    array('Intra Wallet Debit', '', 'DR', $exportData['intrawallet_loads']),
//                    array('Wallet Auto Reversal', 'Cummulative reversal amount for the day', 'CR', $exportData['reversal']),
                    array('Wallet Auto Reversal', 'Cummulative reversal amount for the day', 'CR', 0),                   
                    array('Fee Reversal', '', 'CR', '0'),
                    array('Service Tax Reversal', '', 'CR', '0'), 
                    array('Total Transaction Debits', '', '', $exportData['total_txn_dr']),
                    array('Transaction Processing', '', 'DR', $exportData['txn_dr']),
                    array('Transaction Reversal', '', 'CR', $exportData['txn_cr']),
                    array('Calculated Balance', '', '', $exportData['calculated_balance'] ),
                    array('Wallet Closing Balance', 'Actual Wallet balance for the day', '', $exportData['wallet_balance']),
                    array('Difference', '', '', $exportData['difference']),
                   );
                                  
                 $objCSV = new CSV();
                 try{
                        $resp = $objCSV->exportSpecial($csvDataArr, 'wallet_trial_balance');exit;
                 }catch (Exception $e) {
                                         App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                         $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                         $this->_redirect($this->formatURL('/reports/wallettrialbalance?on_date='.$qurStr['on_date'].'&sub=1&product_id='.$qurStr['product_id'].'&bank_unicode='.$qurStr['bank_unicode'])); 
                                       }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid Paramaters!') );
                         $this->_redirect($this->formatURL('/reports/wallettrialbalance?on_date='.$qurStr['on_date'].'&sub=1&product_id='.$qurStr['product_id'].'&bank_unicode='.$qurStr['bank_unicode'])); 
                      }             
             
       }
  
       
  
   public function walletbalanceAction() {
        $this->title = 'Wallet Balance Report';
        // Get our form and validate it
        $form = new WalletBalanceForm(array('action' => $this->formatURL('/reports/walletbalance'),
            'method' => 'POST',
        ));

        $qurStr['btn_submit'] = $this->_getParam('btn_submit');
        $qurStr['to_date'] = $this->_getParam('to_date');
        $qurStr['product_id'] = $this->_getParam('product_id');
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
	$qurStr['wallettype'] = $this->_getParam('wallettype');
        $qurStr['sub'] = $this->_getParam('sub');
        $page = $this->_getParam('page');


        if ($qurStr['btn_submit']) {
            if ($form->isValid($qurStr)) {

                if ($qurStr['to_date'] != '') {

                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-");
                    $titleDate['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                    $objBank = new Banks();
                    $bankInfo = $objBank->getBankbyUnicode($qurStr['bank_unicode']);
                    
                    $pageTitle = 'Wallet Balance Report of '.$bankInfo->name.' for ' . Util::returnDateFormatted($titleDate['to'], "Y-m-d", "d-m-Y", "-");
                } else {
                    $this->_helper->FlashMessenger(array('msg-error' => 'Invalid date range'));
                }
                //$qurData['to'] = date('Y-m-d', strtotime('-1 day', strtotime($qurData['to'])));
                $qurData['product_id'] = $qurStr['product_id'];
                $qurData['bank_unicode'] = $qurStr['bank_unicode'];
		$qurData['wallettype'] = $qurStr['wallettype'];
                $reportsModel = new Reports();
                $custArr = $reportsModel->getWalletbalance($qurData);

                $paginator = $reportsModel->paginateByArray($custArr, $page, $paginate = NULL);
                $this->view->paginator = $paginator;
                $this->view->pageTitle = $pageTitle;
                $this->view->btnSubmit = $qurStr['btn_submit'];
                $this->view->rsCount = count($custArr);
            }
        }
        $form->getElement('product')->setValue($qurStr['product_id']);
        $this->view->form = $form;
        $this->view->formData = $qurStr;
    }

    /* exportfeereportAction() is responsible to create the csv file on fly with fee data of agents
     * and let user download that file.
     */

    public function exportwalletbalanceAction() {
        $qurStr['to_date'] = $this->_getParam('to_date');
        $qurStr['product_id'] = $this->_getParam('product_id');
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
	$qurStr['wallettype'] = $this->_getParam('wallettype');
        $form = new WalletBalanceForm(array('action' => $this->formatURL('/reports/exportwalletbalance'),
            'method' => 'POST',
        ));

        if ($qurStr['to_date'] != '') {

            if ($form->isValid($qurStr)) {
                if ($qurStr['to_date'] != '') {
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-");
                }

                $qurData['product_id'] = $qurStr['product_id'];
                $qurData['bank_unicode'] = $qurStr['bank_unicode'];
		$qurData['wallettype'] = $qurStr['wallettype'];
                $reportsModel = new Reports();
                $exportData = $reportsModel->exportGetWalletbalance($qurData);

                $RatnakarBank = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR);
                $bankRatnakarUnicode = $RatnakarBank->bank->unicode;
                
                if(( $qurStr['bank_unicode'] == $bankRatnakarUnicode) ){
                    $columns = array(
                        'Product Name',
                        'Bank Name',
                        'Aadhaar No',
                        'Currency ',
                        'Card Number ',
                        'CRN',
                        'Mobile', 
                        'Medi Assist / Employee ID',
                        'Partner Reference No',
                        'Status',
                        'Corporate Id',
                        'Corporate Name',
                        'Report Date',
                        'Wallet Code',
			'Block Fund',
			'Accept Fund',
			'Release Block Fund',
                        'Balance',
                        'Total Balance'
                    );
                } else {
                    $columns = array(
                        'Product Name',
                        'Bank Name',
                        'Aadhaar No',
                        'Currency ',
                        'Card Number ',
                        'CRN',
                        'Mobile', 
                        'Member / Employee ID',
                        'Cust ID',
                        'Wallet Code',
                        'Balance',
                        'Status',
                        'Corporate Id',
                        'Corporate Name',
                        'Report Date'
                    );
                }
                
                $objCSV = new CSV();
                try {
                    $resp = $objCSV->export($exportData, $columns, 'download_reports');
                    exit;
                } catch (Exception $e) {
                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage(),));
                    $this->_redirect($this->formatURL('/reports/exportwalletbalance?to_date=' . $qurStr['to_date'] . '&product_id=' . $qurStr['product_id'] . '&bank_unicode=' . $qurStr['bank_unicode'] . '&wallettype=' . $qurStr['wallettype']));
                }
            } else {
                $this->_helper->FlashMessenger(array('msg-error' => 'Invalid data'));
                $this->_redirect($this->formatURL('/reports/exportwalletbalance?to_date=' . $qurStr['to_date'] . '&product_id=' . $qurStr['product_id'] . '&bank_unicode=' . $qurStr['bank_unicode'] . '&wallettype=' . $qurStr['wallettype']));
            }
        } else {
            $this->_helper->FlashMessenger(array('msg-error' => 'Invalid duration'));
            $this->_redirect($this->formatURL('/reports/exportwalletbalance?to_date=' . $qurStr['to_date'] . '&product_id=' . $qurStr['product_id'] . '&bank_unicode=' . $qurStr['bank_unicode'] . '&wallettype=' . $qurStr['wallettype']));
        }
    }
    
    
    public function downloadreportsAction() {
        $this->title = 'Download Reports';
        $this->view->heading = 'Download Reports';
        // Get our form and validate it
        $form = new DownloadReportsForm(array(
            'action' => $this->formatURL('/reports/downloadreports'),
            'method' => 'POST'
        ));

        $qurStr['btn_submit'] = $this->_getParam('btn_submit');
        $qurStr['from_date'] = $this->_getParam('from_date');
        $qurStr['report_name'] = $this->_getParam('report_name');
        $qurStr['sub'] = $this->_getParam('sub');
        $page = $this->_getParam('page');
        
        $fileObject = new Files();
        $type = $this->_getParam('type');
        $report_name = $this->_getParam('report_name');
        $file_name = $this->_getParam('file_name');
        
        if($type != '') {
            if($type == 'downloadreports' && $file_name != '') {
                switch ($report_name) {
                    case AGENT_BALANCE_SHEET_REPORT:
                        $dir = UPLOAD_PATH_AGENT_BALANCE_SHEET_REPORTS;
                        break;
                    case AGENT_VIRTUAL_BALANCE_SHEET_REPORT:
                        $dir = UPLOAD_PATH_AGENT_VIRTUAL_BALANCE_SHEET_REPORT;
                        break;
                    case WALLET_BALANCE_SHEET_REPORT:
                        $dir = UPLOAD_PATH_WALLET_BALANCE_REPORTS;
                        break;
                    default:
                        $dir = '';
                }
                
                if($dir != ''){
                    $fileObject->setFilepath($dir);
                    $fileObject->setFilename($file_name);
                    $fileObject->download();
                    $this->_helper->layout->disableLayout();
                    $this->_helper->viewRenderer->setNoRender(TRUE); 
                }
            } else{
                $this->_helper->FlashMessenger( array('msg-error' => 'File not found.') );                 
            }
        }
        
        if ($qurStr['btn_submit']) {           
            if ($form->isValid($qurStr)) { 
                if($qurStr['from_date'] != '') { 
                    if ($qurStr['from_date'] != '') {
                        $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", "from");
                        $titleDate['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                        $pageTitle = 'Download Report of '.Util::returnDateFormatted($titleDate['from'], "Y-m-d", "d-m-Y", "-");
                    } else {
                        $this->_helper->FlashMessenger(array('msg-error' => 'Invalid date'));
                    }
                }
                $qurData['report_name'] = $qurStr['report_name']; 
                $reportsModel = new Reports();
                $this->view->pageTitle = $pageTitle;
                $this->view->btnSubmit = $qurStr['btn_submit'];
            }
        } 
        $this->view->form = $form;
        $this->view->formData = $qurStr;
    }
    
    
    public function agentimportAction() {
        
        $this->title = "Agent Import";
        $page = $this->_getParam('page');
        $form = new AgentImportForm();
        $formData = $this->_request->getPost();
        $reqidArr = isset($formData['reqid']) ? $formData['reqid'] : '';
        $submit = isset($formData['submit_success']) ? $formData['submit_success'] : '';
        $this->view->incorrectData = FALSE;
        
        $agentimportModel = new AgentImport();  
        $objValidation = new Validator();  
        $filesModel = new Files();
        $agentsModel = new Agents();  
        $this->view->records = FALSE;
        $user = Zend_Auth::getInstance()->getIdentity();
        
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) { 
                
                $upload = new Zend_File_Transfer_Adapter_Http();
                $upload->receive();
                $name = $upload->getFileName('doc_path');
                $batchName = $upload->getFileName('doc_path', $path = FALSE) ; 

                //insert file info in t_files
                $datafiles = array();
                $datafiles['label'] = AGENT_IMPORT_FILE; 
                $datafiles['file_name'] = $batchName;
                $datafiles['ops_id'] = $user->id;
                $datafiles['status'] = STATUS_ACTIVE;
                $datafiles['date_created'] = new Zend_Db_Expr('NOW()');
                $fileId = $filesModel->insertFileInfo($datafiles);
                            
                //read and save contents of csv                
                $fp = fopen($name, 'r');
                while (!feof($fp)) {
                    $line = fgets($fp);
                    if (!empty($line)) {
                        $delimiter = AGENT_IMPORT_FILE_UPLOAD_DELIMITER;
                        $dataArr = str_getcsv($line, $delimiter);
                        $arrLength = Util::getArrayLength($dataArr);
                        if (!empty($dataArr)) {
                            if ($arrLength != 55){
                                $this->_helper->FlashMessenger(
                                    array(
                                        'msg-error' => 'Invalid Number of Columns.',
                                    )
                                );
                                $this->_redirect($this->formatURL('/reports/agentimport/'));
                            }
                                
                            try {
                                //check for valid distributor code
                                if($dataArr[0] != ''){
                                    $distchk = $agentsModel->findagentifdist($dataArr[0]);
                                }
                                
                                //check for duplicate email
                                $validateArr = array(
                                    'tablename' => DbTable::TABLE_AGENTS,
                                    'col_value' => $dataArr[5],
                                    'col_name' => 'email',
                                    'col' => 'Email',
                                    );
                                
                                if($dataArr[5] != ''){
                                  $emailchk = $objValidation->checkColDuplicacyTF($validateArr);
                                }
                                
                                //check for duplicate mobile number
                                $validateAr = array(
                                    'tablename' => DbTable::TABLE_AGENTS,
                                    'col_value' => $dataArr[6],
                                    'col_name' => 'mobile1',
                                    'col' => 'Mobile Number',
                                    );
                                
                                if($dataArr[6] != ''){
                                  $mobilechk = $objValidation->checkColDuplicacyTF($validateAr);
                                }
                                
                                //set status and failed messages
                                if($dataArr[5] == ''){
                                    $dataArr['import_status'] = STATUS_FAILED;
                                    $dataArr['failed_message'] = 'Invalid Email';
                                }elseif($emailchk == FALSE){
                                    $dataArr['import_status'] = STATUS_DUPLICATE;
                                    $dataArr['failed_message'] = 'Duplicate Email';
                                }elseif($dataArr[6] == '') {
                                    $dataArr['import_status'] = STATUS_FAILED;
                                    $dataArr['failed_message'] = 'Invalid Mobile Number';
                                }elseif ($mobilechk == FALSE) {
                                    $dataArr['import_status'] = STATUS_DUPLICATE;
                                    $dataArr['failed_message'] = 'Duplicate Mobile Number';
                                }elseif ($distchk == '') {
                                    $dataArr['import_status'] = STATUS_FAILED;
                                    $dataArr['failed_message'] = 'Invalid Distributor Code';
                                }else{
                                    $dataArr['import_status'] = STATUS_TEMP;
                                    $dataArr['failed_message'] = '';
                                }
                                
                                $dataArr['file_id'] = $fileId;
                                $agentimportModel->insertAgentImport($dataArr, $batchName);
                                
                                $consolidateArr[] = $dataArr;
                                
                            } catch (Exception $e) {
                                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                            }

                            $this->view->paginator = $agentimportModel->showPendingAgentDetails($fileId, $page, $paginate = NULL);
                        } else {
//                            $this->_helper->FlashMessenger(array('msg-error' => 'Data format is not correct'));
                        }
                    }
                }
                $this->view->records = TRUE;
                $this->view->file_name = $batchName;
                $this->view->file_id = $fileId;
                fclose($fp);
            }
        }

        if ($submit != '') {
            try {
                $status_update = STATUS_PENDING;
                $agentimportModel->bulkUpdateAgentImport($formData['reqid'],$status_update);
                
                $this->_helper->FlashMessenger(
                        array(
                            'msg-success' => 'Agent details have been updated in our records',
                        )
                );
                $this->_redirect($this->formatURL('/reports/agentimport/'));
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
    
    
    public function searchagentimportAction() {
        
        $this->title = "Search Agent Import";
        $page = $this->_getParam('page');
        $form = new SearchAgentImportForm(array('action' => $this->formatURL('/reports/searchagentimport'),
            'method' => 'POST',
        ));
        
        $agentimportModel = new AgentImport();  
        
        $qurStr['btn_submit'] = $this->_getParam('btn_submit');
        $qurStr['to_date'] = $this->_getParam('to_date');
        $qurStr['from_date'] = $this->_getParam('from_date');
        $qurStr['file_name'] = $this->_getParam('file_name');
        $qurStr['email'] = $this->_getParam('email');
        $qurStr['mobile'] = $this->_getParam('mobile');
        $qurStr['distributor_code'] = $this->_getParam('distributor_code');
        $qurStr['import_status'] = $this->_getParam('import_status');
        $page = $this->_getParam('page');


        if($qurStr['btn_submit']) { 
            if ($form->isValid($this->getRequest()->getPost())) {
                
                if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {

                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", "to");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", "from");
                    $titleDate['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                    $titleDate['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                    $pageTitle = 'Agent Import Report from ' . Util::returnDateFormatted($titleDate['from'], "Y-m-d", "d-m-Y", "-");
                    $pageTitle .= ' to ' . Util::returnDateFormatted($titleDate['to'], "Y-m-d", "d-m-Y", "-");
                }
                
                $qurData['file_name'] = $qurStr['file_name'];
                $qurData['email'] = $qurStr['email'];
                $qurData['mobile'] = $qurStr['mobile'];
                $qurData['distributor_code'] = $qurStr['distributor_code'];
                $qurData['import_status'] = $qurStr['import_status'];
                
                $dataArr = $agentimportModel->getagentimportreport($qurData);
                $paginator = $agentimportModel->paginateByArray($dataArr, $page, $paginate = NULL);
                $this->view->paginator = $paginator;
                $this->view->pageTitle = $pageTitle;
                $this->view->btnSubmit = $qurStr['btn_submit'];
            }
        }
        //$form->getElement('product')->setValue($qurStr['product_id']);
        $this->view->form = $form;
        $this->view->formData = $qurStr;
    
    } 
    
    
    public function exportsearchagentimportAction() {
       
        $agentimportModel = new AgentImport();  
        
        $qurStr['btn_submit'] = $this->_getParam('btn_submit');
        $qurStr['sub'] = $this->_getParam('sub');
        $qurStr['to_date'] = $this->_getParam('to_date');
        $qurStr['from_date'] = $this->_getParam('from_date');
        $qurStr['file_name'] = $this->_getParam('file_name');
        $qurStr['email'] = $this->_getParam('email');
        $qurStr['mobile'] = $this->_getParam('mobile');
        $qurStr['distributor_code'] = $this->_getParam('distributor_code');
        $qurStr['import_status'] = $this->_getParam('import_status');
        //$page = $this->_getParam('page');
        
        $form = new SearchAgentImportForm(array('action' => $this->formatURL('/reports/searchagentimport'),
            'method' => 'POST',
        ));

            if($qurStr['sub'] != '') {
                if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", "to");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", "from");
                }

                $qurData['file_name'] = $qurStr['file_name'];
                $qurData['email'] = $qurStr['email'];
                $qurData['mobile'] = $qurStr['mobile'];
                $qurData['distributor_code'] = $qurStr['distributor_code'];
                $qurData['import_status'] = $qurStr['import_status'];
                
                $exportData = $agentimportModel->exportgetagentimportreport($qurData);

                $columns = array(
                    'Distributor Code',
                    'Title',
                    'First Name',
                    'Middle Name',
                    'Last Name',
                    'Email',
                    'Mobile',
                    'Mobile 2',
                    'Institution Name', 
                    'Centre ID',
                    'Terminal ID 1',
                    'Terminal ID 2',
                    'Terminal ID 3',
                    'Education Level',
                    'Matric School Name',
                    'Intermediate School Name',
                    'Graduation Degree',
                    'Graduation College',
                    'Post Graduation Degree',
                    'Post Graduation College',
                    'Other Degree',
                    'Other College',
                    'Date of Birth',
                    'Gender',
                    'Identification Type',
                    'Identification Number',
                    'Passport Expiry',
                    'Address Proof Type',
                    'Address Proof Number',
                    'PAN Number',
                    'Establishment Name',
                    'Establishment Address1',
                    'Establishment Address2',
                    'Establishment City',
                    'Establishment Taluka',
                    'Establishment District',
                    'Establishment State',
                    'Establishment Country',
                    'Establishment Pincode',
                    'Residence Name',
                    'Residence Address1',
                    'Residence Address2',
                    'Residence City',
                    'Residence Taluka',
                    'Residence District',
                    'Residence State',
                    'Residence Country',
                    'Residence Pincode',
                    'Bank Name',
                    'Bank Account Number',
                    'Bank Location',
                    'Bank City',
                    'Bank IFSC Code',
                    'Linked Branch Id',
                    'Bank Area',
                    'Status',
                    'Failed Message',
                );
                
              
                $objCSV = new CSV();
                try {
                    $resp = $objCSV->export($exportData, $columns, 'agent_import_reports');
                    exit;
                } catch (Exception $e) {
                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage(),));
                    $this->_redirect($this->formatURL('/reports/searchagentimport?to_date=' . $qurStr['to_date'] . '&from_date=' . $qurStr['from_date'] . '&file_name=' . $qurStr['file_name'] . '&email=' . $qurStr['email'] . '&mobile=' . $qurStr['mobile'] . '&distributor_code=' . $qurStr['distributor_code']. '&import_status=' . $qurStr['import_status']));
                }
            } else {
                $this->_helper->FlashMessenger(array('msg-error' => 'Invalid data'));
                $this->_redirect($this->formatURL('/reports/searchagentimport?to_date=' . $qurStr['to_date'] . '&from_date=' . $qurStr['from_date'] . '&file_name=' . $qurStr['file_name'] . '&email=' . $qurStr['email'] . '&mobile=' . $qurStr['mobile'] . '&distributor_code=' . $qurStr['distributor_code']. '&import_status=' . $qurStr['import_status']));
            }
       
    }
    
     
    public function beneregistrationAction() {
        $this->title = 'Beneficiary Registration Report';
        // Get our form and validate it
        $form = new BeneficiaryRegistrationForm(array('action' => $this->formatURL('/reports/beneregistration'),
            'method' => 'POST',
        ));

        $page = $this->_getParam('page');
        $request = $this->_getAllParams();
        $sub = $this->_getParam('sub');
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['sub'] = $sub;
        $qurStr['to_date']    = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');

        if ($sub != '') {
            if ($form->isValid($qurStr)) {
 
                if ($qurStr['dur'] != '') {
                 $durationArr = Util::getDurationDates($qurStr['dur']);
                 $qurData['from'] = $durationArr['from'];
                 $qurData['to'] = $durationArr['to'];
                 $fromDate = explode(' ', Util::returnDateFormatted($durationArr['from'], "d-m-Y", "Y-m-d", "-"));
                 $toDate = explode(' ', Util::returnDateFormatted($durationArr['to'], "d-m-Y", "Y-m-d", "-"));
                 $this->view->from = $fromDate[0];
                 $this->view->to   = $toDate[0];                 
                 $pageTitle = $this->getReportTitle('Beneficiary Registration Report', $qurStr['dur'],0,FALSE,$qurStr['bank_unicode']);
                 
                } elseif ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {

                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", "to");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", "from");
                    $titleDate['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                    $titleDate['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                    $objBank = new Banks();
                    $bankInfo = $objBank->getBankbyUnicode($qurStr['bank_unicode']);
                    
                    $pageTitle = 'Beneficiary Registration Report of '.$bankInfo->name.' from ' . Util::returnDateFormatted($titleDate['from'], "Y-m-d", "d-m-Y", "-");
                    $pageTitle .= ' to ' . Util::returnDateFormatted($titleDate['to'], "Y-m-d", "d-m-Y", "-");
                } else {
                    $this->_helper->FlashMessenger(array('msg-error' => 'Invalid date range'));
                }

                $qurData['bank_unicode'] = $qurStr['bank_unicode'];
                $reportsModel = new Reports();
                $dataArr = $reportsModel->getBeneficiaryRegistrations($qurData);

                $paginator = $reportsModel->paginateByArray($dataArr, $page, $paginate = NULL);
                $this->view->paginator = $paginator;
                $this->view->pageTitle = $pageTitle;
                $this->view->btnSubmit = $qurStr['sub'];
            }
        }
        $this->view->form = $form;
        $this->view->formData = $qurStr;
    }

    public function exportbeneregistrationAction()
    {
        $qurStr['to_date'] = $this->_getParam('to_date');
        $qurStr['from_date'] = $this->_getParam('from_date');
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        $form = new BeneficiaryRegistrationForm(array('action' => $this->formatURL('/reports/exportbeneregistration'),
            'method' => 'POST',
        ));

        if ($form->isValid($qurStr)) {
            if ($qurStr['dur'] != '') {
                $durationArr = Util::getDurationDates($qurStr['dur']);
                $qurData['from'] = $durationArr['from'];
                $qurData['to'] = $durationArr['to'];
            } elseif ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", "to");
                $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", "from");
            }

            $qurData['bank_unicode'] = $qurStr['bank_unicode'];
            $reportsModel = new Reports();
            $exportData = $reportsModel->exportGetBeneficiaryRegistrations($qurData);

            $columns = array(
                'Name',
                'Nick Name',
                'Mobile',
                'IFSC Code',
                'Bank Account Number',
                'Bank Name',
                'Branch Name',
                'Branch City',
                'Bank Account Type',
                'Email',
                'Status'
            );

            $objCSV = new CSV();
            try {
                $resp = $objCSV->export($exportData, $columns, 'download_reports');
                exit;
            } catch (Exception $e) {
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage(),));
                $this->_redirect($this->formatURL('/reports/exportbeneregistration?to_date=' . $qurStr['to_date'] . '&from_date=' . $qurStr['from_date']));
            }
        } else {
            $this->_helper->FlashMessenger(array('msg-error' => 'Invalid data'));
            $this->_redirect($this->formatURL('/reports/exportbeneregistration?to_date=' . $qurStr['to_date'] . '&from_date=' . $qurStr['from_date']));
        }
    }
    
        
    /* Start: function in which we are getting agent details of current month.
     */
    public function agentremittanceAction(){
           $this->title = 'Agent Remittance Report';              
           // Get our form and validate it
           $form = new AgentRemittanceForm(array('action' => $this->formatURL('/reports/agentremittance'),
                                                      'method' => 'POST',
                                               )); 
          $page = $this->_getParam('page');
          $request = $this->_getAllParams();
          $sub = $this->_getParam('sub');
          $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
          $qurStr['product_id'] = $this->_getParam('product_id');
          $qurStr['searchCriteria'] = $this->_getParam('searchCriteria');
          $qurStr['keyword'] = $this->_getParam('keyword');
          $qurStr['sub'] = $this->_getParam('sub');

           if($sub!=''){ 
                if($form->isValid($qurStr)){ 
                    $agentModel = new Agents();
                    $agentUserModel = new AgentUser();
                    $qurData['product_id'] = $qurStr['product_id'];
                    $qurData['bank_unicode'] = $qurStr['bank_unicode'];
                    $qurData['searchCriteria'] = $qurStr['searchCriteria'];
                    $qurData['keyword'] = $qurStr['keyword'];
                    $qurData['product'] = $qurStr['product_id'];
                    
                    $bankUnicodeArr = Util::bankUnicodesArray();
                    //$agentDetailList= $agentModel->getAgentProductRemitDetails($qurData, $this->_getPage());
                   
                   
                    $this->view->paginator = $agentDetailList= $agentModel->getAgentProductRemitDetails($qurData, $this->_getPage());
                    $form->getElement('product')->setValue($qurData['product']);
                 
                    $form->populate($qurData);
                   }   

            }
            $this->view->backLink = 'searchCriteria=' . $qurStr['searchCriteria'] . '&keyword=' . $qurStr['keyword'] . '&sub=1';
            $this->view->controllerName = Zend_Registry::get('controllerName');
            $this->view->form = $form;
            $this->view->formData = $qurStr;
            $this->view->agentUser = $agentUserModel;

      }
      /* End: function in which we are getting agent details of current month.
       */
      
      /* exportagentremittanceAction() is responsible to create the csv file on fly with registered Kotak remittence agents
     * and let user download that file.
     */

    public function exportagentremittanceAction() {
          $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
          $qurStr['product_id'] = $this->_getParam('product_id');
          $qurStr['searchCriteria'] = $this->_getParam('searchCriteria');
          $qurStr['keyword'] = $this->_getParam('keyword');
          $qurStr['sub'] = $this->_getParam('sub');
       $form = new AgentRemittanceForm(array('action' => $this->formatURL('/reports/exportagentremittance'),
                                                      'method' => 'POST',
                                               )); 

        if ($qurStr['searchCriteria'] != '' && $qurStr['keyword'] != '') {

            if ($form->isValid($qurStr)) {
               

                 $qurData['product_id'] = $qurStr['product_id'];
                 $qurData['bank_unicode'] = $qurStr['bank_unicode'];
                 $qurData['searchCriteria'] = $qurStr['searchCriteria'];
                 $qurData['keyword'] = $qurStr['keyword'];
                $agentModel = new Agents();
                $exportData = $agentModel->getAgentProductRemitDetails($qurData);
                
                 $j = 0;
                
                 foreach($exportData as $data){
          	 $formattedArr[$j] = array(
                     'name' =>$data['name'] ,'email' => $data['email'],'agent_code' => $data['agent_code'],
                     'mobile1' => $data['mobile1'], 'regdate' =>$data['regdate'],'agent_limit' => $data['agent_limit'],'count' => $data['count'],'total' => $data['total'],
                     'status' => $data['status']       
                 );
                 $j++;
                 }
                $columns = array(
                   'Agent Name',
                    'Email ID',
                    'Agent Code',
                    'Mobile Number', 
                    'Registration Date',
                    'Agent Limit',
                    'Transaction Count in a Month',  
                    'Transaction Volume in a Month',
                    'Approval Status',
                );
            
                $objCSV = new CSV();
                try {
                    $resp = $objCSV->export($formattedArr, $columns, 'download_reports');
                    exit;
                } catch (Exception $e) {
                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage(),));
                    $this->_redirect($this->formatURL('/reports/exportagentremittance?bank_unicode=' . $qurStr['bank_unicode'] . '&product_id=' . $qurStr['product_id'] . '&searchCriteria=' . $qurStr['searchCriteria']. '&keyword=' . $qurStr['keyword']. '&sub=1'));
                }
            } else {
                $this->_helper->FlashMessenger(array('msg-error' => 'Invalid search value'));
                $this->_redirect($this->formatURL('/reports/exportagentremittance?bank_unicode=' . $qurStr['bank_unicode'] . '&product_id=' . $qurStr['product_id'] . '&searchCriteria=' . $qurStr['searchCriteria']. '&keyword=' . $qurStr['keyword']. '&sub=1'));
            }
        } else {
            $this->_helper->FlashMessenger(array('msg-error' => 'Invalid duration'));
            $this->_redirect($this->formatURL('/reports/exportagentremittance?bank_unicode=' . $qurStr['bank_unicode'] . '&product_id=' . $qurStr['product_id'] . '&searchCriteria=' . $qurStr['searchCriteria']. '&keyword=' . $qurStr['keyword']. '&sub=1'));
        }
    }
    
    
    
         public function remitwallettrialbalanceAction(){
         $this->title = 'Remittance Wallet Trial Balance';              
         // Get our form and validate it
         $form = new RemitWalletTrialBalanceForm(array('action' => $this->formatURL('/reports/remitwallettrialbalance'),
                                                    'method' => 'POST',
                                             )); 
        $page = $this->_getParam('page');
        $request = $this->_getAllParams();
        $sub = $this->_getParam('sub');
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        $qurStr['product_id'] = $this->_getParam('product_id');
        $qurStr['sub'] = $sub;
        $qurStr['to_date'] = $this->_getParam('to_date');
        $qurStr['from_date'] = $this->_getParam('from_date');
      
         if($sub!=''){ 
             
              if($form->isValid($qurStr)){ 
               if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", 'from');
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", 'to');
                    $qurData['product_id'] = $qurStr['product_id'];
                    $qurData['bank_unicode'] = $qurStr['bank_unicode'];
                    $objBank = new Banks();
                    $bankInfo = $objBank->getBankbyUnicode($qurData['bank_unicode']);
               $this->view->title = 'Remittance Wallet Trial Balance Report of '. $bankInfo->name.' for '.Util::returnDateFormatted($qurStr['from_date'], "Y-m-d", "d-m-Y", "-");
                }
                 
                 $objReports = new Reports();
               
                 $custTxns = $objReports->getRemitWalletTrialBalance($qurData);
                 
                 $this->view->reportdata = $custTxns;
                 
                 
              } 
              $this->view->formData = $qurStr;
            $form->getElement('product_id')->setValue($qurStr['product_id']);
            $form->getElement('bank_unicode')->setValue($qurStr['bank_unicode']);
             
          }
          $form->getElement('product')->setValue($qurStr['product_id']);
          $this->view->form = $form;
          $this->view->to_date = $qurData['to'];
            
    }
    
    public function exportremitwallettrialbalanceAction(){
        
         // Get our form and validate it
         $form = new RemitWalletTrialBalanceForm(array('action' => $this->formatURL('/reports/remitwallettrialbalance'),
                                                    'method' => 'POST',
                                             )); 
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        $qurStr['to_date'] = $this->_getParam('to_date');
        $qurStr['from_date'] = $this->_getParam('from_date');
        $qurStr['product_id'] = $this->_getParam('product_id');
        
        
             
              if($form->isValid($qurStr)){ 
               if ($qurStr['from_date'] != '' && $qurStr['to_date'] != '') {
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", 'from');
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", 'to');
               }
                   
                
                 $qurData['product_id'] = $qurStr['product_id'];
                 $qurData['bank_unicode'] = $qurStr['bank_unicode'];
                 $objReports = new Reports();
                 $exportData = $objReports->getRemitWalletTrialBalance($qurData);

		
			

               $csvDataArr = array(
                    array('Sr. No.','Particulars', 'Composition', 'DR / CR', 'Values in INR'),
                    array('A','Previous Day Balance', 'Opening Balance as on previous day', 'CR', $exportData['opening_bal']),
                    array('B','Agent Funds Approved', 'Agent Fund Received and Approved', 'CR', $exportData['agent_funds_approved']),
                    array('C','IMPS/NEFT Rejection', 'IMPS/NEFT Rejection Credit Received in Pool', 'CR', $exportData['neft_reject']),
                    array('D','Miscellaneous Cr', 'If any', 'CR', '-'),
                    array('E','Sub Total (A - D)', '', '', $exportData['sub_totalAD']),
                    array('F','Total Agent Transactions', 'IMPS Transaction Amount for the day', 'DR', $exportData['txn_total']),
                    array('H','Miscellaneous Dr', '', 'DR','-'),
                    array('I','Sub Total (F-H)', '', '', $exportData['sub_totalFH']),
                    array('J','Calculated Balance (E + I)', '', '', $exportData['sub_totalEI']),
                    array('K','Current A/C as per Bank Statement', 'Outside system', '', '-'),
                    array('','Difference (If Any)', '', '', '-'),
                    array('L','Transaction Fee for the month', 'Fee Break-up MTD', 'DR', $exportData['txn_fee']),
                    array('M','Service Tax for the month', 'Service Tax MTD', 'DR', $exportData['txn_service_tax']),
                    array('N','Transaction Reversal Fee for the month', 'Reversal Fee Break-up MTD', 'CR', $exportData['txn_reversal_fee']),
                    array('O','Service Tax Reversal for the month', 'Reversal Fee Break-up MTD', 'CR', $exportData['txn_reversal_service_tax']),
                    array('P','Sub Total (L-O)', '', '', $exportData['sub_totalLO']),
                    array('Q','All Refund Amount for the day', 'Refund made to Remitter by Agents', 'CR', $exportData['refund_amount']),
                    array('R','IMPS/NEFT Rejection + Refund for the day', 'Refund yet to claim by remitter as on date + Refund for the day', 'DR', $exportData['refund_yet_to_claim']),
                    array('S','Unprocessed NEFT transactions for the day', 'After cut-off (Batch not generated). Not applicable for IMPS', 'DR', $exportData['unprocessed_txn']),
                    array('T','Miscellaneous Cr/Dr', '', 'DR', '-'),
                    array('U','Calculated balance (K+P+Q+R+S+T)', '', '', $exportData['sub_totalU']),
                    array('','Closing Balance as per Balance Sheet Report', '', '', $exportData['closing_balance']),
                    array('','Difference', '', '', $exportData['difference']),
                   );
                                  
                 $objCSV = new CSV();
                 try{
                        $resp = $objCSV->exportSpecial($csvDataArr, 'wallet_trial_balance');exit;
                 }catch (Exception $e) {
                                         App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                         $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                         $this->_redirect($this->formatURL('/reports/remitwallettrialbalance?on_date='.$qurStr['on_date'].'&sub=1&product_id='.$qurStr['product_id'].'&bank_unicode='.$qurStr['bank_unicode'])); 
                                       }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid Paramaters!') );
                         $this->_redirect($this->formatURL('/reports/remitwallettrialbalance?on_date='.$qurStr['on_date'].'&sub=1&product_id='.$qurStr['product_id'].'&bank_unicode='.$qurStr['bank_unicode'])); 
                      }             
             
       }
  
       
    public function multiwalletbalanceAction()
        {
            $this->title = 'Multi Wallet Balance Report';
            // Get our form and validate it
            $form = new MultiWalletBalanceForm(array('action' => $this->formatURL('/reports/multiwalletbalance'),
                'method' => 'POST',
            ));

            $qurStr['btn_submit'] = $this->_getParam('btn_submit');
            $qurStr['to_date'] = $this->_getParam('to_date');
            $qurStr['from_date'] = $this->_getParam('from_date');
            $qurStr['product_id'] = $this->_getParam('product_id');
            $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
            $qurStr['sub'] = $this->_getParam('sub');
            $page = $this->_getParam('page');


            if ($qurStr['btn_submit']) {
                if ($form->isValid($qurStr)) {
                    if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                        $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", "to");
                        $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", "from");
                        $titleDate['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                        $titleDate['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                        $objBank = new Banks();
                        $bankInfo = $objBank->getBankbyUnicode($qurStr['bank_unicode']);

                        $pageTitle = 'Multi Wallet Balance Report of '.$bankInfo->name.' from ' . Util::returnDateFormatted($titleDate['from'], "Y-m-d", "d-m-Y", "-");
                        $pageTitle .= ' to ' . Util::returnDateFormatted($titleDate['to'], "Y-m-d", "d-m-Y", "-");
                    } else {
                        $this->_helper->FlashMessenger(array('msg-error' => 'Invalid date range'));
                    }
                    $qurData['product_id'] = $qurStr['product_id'];
                    $qurData['bank_unicode'] = $qurStr['bank_unicode'];
                    $reportsModel = new Reports();
                    $custArr = $reportsModel->getMultiWalletbalance($qurData);

                    $paginator = $reportsModel->paginateByArray($custArr, $page, $paginate = NULL);
                    $this->view->paginator = $paginator;
                    $this->view->pageTitle = $pageTitle;
                    $this->view->btnSubmit = $qurStr['btn_submit'];
                }
            }
            $form->getElement('product')->setValue($qurStr['product_id']);
            $this->view->form = $form;
            $this->view->formData = $qurStr;
       }
       
       public function exportmultiwalletbalanceAction() {
            $qurStr['to_date'] = $this->_getParam('to_date');
            $qurStr['from_date'] = $this->_getParam('from_date');
            $qurStr['product_id'] = $this->_getParam('product_id');
            $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
            $form = new MultiWalletBalanceForm(array('action' => $this->formatURL('/reports/exportmultiwalletbalance'),
                'method' => 'POST',
            ));

            if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {

                if ($form->isValid($qurStr)) {
                    if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                        $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", "to");
                        $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", "from");
                    }

                    $qurData['product_id'] = $qurStr['product_id'];
                    $qurData['bank_unicode'] = $qurStr['bank_unicode'];
                    $reportsModel = new Reports();
                    $exportData = $reportsModel->exportMultiWalletbalance($qurData);

                    $columns = array(
                        'Product Name',
                        'Bank Name',
                        'Medi Assist Id / Partner Ref No',
                        'Currency ',
                        'Wallet Code',
                        'Closing Balance as on date',
                        'Status'
                    );

                    $objCSV = new CSV();
                    try {
                        $resp = $objCSV->export($exportData, $columns, 'download_balance_report');
                        exit;
                    } catch (Exception $e) {
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                        $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage(),));
                        $this->_redirect($this->formatURL('/reports/exportmultiwalletbalance?to_date=' . $qurStr['to_date'] . '&from_date=' . $qurStr['from_date']));
                    }
                } else {
                    $this->_helper->FlashMessenger(array('msg-error' => 'Invalid data'));
                    $this->_redirect($this->formatURL('/reports/exportmultiwalletbalance?to_date=' . $qurStr['to_date'] . '&from_date=' . $qurStr['from_date']));
                }
            } else {
                $this->_helper->FlashMessenger(array('msg-error' => 'Invalid duration'));
                $this->_redirect($this->formatURL('/reports/exportmultiwalletbalance?to_date=' . $qurStr['to_date'] . '&from_date=' . $qurStr['from_date']));
            }
        }
        
        public function settledreportAction() {
        
        $this->title = 'Settled Report';
        
        // Get our form and validate it
        $form = new SettledReportForm(array('action' => $this->formatURL('/reports/settledreport'),
            'method' => 'POST',
        ));
        
        $formData = $this->_request->getPost();
        $page = $this->_getParam('page');
        $qurStr['card_number'] = $this->_getParam('card_number');
        $qurStr['settlement_ref_no'] = $this->_getParam('settlement_ref_no');
        $qurStr['sub'] = $this->_getParam('sub');
        $qurStr['product_id']= $this->_getParam('product_id');
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $session = new Zend_Session_Namespace('App.Operation.Controller');
        $session->items_per_page=10; 
        $to = '';
        $from = '';
        
        if($qurStr['sub'] == 1) {
             if ($form->isValid($qurStr)) {
              
               if ($qurStr['to_date'] != '' || $qurStr['from_date'] != '') {
                    
                    
                    if($qurStr['from_date'] !=''){
                     $from = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", "from");
                     $qurStr['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                    }else{
                        $from = '';
                        $qurStr['from']='';
                    }
                    if($qurStr['to_date'] !=''){
                     $to = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", "to");
                     $qurStr['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                    }else{
                        $to = '';
                        $qurStr['to']='';
                    }
                    
//                    $objBank = new Banks();
//                    $bankInfo = $objBank->getBankbyUnicode($qurStr['bank_unicode']);
                    $this->view->title = 'Settled Report for '.Util::returnDateFormatted($qurStr['from'], "Y-m-d", "d-m-Y", "-");
                    $this->view->title .= ' to '.Util::returnDateFormatted($qurStr['to'], "Y-m-d", "d-m-Y", "-");
                    $this->view->from = $qurStr['from_date'];
                    $this->view->to   = $qurStr['to_date'];
                }else{
                    $this->view->title = 'Settled Report';
                    
                }
            
                $objSettled = new Corp_Ratnakar_SettlementResponse();
                $sql = $objSettled->searchSettledRecords(array(
                    'product_id' => $qurStr['product_id'],
                    'card_number' => $qurStr['card_number'],
                    'settlement_ref_no' => $qurStr['settlement_ref_no'],
                    'to_date' => $to,
                    'from_date' => $from,
                    ));

                $this->view->paginator = $objSettled->paginateByArray($sql, $page, $paginate = NULL);
                $form->getElement('card_number')->setValue($qurStr['card_number']);
                $form->getElement('settlement_ref_no')->setValue($qurStr['settlement_ref_no']);
                $form->getElement('sub')->setValue($qurStr['sub']);
                $form->getElement('product_id')->setValue($qurStr['product_id']);
                $this->view->sub = $qurStr['sub'];
                $this->view->title = $this->title;
        }
        }
        $this->view->form = $form;
        $this->view->formData = $formData;
    }
    
    public function exportsettledreportAction() {
        $qurStr['card_number'] = $this->_getParam('card_number');
        $qurStr['settlement_ref_no'] = $this->_getParam('settlement_ref_no');
        $qurStr['sub'] = $this->_getParam('sub');
        $qurStr['product_id']= $this->_getParam('product_id');
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
            $form = new SettledReportForm(array('action' => $this->formatURL('/reports/exportsettledreport'),
                'method' => 'POST',
            ));

           

                if ($form->isValid($qurStr)) {
                        if ($qurStr['to_date'] != '' || $qurStr['from_date'] != '') {

                             if($qurStr['from_date'] !=''){
                                $qurData['from_date'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", "from");
                            }else{
                                  $qurStr['from_date']='';
                            }
                               
                            if($qurStr['to_date'] !=''){
                                $qurData['to_date'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", "to");
                            }else{
                                  $qurStr['to_date']='';
                            }
                       }

                    $qurData['product_id'] = $qurStr['product_id'];
                    $qurData['card_number'] = $qurStr['card_number'];
                    $qurData['settlement_ref_no'] = $qurStr['settlement_ref_no'];
                    
                    $settledModel = new Corp_Ratnakar_SettlementResponse();
                    $exportData = $settledModel->exportsearchSettledRecords($qurData);

                    $columns = array(
                    'Transaction Date & Time',
                    'Product Name',
                    'Bank Name',
                    'Agent/Corporate Code',
                    'Agent/Corporate Name',
//                    'Card Pack Id',
                    'Card Number',
                    'Member ID/CRN',
                    'Shmart Transaction code',
                    'Transaction Amount',    
                    'Transaction Type',
                    'Transaction Status',
                    'Wallet Code',
                    'Cr/Dr',
                    'RRN No',
                    'Acknowledge No.',
                    'Decline Reason',
                    'MCC',
                    'TID',
                    'MID',
                    'Channel',
                    'Reversal Flag',
                    'Reversal Date', 
                    'Mode',
                    'Transaction Narration',
                    'Settlement Flag',
                    'Settlement date',
                    'Benf. A/c No',
                    'Benf. A/c Name',
                    'Response file Reference Number'
                );

                $objCSV = new CSV();
                    try {
                        $resp = $objCSV->export($exportData, $columns, 'settled_report');
                        exit;
                    } catch (Exception $e) {
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                        $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage(),));
                        $this->_redirect($this->formatURL('/reports/settledreport?to_date=' . $qurStr['to_date'] . '&from_date=' . $qurStr['from_date'].'&product_id='.$qurStr['product_id'].'&card_number='.$qurStr['card_number'].'&settlement_ref_no='.$qurStr['settlement_ref_no'].'&sub=1'));
                    }
                } else {
                    $this->_helper->FlashMessenger(array('msg-error' => 'Invalid data'));
                    $this->_redirect($this->formatURL('/reports/settledreport?to_date=' . $qurStr['to_date'] . '&from_date=' . $qurStr['from_date'].'&product_id='.$qurStr['product_id'].'&card_number='.$qurStr['card_number'].'&settlement_ref_no='.$qurStr['settlement_ref_no'].'&sub=1'));
                }
           
        }
     
        
     public function unsettledreportAction() {
        
        $this->title = 'Unsettled Report';
        
        // Get our form and validate it
        $form = new SettledReportForm(array('action' => $this->formatURL('/reports/unsettledreport'),
            'method' => 'POST',
        ));
        
        $formData = $this->_request->getPost();
        $page = $this->_getParam('page');
        $qurStr['card_number'] = $this->_getParam('card_number');
        $qurStr['settlement_ref_no'] = $this->_getParam('settlement_ref_no');
        $qurStr['sub'] = $this->_getParam('sub');
        $qurStr['product_id']= $this->_getParam('product_id');
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $session = new Zend_Session_Namespace('App.Operation.Controller');
        $session->items_per_page=10; 
        
        if($qurStr['sub'] == 1) {
             if ($form->isValid($qurStr)) {
//              $durationArr = Util::getDurationDates($qurStr['dur']);
//                   if ($qurStr['dur'] != '') {
//                    //$fromDate =  Util::returnDateFormatted($durationArr['from'], "d-m-Y", "Y-m-d", "-");
//                    //$toDate = Util::returnDateFormatted($durationArr['to'], "d-m-Y", "Y-m-d", "-");
//                    $fromDate = $durationArr['from'];
//                    $toDate = $durationArr['to'];
//                    $this->view->title = $this->getReportTitle('Settled Report', $qurStr['dur'],0,FALSE,$qurStr['bank_unicode']);
//                    $this->view->from = $fromDate;
//                    $this->view->to   = $toDate;  
//                    $from = $fromDate;
//                    $to = $toDate; 
//                 
//                }
//                
                
                 if($qurStr['to_date'] !=''){
                     $to = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", "to");
                     $qurStr['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                    }else{
                        $to = '';
                        $qurStr['to']='';
                    }
                    
                     if($qurStr['from_date'] !=''){
                     $from = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", "from");
                     $qurStr['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                    }else{
                        $from = '';
                        $qurStr['from']='';
                    }
                   
                    $objBank = new Banks();
//                    $bankInfo = $objBank->getBankbyUnicode($qurStr['bank_unicode']);
                    $this->view->title = 'Settled Report of '.$bankInfo->name.' for '.Util::returnDateFormatted($qurStr['from'], "Y-m-d", "d-m-Y", "-");
                    $this->view->title .= ' to '.Util::returnDateFormatted($qurStr['to'], "Y-m-d", "d-m-Y", "-");
                    $this->view->from = $qurStr['from_date'];
                    $this->view->to   = $qurStr['to_date'];
                
                $objSettled = new Corp_Ratnakar_SettlementResponse();
                $sql = $objSettled->searchUnSettledRecords(array(
                    'product_id' => $qurStr['product_id'],
                    'card_number' => $qurStr['card_number'],
                    'settlement_ref_no' => $qurStr['settlement_ref_no'],
                    'to_date' => $to,
                    'from_date' => $from,
                    ));
                     
                $this->view->paginator = $objSettled->paginateByArray($sql, $page, $paginate = NULL);
                $form->getElement('card_number')->setValue($qurStr['card_number']);
                $form->getElement('settlement_ref_no')->setValue($qurStr['settlement_ref_no']);
                $form->getElement('sub')->setValue($qurStr['sub']);
                $form->getElement('product_id')->setValue($qurStr['product_id']);
                $this->view->sub = $qurStr['sub'];
                $this->view->title = $this->title;
        }
        }
        $this->view->form = $form;
        $this->view->formData = $formData;
    }
    
        public function exportunsettledreportAction() {
        $qurStr['card_number'] = $this->_getParam('card_number');
        $qurStr['settlement_ref_no'] = $this->_getParam('settlement_ref_no');
        $qurStr['sub'] = $this->_getParam('sub');
        $qurStr['product_id']= $this->_getParam('product_id');
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
            $form = new SettledReportForm(array('action' => $this->formatURL('/reports/exportunsettledreport'),
                'method' => 'POST',
            ));

           

                if ($form->isValid($qurStr)) {
                    if ($qurStr['to_date'] != '' || $qurStr['from_date'] != '') {

                        if($qurStr['from_date'] !=''){
                            $qurData['from_date'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", "from");
                        }else{
                              $qurStr['from_date']='';
                        }

                        if($qurStr['to_date'] !=''){
                            $qurData['to_date'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", "to");
                        }else{
                              $qurStr['to_date']='';
                        }
                    }

                    $qurData['product_id'] = $qurStr['product_id'];
                    $qurData['card_number'] = $qurStr['card_number'];
                    $qurData['settlement_ref_no'] = $qurStr['settlement_ref_no'];
                    
                    $settledModel = new Corp_Ratnakar_SettlementResponse();
                    $exportData = $settledModel->exportsearchUnSettledRecords($qurData);

                    $columns = array(
                    'Transaction Date & Time',   
                    'Product Name',
                    'Bank Name',
                    'Agent/Corporate Code',
                    'Agent/Corporate Name',
//                    'Card Pack Id',
                    'Card Number',
                    'Member ID/CRN',
                    'Shmart Transaction Code',
                    'Transaction Amount',
                    'Transaction Type',   
                    'Transaction Status',
                    'Wallet Code',
                    'Cr/Dr',
                    'RRN No',
                    'Acknowledge No.',
                    'Decline Reason',
                    'MCC',
                    'TID',
                    'MID',
                    'Channel',
                    'Reversal Flag',
                    'Reversal Date',   
                    'Mode',
                    'Transaction Narration',
                     'Settlement Flag',
                    
                );

                $objCSV = new CSV();
                    try {
                        $resp = $objCSV->export($exportData, $columns, 'Unsettled_report');
                        exit;
                    } catch (Exception $e) {
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                        $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage(),));
                        $this->_redirect($this->formatURL('/reports/exportunsettledreport?to_date=' . $qurStr['to_date'] . '&from_date=' . $qurStr['from_date'].'&product_id='.$qurStr['product_id'].'&card_number='.$qurStr['card_number'].'&settlement_ref_no='.$qurStr['settlement_ref_no'].'&sub=1'));
                    }
                } else {
                    $this->_helper->FlashMessenger(array('msg-error' => 'Invalid data'));
                    $this->_redirect($this->formatURL('/reports/exportunsettledreport?to_date=' . $qurStr['to_date'] . '&from_date=' . $qurStr['from_date'].'&product_id='.$qurStr['product_id'].'&card_number='.$qurStr['card_number'].'&settlement_ref_no='.$qurStr['settlement_ref_no'].'&sub=1'));
                }
           
        }

        
    public function agentvirtualfundingAction(){
        $this->title = 'Authorized Virtual Funding Report';
        $this->view->heading = 'Authorized Virtual Funding Report';
        
        $form = new RptAgentFundRequestsForm(array(
            'action' => $this->formatURL('/reports/agentvirtualfunding'),
            'method' => 'POST'
        )); 
        $request = $this->_getAllParams();
        $sub = $this->_getParam('sub');
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['sub'] = $sub;
        $qurStr['to_date']  = $this->_getParam('to_date');
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
                    $this->view->title = $this->getReportTitle('Authorized Virtual Funding Report', $qurStr['dur']);
                } else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-","-","from");
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-","-","to"); 
                    $this->view->from = $qurData['from'];
                    $this->view->to = $qurData['to']; 
                    $this->view->title = 'Authorized Virtual Funding Report for '.$qurStr['from_date'].' to '.$qurStr['to_date'] ;
                }
                $qurData['status'] = array(
                    STATUS_APPROVED 
                );
                $qurData['authorize'] = FLAG_YES;
                $objReports = new Reports();
                $this->view->paginator = $objReports->getAgentVirtualFundRequests($qurData, $this->_getPage()); 
                $this->view->formData = $qurStr;
            }             
        }
        $this->view->form = $form;
    }
    
    public function exportagentvirtualfundingAction(){
        $form = new RptAgentFundRequestsForm(array(
            'action' => $this->formatURL('/reports/agentvirtualfunding'),
            'method' => 'POST'
        ));
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        
        if($qurStr['dur']!=''|| ($qurStr['to_date'] !='' && $qurStr['from_date']!='')){ 
            if($form->isValid($qurStr)){
                if ($qurStr['dur'] != '') {
                    $durationArr = Util::getDurationDates($qurStr['dur']);
                    $qurData['from'] = $durationArr['from'];
                    $qurData['to'] = $durationArr['to']; 
                } else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') { 
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-","-","from");
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-","-","to");
                }
                $qurData['status'] = array(
                    STATUS_APPROVED 
                );
                $qurData['authorize'] = FLAG_YES;
                $objReports = new Reports();
                $exportData = $objReports->exportAgentVirtualFund($qurData);
                $columns = array(
                    'Request Date',
                    'Authorized Date',
                    'Agent Code',
                    'Agent Name',
                    'Agent Virtual Funding Amount',
                    'UTR No.',
                    'Opening Balance (for the day)',
                    'Closing Balance (for the day)',
                    'Authorized By',
                    'Transaction Ref Number'
                );
                $objCSV = new CSV();
                try{
                    $resp = $objCSV->export($exportData, $columns, 'agent_fund_requests');exit;
                }catch (Exception $e) {
                    App_Logger::log($e->getMessage() , Zend_Log::ERR);
                    $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                    $this->_redirect($this->formatURL('/reports/agentvirtualfunding?dur='.$qurStr['dur'].'&sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'])); 
                }                
            } else {
                $this->_helper->FlashMessenger( array('msg-error' => 'Invalid duration!') );
                $this->_redirect($this->formatURL('/reports/agentvirtualfunding?dur='.$qurStr['dur'].'&sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'])); 
            }  
        } else {
            $this->_helper->FlashMessenger( array('msg-error' => 'Invalid duration!') );
            $this->_redirect($this->formatURL('/reports/agentvirtualfunding?dur='.$qurStr['dur'].'&sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'])); 
        }
    }
    
    public function unauthorizevirtualfundAction(){
        $this->title = 'Unauthorized Virtual Funding Report';
        $this->view->heading = 'Unauthorized Virtual Funding Report';
        
        $form = new RptAgentFundRequestsForm(array(
            'action' => $this->formatURL('/reports/unauthorizevirtualfund'),
            'method' => 'POST'
        )); 
        $request = $this->_getAllParams();
        $sub = $this->_getParam('sub');
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['sub'] = $sub;
        $qurStr['to_date']  = $this->_getParam('to_date');
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
                    $this->view->title = $this->getReportTitle('Unauthorized Virtual Funding Report', $qurStr['dur']);
                } else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-","-","from");
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-","-","to"); 
                    $this->view->from = $qurData['from'];
                    $this->view->to = $qurData['to']; 
                    $this->view->title = 'Unauthorized Virtual Funding Report for '.$qurStr['from_date'].' to '.$qurStr['to_date'] ;
                }
                $qurData['status'] = array(
                    STATUS_REJECTED,
                    STATUS_PENDING
                );
                $qurData['authorize'] = FLAG_NO;
                $objReports = new Reports();
                $this->view->paginator = $objReports->getAgentVirtualFundRequests($qurData, $this->_getPage()); 
                $this->view->formData = $qurStr;
            }             
        }
        $this->view->form = $form;
    }
    
    public function exportunauthorizevirtualfundAction(){
        $form = new RptAgentFundRequestsForm(array(
            'action' => $this->formatURL('/reports/unauthorizevirtualfund'),
            'method' => 'POST'
        ));
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        
        if($qurStr['dur']!=''|| ($qurStr['to_date'] !='' && $qurStr['from_date']!='')){ 
            if($form->isValid($qurStr)){
                if ($qurStr['dur'] != '') {
                    $durationArr = Util::getDurationDates($qurStr['dur']);
                    $qurData['from'] = $durationArr['from'];
                    $qurData['to'] = $durationArr['to']; 
                } else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') { 
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-","-","from");
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-","-","to");
                }
                $qurData['status'] = array(
                    STATUS_REJECTED,
                    STATUS_PENDING
                );
                $qurData['authorize'] = FLAG_NO;
                $objReports = new Reports();
                $exportData = $objReports->exportUnauthVirtualFund($qurData);
                $columns = array(
                    'Request Date',
                    'Agent Code',
                    'Agent Name',
                    'Agent Virtual Funding Amount',
                    'UTR No.',
                    'Opening Balance (for the day)',
                    'Closing Balance (for the day)',
                    'status'
                );
                $objCSV = new CSV();
                try{
                    $resp = $objCSV->export($exportData, $columns, 'agent_fund_requests');exit;
                }catch (Exception $e) {
                    App_Logger::log($e->getMessage() , Zend_Log::ERR);
                    $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                    $this->_redirect($this->formatURL('/reports/unauthorizevirtualfund?dur='.$qurStr['dur'].'&sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'])); 
                }                
            } else {
                $this->_helper->FlashMessenger( array('msg-error' => 'Invalid duration!') );
                $this->_redirect($this->formatURL('/reports/unauthorizevirtualfund?dur='.$qurStr['dur'].'&sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'])); 
            }  
        } else {
            $this->_helper->FlashMessenger( array('msg-error' => 'Invalid duration!') );
            $this->_redirect($this->formatURL('/reports/unauthorizevirtualfund?dur='.$qurStr['dur'].'&sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'])); 
        }
    }
    
    
    public function virtualwalletbalanceAction() {
        $this->title = 'Virtual Wallet Balance Report';
        $this->view->heading = 'Virtual Wallet Balance Report';
        $form = new WalletVirtualBalanceForm(array(
            'action' => $this->formatURL('/reports/virtualwalletbalance'),
            'method' => 'POST',
        ));
        $qurStr['btn_submit'] = $this->_getParam('btn_submit');
        $qurStr['to_date'] = $this->_getParam('to_date');
        $qurStr['product_id'] = $this->_getParam('product_id');
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        $qurStr['sub'] = $this->_getParam('sub');
        $page = $this->_getParam('page');
        if ($qurStr['btn_submit']) {
            if ($form->isValid($qurStr)) {
                if ($qurStr['to_date'] != '') {
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-");
                    $titleDate['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                    $objBank = new Banks();
                    $bankInfo = $objBank->getBankbyUnicode($qurStr['bank_unicode']); 
                    $pageTitle = 'Virtual Wallet Balance Report of '.$bankInfo->name.' for ' . Util::returnDateFormatted($titleDate['to'], "Y-m-d", "d-m-Y", "-");
                }
                $qurData['product_id'] = $qurStr['product_id'];
                $qurData['bank_unicode'] = $qurStr['bank_unicode'];
                $reportsModel = new Reports();
                $custArr = $reportsModel->getVirtualWalletBalance($qurData);

                $paginator = $reportsModel->paginateByArray($custArr, $page, $paginate = NULL);
                $this->view->paginator = $paginator;
                $this->view->pageTitle = $pageTitle;
                $this->view->btnSubmit = $qurStr['btn_submit'];
                $this->view->rsCount = count($custArr);
            } 
        }
        $form->getElement('product')->setValue($qurStr['product_id']);
        $this->view->form = $form;
        $this->view->formData = $qurStr; 
    }
    
    public function exportvirtualwalletbalanceAction() {
        $qurStr['to_date'] = $this->_getParam('to_date');
        $qurStr['product_id'] = $this->_getParam('product_id');
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        $form = new WalletBalanceForm(array(
            'action' => $this->formatURL('/reports/virtualwalletbalance'),
            'method' => 'POST',
        ));
        if ($qurStr['to_date'] != '') {
            if ($form->isValid($qurStr)) {
                if ($qurStr['to_date'] != '') {
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-");
                }
                $qurData['product_id'] = $qurStr['product_id'];
                $qurData['bank_unicode'] = $qurStr['bank_unicode'];
                $reportsModel = new Reports();
                $exportData = $reportsModel->exportGetVirtualWalletbalance($qurData); 
                $columns = array(
                    'Product Name',
                    'Bank Name',
                    'Aadhaar No',
                    'Currency ',
                    'Card Number ',
                    'CRN',
                    'Mobile', 
                    'Medi Assist / Employee ID',
                    'Partner Reference No',
                    'Status',
                    'Corporate Id',
                    'Corporate Name',
                    'Report Date',
                    'Wallet Code',
                    'Balance',
                    'Total Balance'
                );  
                $objCSV = new CSV();
                try {
                    $resp = $objCSV->export($exportData, $columns, 'download_reports');
                    exit;
                } catch (Exception $e) {
                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage(),));
                    $this->_redirect($this->formatURL('/reports/exportvirtualwalletbalance?to_date=' . $qurStr['to_date'] . '&product_id=' . $qurStr['product_id'] . '&bank_unicode=' . $qurStr['bank_unicode']));
                }
            } else {
                $this->_helper->FlashMessenger(array('msg-error' => 'Invalid data'));
                $this->_redirect($this->formatURL('/reports/exportvirtualwalletbalance?to_date=' . $qurStr['to_date'] . '&product_id=' . $qurStr['product_id'] . '&bank_unicode=' . $qurStr['bank_unicode']));
            }
        } else {
            $this->_helper->FlashMessenger(array('msg-error' => 'Invalid duration'));
            $this->_redirect($this->formatURL('/reports/exportvirtualwalletbalance?to_date=' . $qurStr['to_date'] . '&product_id=' . $qurStr['product_id'] . '&bank_unicode=' . $qurStr['bank_unicode']));
        }
    }
        


	
	
	public function w2wtransferAction() {
	    $this->title = 'Wallet to wallet transfer Report';
	    $this->view->heading = 'Wallet to wallet transfer Report';
	    // Get our form and validate it
	    $form = new WalletToWalletTransferForm(array(
		'action' => $this->formatURL('/reports/w2wtransfer'),
		'method' => 'POST',
	    ));
	    $sub = $this->_getParam('sub');
	    $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
	    $qurStr['product_id'] = $this->_getParam('product_id');
	    $qurStr['duration'] = $this->_getParam('duration');
	    $qurStr['to_date']  = $this->_getParam('to_date');
	    $qurStr['from_date']  = $this->_getParam('from_date');
	    $page = $this->_getParam('page');
	    
	    if($sub!=''){  
		if($form->isValid($qurStr)){
		    if ($qurStr['duration'] != ''){ 
			$durationArr = Util::getDurationDates($qurStr['duration']);
			$qurData['from'] = $durationArr['from'];
			$qurData['to'] = $durationArr['to'];                 

			$fromDate = explode(' ', Util::returnDateFormatted($durationArr['from'], "d-m-Y", "Y-m-d", "-"));
			$toDate = explode(' ', Util::returnDateFormatted($durationArr['to'], "d-m-Y", "Y-m-d", "-"));
			$this->view->from = $fromDate[0];
			$this->view->to   = $toDate[0];                 
			$this->view->title = $this->getReportTitle('Wallet to wallet transfer Report', $qurStr['duration'],0,FALSE,$qurStr['bank_unicode']);
		    } else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                        $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-","-","to");
                        $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-","-","from");
                        $qurStr['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                        $qurStr['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                        $objBank = new Banks();
                        $bankInfo = $objBank->getBankbyUnicode($qurStr['bank_unicode']);
                        $this->view->title = 'Wallet to wallet transfer Report of '.$bankInfo->name.' for '.Util::returnDateFormatted($qurStr['from'], "Y-m-d", "d-m-Y", "-");
                        $this->view->title .= ' to '.Util::returnDateFormatted($qurStr['to'], "Y-m-d", "d-m-Y", "-");
                        $this->view->from = $qurStr['from'];
                        $this->view->to   = $qurStr['to'];
                        $this->view->on_page = $qurStr['on_page'];
                    }
		    $qurData['bank_unicode'] =  $qurStr['bank_unicode'];
		    $qurData['product_id'] = $qurStr['product_id'];
                    $qurData['duration'] =  $qurStr['duration'];  
		    $page = $this->_getParam('page');
		    $objReports = new Remit_Ratnakar_WalletTransfer();
		    $w2wTxns = $objReports->getListWalletTranfer($qurData);
		    $paginator = $objReports->paginateByArray($w2wTxns, $page, $paginate = NULL);
		    $this->view->paginator=$paginator;
		}
	    }
	    $form->getElement('product')->setValue($qurStr['product_id']);
            $this->view->form = $form;
            $this->view->sub = $sub;
            $this->view->formData = $qurStr;
	}
	
	public function exportw2wtransferAction(){ 
	    // Get our form and validate it
	    $form = new WalletToWalletTransferForm(array(
		'action' => $this->formatURL('/reports/w2wtransfer'),
		'method' => 'POST',
	    ));
	    $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
	    $qurStr['product_id']  = $this->_getParam('product_id');
	    $qurStr['duration'] = $this->_getParam('duration');
	    $qurStr['to_date']  = $this->_getParam('to_date');
	    $qurStr['from_date']  = $this->_getParam('from_date');
                     
	    if($form->isValid($qurStr)){
		if($qurStr['duration']!=''){ 
		    $durationArr = Util::getDurationDates($qurStr['duration']);
		    $fromDate = $durationArr['from'];
		    $toDate = $durationArr['to'];  
		} else if($qurStr['to_date']!='' && $qurStr['from_date']!=''){ 
		    $fromDate = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-","-","from");
		    $toDate = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-","-","to"); 
		}
		$queryParam = array(
		    'from' => $fromDate,
		    'to' => $toDate,
		    'bank_unicode' =>  $qurStr['bank_unicode'],
		    'product_id' =>  $qurStr['product_id'],
		    'duration' =>  $qurStr['duration']
		);
		$objReports = new Reports();
		$exportData = $objReports->exportw2wtransferdata($queryParam);
		$columns = array(
                    'Bank Name',
		    'Product Name',
		    'Agent Code',
		    'Sender Name',
		    'Sender Mobile number',
		    'Receiver Name',
		    'Receiver Mobile number',
		    'Transaction Date',
		    'Transaction Amount',
		    'Transaction Reference Number',
		    'Transaction Type',
		    'Transaction Status',
		    'Shmart Transaction refno',
		    'Block Amount'
                );
	       
                $objCSV = new CSV();
		try{
		    $resp = $objCSV->export($exportData, $columns, 'w2wtransfer_reports');exit;
		} catch (Exception $e) {
		    App_Logger::log($e->getMessage() , Zend_Log::ERR);
		    $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
		    $this->_redirect($this->formatURL('/reports/w2wtransfer?to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&sub=1&bank_unicode='.$qurStr['bank_unicode'].'&product_id='.$qurStr['product_id'].'&duration='.$qurStr['duration']));
		}
	    } else {
		$this->_helper->FlashMessenger( array('msg-error' => 'Invalid data found') );
		$this->_redirect($this->formatURL('/reports/w2wtransfer?to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&sub=1&bank_unicode='.$qurStr['bank_unicode'].'&product_id='.$qurStr['product_id'].'&duration='.$qurStr['duration'])); 
	    }
	}
	
	public function wwftexceptionsAction() {
	    $this->title = 'Wallet Transfer Exceptions Report';
	    $this->view->heading = 'Wallet Transfer Exceptions Report';
	    // Get our form and validate it
	    $form = new WalletToWalletTransferForm(array(
		'action' => $this->formatURL('/reports/wwftexceptions'),
		'method' => 'POST',
	    ));
	    $sub = $this->_getParam('sub');
	    $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
	    $qurStr['product_id'] = $this->_getParam('product_id');
	    $qurStr['duration'] = $this->_getParam('duration');
	    $qurStr['to_date']  = $this->_getParam('to_date');
	    $qurStr['from_date']  = $this->_getParam('from_date');
	    $page = $this->_getParam('page');
	    
	    if($sub!=''){  
		if($form->isValid($qurStr)){
		    if ($qurStr['duration'] != ''){ 
			$durationArr = Util::getDurationDates($qurStr['duration']);
			$qurData['from'] = $durationArr['from'];
			$qurData['to'] = $durationArr['to'];                 

			$fromDate = explode(' ', Util::returnDateFormatted($durationArr['from'], "d-m-Y", "Y-m-d", "-"));
			$toDate = explode(' ', Util::returnDateFormatted($durationArr['to'], "d-m-Y", "Y-m-d", "-"));
			$this->view->from = $fromDate[0];
			$this->view->to   = $toDate[0];                 
			$this->view->title = $this->getReportTitle('Wallet to wallet transfer Report', $qurStr['duration'],0,FALSE,$qurStr['bank_unicode']);
		    } else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                        $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-","-","to");
                        $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-","-","from");
                        $qurStr['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                        $qurStr['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                        $objBank = new Banks();
                        $bankInfo = $objBank->getBankbyUnicode($qurStr['bank_unicode']);
                        $this->view->title = 'Wallet to wallet transfer Report of '.$bankInfo->name.' for '.Util::returnDateFormatted($qurStr['from'], "Y-m-d", "d-m-Y", "-");
                        $this->view->title .= ' to '.Util::returnDateFormatted($qurStr['to'], "Y-m-d", "d-m-Y", "-");
                        $this->view->from = $qurStr['from'];
                        $this->view->to   = $qurStr['to'];
                        $this->view->on_page = $qurStr['on_page'];
                    }
		    $qurData['status'] =  STATUS_IN_PROCESS;
		    $qurData['bank_unicode'] =  $qurStr['bank_unicode'];
		    $qurData['product_id'] = $qurStr['product_id'];
                    $qurData['duration'] =  $qurStr['duration'];  
		    $page = $this->_getParam('page');
		    $objReports = new Remit_Ratnakar_WalletTransfer();
		    $w2wTxns = $objReports->getListWalletTranfer($qurData);
		    $paginator = $objReports->paginateByArray($w2wTxns, $page, $paginate = NULL);
		    $this->view->paginator=$paginator;
		}
	    }
	    $form->getElement('product')->setValue($qurStr['product_id']);
            $this->view->form = $form;
            $this->view->sub = $sub;
            $this->view->formData = $qurStr;
	}
	
	public function exportwwftexceptionsAction(){ 
	    // Get our form and validate it
	    $form = new WalletToWalletTransferForm(array(
		'action' => $this->formatURL('/reports/wwftexceptions'),
		'method' => 'POST',
	    ));
	    $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
	    $qurStr['product_id']  = $this->_getParam('product_id');
	    $qurStr['duration'] = $this->_getParam('duration');
	    $qurStr['to_date']  = $this->_getParam('to_date');
	    $qurStr['from_date']  = $this->_getParam('from_date');
	       
	    if($form->isValid($qurStr)){
		if($qurStr['duration']!=''){ 
		    $durationArr = Util::getDurationDates($qurStr['duration']);
		    $fromDate = $durationArr['from'];
		    $toDate = $durationArr['to'];  
		} else if($qurStr['to_date']!='' && $qurStr['from_date']!=''){ 
		    $fromDate = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-","-","from");
		    $toDate = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-","-","to"); 
		}
		$queryParam = array(
		    'from' => $fromDate,
		    'to' => $toDate,
		    'bank_unicode' =>  $qurStr['bank_unicode'],
		    'product_id' =>  $qurStr['product_id'],
		    'duration' =>  $qurStr['duration'],
		    'status' =>  STATUS_IN_PROCESS
		);
		$objReports = new Reports();
		$exportData = $objReports->exportw2wtransferdata($queryParam);
		$columns = array(
                    'Bank name',
		    'Product name',
		    'Agent Code',
		    'Sender name',
		    'Sender Mobile number',
		    'Receiver Name',
		    'Receiver Mobile number',
		    'Transaction date',
		    'Transaction amount',
		    'Transaction reference number',
		    'Transaction type',
		    'Transaction status'
                );
	       
                $objCSV = new CSV();
		try{
		    $resp = $objCSV->export($exportData, $columns, 'wallettransferexceptions_reports');exit;
		} catch (Exception $e) {
		    App_Logger::log($e->getMessage() , Zend_Log::ERR);
		    $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
		    $this->_redirect($this->formatURL('/reports/wwftexceptions?to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&sub=1&bank_unicode='.$qurStr['bank_unicode'].'&product_id='.$qurStr['product_id'].'&duration='.$qurStr['duration']));
		}
	    } else {
		$this->_helper->FlashMessenger( array('msg-error' => 'Invalid data found') );
		$this->_redirect($this->formatURL('/reports/wwftexceptions?to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&sub=1&bank_unicode='.$qurStr['bank_unicode'].'&product_id='.$qurStr['product_id'].'&duration='.$qurStr['duration'])); 
	    }
	}
	
    }
