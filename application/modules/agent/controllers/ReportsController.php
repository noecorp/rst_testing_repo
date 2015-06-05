<?php
/**
 * Allows user to see reports
 *
 * @category backoffice
 * @package backoffice_controllers
 * @copyright company
 */

class ReportsController extends App_Agent_Controller
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
    
    /**
     * Controller's entry point
     *
     * @access public
     * @return void
     */
    public function indexAction(){
       
    }
    private function getReportTitle($reportTitle, $dur, $agentId = 0, $singleDayOnly=false)
    {
        $title = $reportTitle;
        if($agentId > 0)
        {
            $objAgent = new Agents();
            $agentInfo = $objAgent->findById($agentId);
            //$title .= ' For '. $agentInfo->name;
            
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
                    $title .= ' (' .$fromDate[0]. ' to '.$toDate[0].')';break;
            }
      } else{
            //$dt = explode(' ', Util::returnDateFormatted($dur, "Y-m-d", "d-m-Y", "-"));;
            $title .= ' For '.$dur;
      }
        
        return $title;
        
    }
     
       /* agentfundrequestsAction function is responsible to find agent fund requests info and display
        */
        public function agentfundrequestsAction(){
         $this->title = 'Authorized Funding Report';              
         // Get our form and validate it
         $form = new RptAgentFundRequestsForm(array('action' => $this->formatURL('/reports/agentfundrequests'),
                                                    'method' => 'POST',
                                             ));
        $user = Zend_Auth::getInstance()->getIdentity();
  
        $request = $this->_getAllParams();
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['to_date'] = $this->_getParam('to_date');
        $qurStr['from_date'] = $this->_getParam('from_date');
        $qurStr['sub'] = $this->_getParam('sub');
        
        
         if($qurStr['sub']!=''){ 
              $formData  = $this->_request->getPost();
              if($form->isValid($qurStr)){ 
                   if ($qurStr['dur'] != '') {
                 $durationArr = Util::getDurationDates($qurStr['dur']);
                 $qurData['from'] = $durationArr['from'];
                 $qurData['to'] = $durationArr['to'];
                 
                 $fromDate = explode(' ', Util::returnDateFormatted($durationArr['from'], "d-m-Y", "Y-m-d", "-"));
                 $toDate = explode(' ', Util::returnDateFormatted($durationArr['to'], "d-m-Y", "Y-m-d", "-"));
                 $this->view->from = $fromDate[0];
                 $this->view->to   = $toDate[0];                 
                 $this->view->title = $this->getReportTitle('Agent Authorized Funding Report for', $qurStr['dur'], $qurData['agent_id']);
                 } 
                   else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-","-","to");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-","-","from");
                    $from = Util::returnDateFormatted($qurData['from'], "Y-m-d", "d-m-Y", "-");
                    $to = Util::returnDateFormatted($qurData['to'], "Y-m-d", "d-m-Y", "-");
                    $fromArr = explode(" ",$from);
                    $toArr = explode(" ",$to);
                    $this->view->title = 'Agent Authorized Funding Report for '.$fromArr[0];
	            $this->view->title .= ' to '.$toArr[0];
                    $this->view->from = $qurData['from'];
                    $this->view->to   = $qurData['to'];
                }
                $qurData['agent_id'] = $user->id;
                 $objReports = new Reports();
                 $this->view->paginator = $objReports->getAgentWiseFundRequests($qurData, $this->_getPage());
                 
              }
                 $this->view->formData = $qurStr;
                 
          }
            $this->view->form = $form;
            
    }
    
    
    /* exportagentfundrequestsAction function is responsible to create the csv file on fly with agent fund requests report data
     * and let user download that file.
     */
    
     public function exportagentfundrequestsAction(){
        
        // Get our form and validate it
         $form = new RptAgentFundRequestsForm(array('action' => $this->formatURL('/reports/agentfundrequests'),
                                                    'method' => 'POST',
                                             )); 
         
        $user = Zend_Auth::getInstance()->getIdentity();
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['to_date'] = $this->_getParam('to_date');
        $qurStr['from_date'] = $this->_getParam('from_date');
        
         if(($qurStr['dur']!=''|| ($qurStr['to_date'] !='' && $qurStr['from_date']!=''))){ 
             
              if($form->isValid($qurStr)){ 
                  if ($qurStr['dur'] != '') { 
                 $durationArr = Util::getDurationDates($qurStr['dur']);
                 $qurData['from'] = $durationArr['from'];
                 $qurData['to'] = $durationArr['to'];
                 } else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                   
                }
                 $qurData['agent_id'] = $user->id;
                 
                 $objReports = new Reports();
                 $exportData = $objReports->exportAgentWiseFundRequestsFromAgent($qurData);
                
                 $columns = array(
                                    'Transaction Date',
                                    'Agent Code',
                                    'Agent Name',
                                    'Opening Balance (for the day)',
                                    'Total Agent Funding Amount (Authorized)',
                                    'Remarks (Agent Comments)'
                                    //'Closing Balance', 
                                 );
                                  
                 $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'agent_fund_requests');exit;
                 } 
                 catch (Exception $e) {
                    App_Logger::log($e->getMessage() , Zend_Log::ERR);
                    $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
//                                            $this->_redirect('/reports/agentfundrequests?dur='.$qurStr['dur'].'&sub=1'); 
                    $this->_redirect($this->formatURL('/reports/agentfundrequests?dur='.$qurStr['dur'].'&sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'])); 
                }
                 
               } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Invalid data found!') );
//                         $this->_redirect('/reports/agentfundrequests?dur='.$qurStr['dur'].'&sub=1'); 
                    $this->_redirect($this->formatURL('/reports/agentfundrequests?dur='.$qurStr['dur'].'&sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'])); 
                 }             
          } else {
            $this->_helper->FlashMessenger( array('msg-error' => 'Duration missing!') );
//                    $this->_redirect('/reports/agentfundrequests?dur='.$qurStr['dur'].'&sub=1'); 
            $this->_redirect($this->formatURL('/reports/agentfundrequests?dur='.$qurStr['dur'].'&sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'])); 
         }    
       }
  
           
     /*
     * Agent wise Consolidated Fee report for
     */
    public function feereportAction(){
        $this->title = 'Fee Report'; 
        $user = Zend_Auth::getInstance()->getIdentity();
         // Get our form and validate it
        $form = new FeeReportForm(array('action' => $this->formatURL('/reports/feereport'),
                                                    'method' => 'POST',
                                         )); 
        $qurStr['duration'] = $this->_getParam('duration');
        $qurStr['agent_id'] = $user->id;
        $qurStr['btn_submit'] = $this->_getParam('btn_submit');
        $qurStr['submit'] = $this->_getParam('submit');
        $qurStr['bank_unicode'] = $user->bank_unicode;
        $page = $this->_getParam('page');
        
         
         if($qurStr['btn_submit']){             
              if($form->isValid($qurStr)){ 
                 
                 $pageTitle = $this->getReportTitle('Fee Report', $qurStr['duration']);
                 
                 $durationArr = Util::getDurationDates($qurStr['duration']);
                 $qurData['from'] = $durationArr['from'];
                 $qurData['to'] = $durationArr['to'];  
                 $qurData['agent_id'] = $qurStr['agent_id'];  
                 $qurData['check_fee'] = FALSE;
                 $qurData['bank_unicode'] =  $qurStr['bank_unicode']; 
                 $objFeeReport = new FeePlan();
                 $feeArr = $objFeeReport->getAgentFeeArray($qurData);
                 $paginator = $objFeeReport->paginateByArray($feeArr, $page, $paginate = NULL);
                 $this->view->paginator = $paginator;
                 $this->view->pageTitle = $pageTitle;
                 $this->view->btnSubmit = $qurStr['btn_submit'];
               }   
               
          }
            $this->view->form = $form;
            $this->view->formData = $qurStr; 
            
    }
       
     
       
            /*exportagentwisefeereportAction function is responsible to create the csv file on fly with agent fee data
     * and let user download that file.
     */
    
     public function exportfeereportAction(){
        $user = Zend_Auth::getInstance()->getIdentity();
        $qurStr['duration'] = $this->_getParam('duration');
        $qurStr['agent_id'] = $user->id;
        $qurStr['bank_unicode'] = $user->bank_unicode;
        $form = new FeeReportForm(array('action' => $this->formatURL('/reports/exportfeereport'),
                                              'method' => 'POST',
                                       )); 
        
         if($qurStr['duration']!=''){    
            
              if($form->isValid($qurStr)){ 
                 $durationArr = Util::getDurationDates($qurStr['duration']);
                 $qurData['from'] = $durationArr['from'];
                 $qurData['to'] = $durationArr['to'];  
                 $qurData['agent_id'] = $this->_getParam('agent_id');
                 $qurData['check_fee'] = FALSE;
                 $qurData['bank_unicode'] =  $qurStr['bank_unicode']; 
                 $objFeeReport = new FeePlan();
                 $exportData = $objFeeReport->getAgentFeeArray($qurData);
               
                 $columns = array(
                                    'Date',
                                    'Agent Code',
                                    'Agent Name',
                                    'Agent City',
                                    'Agent Pincode', 
                                    'Transaction Ref Number',
                                    'Transaction Narration',
                                    'Transaction Amount',
                                    'Product Code',
                                    //'Fee Plan',
                                    'Fee',
                                    'Service Tax',
                                    'Reversal Fee',
                                    'Reversal Service Tax'
                                 );
                                  
                 $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'agent_fee');exit;
                 }catch (Exception $e) {
                                         App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                         $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                         $this->_redirect($this->formatURL('/reports/feereport?duration='.$qurStr['duration'].'&sub=1&agent_id='.$qurStr['agent_id'].'&bank_unicode='.$qurStr['bank_unicode'])); 
                                       }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid data') );
                         $this->_redirect($this->formatURL('/reports/feereport?duration='.$qurStr['duration'].'&sub=1&agent_id='.$qurStr['agent_id'].'&bank_unicode='.$qurStr['bank_unicode'])); 
                      }             
          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Invalid duration') );
                    $this->_redirect($this->formatURL('/reports/feereport?duration='.$qurStr['duration'].'&sub=1&agent_id='.$qurStr['agent_id'].'&bank_unicode='.$qurStr['bank_unicode'])); 
                 }    
       }
       
       
       
       
    /* agentsummaryAction function will show the agent load,reload, remitter, remittance, refund, including service tax & fee and counts for all
     */
    public function agentsummaryAction()
    {   
        $this->title = 'Agent Transaction Summary Report';
        $user = Zend_Auth::getInstance()->getIdentity();
    
       // Get our form and validate it
        $form = new AgentSummaryForm(array('action' => $this->formatURL('/reports/agentsummary'),
                                           'method' => 'POST',
                                    )); 
        
        $request = $this->getRequest();  
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['sub'] = $this->_getParam('sub');
        $qurStr['bank_unicode'] = $user->bank_unicode;
         
         if($qurStr['sub']!=''){             
              if($form->isValid($qurStr)){ 
                 $qurData['agent_id'] = $user->id;
                 $durationArr = Util::getDurationDates($qurStr['dur']);
                 $qurData['duration'] = $qurStr['dur'];
                 $qurData['bank_unicode'] = $qurStr['bank_unicode'];
                 $fromDate = explode(' ', Util::returnDateFormatted($durationArr['from'], "d-m-Y", "Y-m-d", "-"));
                 $toDate = explode(' ', Util::returnDateFormatted($durationArr['to'], "d-m-Y", "Y-m-d", "-"));
                
                 $this->view->sub = $qurStr['sub'];
                 $this->view->title = $this->getReportTitle('Agent Transaction Summary Report', $qurStr['dur'], $qurData['agent_id']);
                 $page = $this->_getParam('page');
                
                 $objReports = new Reports();
                 $agentSummary = $objReports->getAgentSummary($qurData);
                 
                 $paginator = $objReports->paginateByArray($agentSummary, $page, $paginate = NULL);
                 $this->view->paginator=$paginator;
                
              } 
          }
            $this->view->form = $form;
            $this->view->formData = $qurStr; 
     } 
       
       
        
    /* exportagentsummaryAction function is responsible to create the csv file on fly with agent load,reload, remitter, remittance, refund, 
     * with service tax and fee and counts for all report data and let user download that file.
     */
    
     public function exportagentsummaryAction(){
        
        // Get our form and validate it
        $form = new AgentSummaryForm(); 
        $qurStr['dur'] = $this->_getParam('dur');
        $user = Zend_Auth::getInstance()->getIdentity();      
        $qurStr['bank_unicode'] = $user->bank_unicode;
         if($qurStr['dur']!=''){             
              if($form->isValid($qurStr)){ 
                 $qurData['agent_id'] = $user->id;
                 $durationArr = Util::getDurationDates($qurStr['dur']);
                 $qurData['duration'] = $qurStr['dur'];
                 $qurData['bank_unicode'] = $qurStr['bank_unicode'];
                 $agentInfo = Array();
                        
                 $objReports = new Reports();
                 $exportData = $objReports->exportAgentSummaryFromAgent($qurData);
                 $columns = array(
                                    'Transaction Date',
                                    'Agent Name',
                                    'Agent Code',        
                                    'Card Loads Count',
                                    'Card Loads Amount',
                                    'Card Reloads Count',
                                    'Card Reloads Amount',
                                    'Remitters Registration Count',
                                    'Remitters Registration Amount',
                                    'Remittance Count',
                                    'Remittance Amount',  
                                    'Remittance Refund Count',
                                    'Remittance Refund Amount',   
                                    
                                 );
                 

                 $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'agent_summary');exit;
                 } catch (Exception $e) {
                                            App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                            $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                            $this->_redirect($this->formatURL('/reports/agentsummary?dur='.$qurStr['dur'].'&sub=1&bank_unicode='.$qurStr['bank_unicode'])); 
                                        }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid data found') );
                         $this->_redirect($this->formatURL('/reports/agentsummary?dur='.$qurStr['dur'].'&sub=1&bank_unicode='.$qurStr['bank_unicode'])); 
                      }             
          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Data missing') );
                    $this->_redirect($this->formatURL('/reports/agentsummary?dur='.$qurStr['dur'].'&sub=1&bank_unicode='.$qurStr['bank_unicode'])); 
                 }    
       }
       
       
       
       /* agentcommissionsummaryAction function is responsible to generate agent commission summary report
        * on duration and agent id(optional) basis
        */
       
        public function agentcommissionsummaryAction(){
         $this->title = 'Commission Summary Report';                         
         // Get our form and validate it
         $form = new AgentCommissionSummaryForm(array('action' => $this->formatURL('/reports/agentcommissionsummary'),
                                                    'method' => 'POST',
                                             )); 
  
        $request = $this->_getAllParams();
        $user = Zend_Auth::getInstance()->getIdentity();
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['sub'] = $this->_getParam('sub');
        $qurStr['bank_unicode'] = $user->bank_unicode;
        $page = $this->_getParam('page');
        
        if($qurStr['sub']!=''){
              if($form->isValid($qurStr)){ 
                 $qurStr['id']  = $user->id;
                 $queryParam = array(
                                        'agent_id'=>$qurStr['id'],
                                        'duration'=>$qurStr['dur'],
                                        'bank_unicode' => $qurStr['bank_unicode']
                                    );
                 
                 $this->view->title = $this->getReportTitle('Commission Summary Report', $qurStr['dur'], $queryParam['agent_id']);
                 
                 $objReports = new Reports();
                 $rptData = $objReports->getAgentCommissionSummary($queryParam);
                 
                 $paginator = $objReports->paginateByArray($rptData, $page, $paginate = NULL);
                 $this->view->paginator = $paginator;
                 $this->view->formData = $qurStr;
                 $cronDuration = App_DI_Container::get('ConfigObject')->cron->commission_report;
                 $cronId = CRON_COMMISSION_REPORT_ID;
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
        $user = Zend_Auth::getInstance()->getIdentity();
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['bank_unicode'] = $user->bank_unicode;      
         if($qurStr['dur']!=''){             
              if($form->isValid($qurStr)){ 
                 $qurData['agent_id'] = $qurStr['id'] = $user->id;
                 $durationArr = Util::getDurationDates($qurStr['dur']);
                 $qurData['duration'] = $qurStr['dur'];
                 $qurData['bank_unicode'] = $qurStr['bank_unicode'];
                 $agentInfo = Array();
                 //$qurData['from'] = $durationArr['from'];
                 //$qurData['to'] = $durationArr['to'];                 
                 
                        
                 $objReports = new Reports();
                 $exportData = $objReports->exportAgentCommissionSummary($qurData);
                 $columns = array(
                                    'Transaction Date',
                                    'Agent Name',
                                    'Agent Code',        
                                    'City',
                                    'Pin Code',
                                    'Card Load/Reload Amount',
                                    'Card Load/Reload Commission Amount',        
                                    'Remittance Amount',
                                    'Remittance Commission Amount'
                                 );
                 

                 $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'agent_commission_summary');exit;
                 } catch (Exception $e) {
                                            App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                            $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                            $this->_redirect($this->formatURL('/reports/exportagentcommissionsummary?dur='.$qurStr['dur'].'&sub=1&id='.$qurStr['id'].'&bank_unicode='.$qurStr['bank_unicode'])); 
                                        }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid data found') );
                         $this->_redirect($this->formatURL('/reports/exportagentcommissionsummary?dur='.$qurStr['dur'].'&sub=1&id='.$qurStr['id'].'&bank_unicode='.$qurStr['bank_unicode'])); 
                      }             
          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Data missing') );
                    $this->_redirect($this->formatURL('/reports/exportagentcommissionsummary?dur='.$qurStr['dur'].'&sub=1&id='.$qurStr['id'].'&bank_unicode='.$qurStr['bank_unicode'])); 
                 }    
       }
       
       /* 
        * balancesheetAction generates agent balance sheet for specific period
        */
       public function balancesheetAction(){
	       	$this->title = 'Balance Sheet';                       
	       	$user = Zend_Auth::getInstance()->getIdentity();
	       	$form = new AgentBalanceSheetForm(array('action' => $this->formatURL('/reports/balancesheet'),
	                                                    'method' => 'POST',
	                                             )); 
	  			
	  	$request = $this->_getAllParams();
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
	                 $this->view->title = $this->getReportTitle('Balance Sheet', $qurStr['dur']);
	                 $durationDates = Util::getDurationAllDates($qurStr['dur']);
	                } else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
	                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
	                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
	                    $durationDates = Util::getDurationRangeAllDates($qurData);
	                    $this->view->title = 'Balance Sheet for '.Util::returnDateFormatted($qurData['from'], "Y-m-d", "d-m-Y", "-");
	                    $this->view->title .= ' to '.Util::returnDateFormatted($qurData['to'], "Y-m-d", "d-m-Y", "-");
	                    $this->view->from = $qurData['from'];
	                    $this->view->to   = $qurData['to'];
	                }
	                  
	                 $objReports = new Reports();
	                 $rptData = $objReports->getAgentBalanceSheet($durationDates, $user->id);
	                 $paginator = $objReports->paginateByArray($rptData, $page, $paginate = NULL);
	                 
	                 $this->view->paginator = $paginator;
	                 $this->view->formData = $qurStr;
	              } 
	        }
	         
	        $this->view->form = $form;
       	
       }
       
       /* exportbalancesheetAction function creates csv file for agent balance sheet report data
     */
    
     public function exportbalancesheetAction(){
     	$user = Zend_Auth::getInstance()->getIdentity();
     	  
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $qurStr['dur'] = $this->_getParam('dur');
        $form = new AgentBalanceSheetForm(array('action' => $this->formatURL('/reports/balancesheet'),
                                              'method' => 'POST',
                                       )); 
             
          if($form->isValid($qurStr)){ 
           if ($qurStr['dur'] != '') {
            $durationDates = Util::getDurationAllDates($qurStr['dur']);
            } else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                 $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                $durationDates = Util::getDurationRangeAllDates($qurData);
                
            }
             $objReports = new Reports();
             $exportData = $objReports->exportAgentBalanceSheetAgent($durationDates,$user->id);
                    
             $columns = array(
                                'Date',
                                'Opening Balance',
                                'Total Agent Funding Amount (Authorized)',
                                'Unauthorized Fund Request Amount',
                                'Total Transaction Amount', 
                                'Total Refund Transaction Amount', 
                                'Total Fee', 
                                'Total Service Tax', 
                                'Total Service Tax Reversal', 
                                'Total Fee Reversal', 
                                'Closing Balance',
	'Commission',
                                'Commission Reversal',
                             );
                              
             $objCSV = new CSV();
             try{
                 
                    $resp = $objCSV->export($exportData, $columns, 'agent_balance_sheet');exit;
             }catch (Exception $e) {
                                     App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                     $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                     $this->_redirect($this->formatURL('/reports/balancesheet?sub=1&dur='.$qurStr['dur'].'&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'])); 
                                   }
             
           } else {
                     $this->_helper->FlashMessenger( array('msg-error' => 'Invalid duration!') );
                     $this->_redirect($this->formatURL('/reports/balancesheet?sub=1&dur='.$qurStr['dur'].'&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'])); 
           }             
    
       }
       
  /* exportdailytxnAction function is responsible to create the csv file on fly with agent daily transactions sheet report data
     * and let user download that file.
     */
    
     public function exportdailytxnAction(){
     	 $this->title = 'Account Activity Report';
        $user = Zend_Auth::getInstance()->getIdentity();
        
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $qurStr['dur'] = $this->_getParam('dur');
        $form = new AgentBalanceSheetForm(array('action' => $this->formatURL('/reports/balancesheet'),
                                              'method' => 'POST',
                                       )); 
          
        if ($qurStr['dur'] != '') {
         $durationDates = Util::getDurationAllDates($qurStr['dur']);
         $fromToDates = Util::getDurationDates($qurStr['dur'],FALSE);
         $qurStr['from_date'] = Util::returnDateFormatted($fromToDates['from'], "Y-m-d", "d-m-Y", "-");
         $qurStr['to_date'] = Util::returnDateFormatted($fromToDates['to'], "Y-m-d", "d-m-Y", "-");
         } else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
             $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
             $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
             $durationDates = Util::getDurationRangeAllDates($qurData);

         }
        
        $agentName = $user->first_name." ".$user->last_name;
        $objReports = new Reports();
        $rptData = $objReports->getAgentDailyTxn($durationDates, $user->id, $agentName);
        
        if(count($rptData))
        {      
            $i=0;        
            $csvData=array();
            $csvData[$i]['date_created']= $qurStr['from_date'];
            $csvData[$i]['agent_name']= $agentName;
            $csvData[$i]['txn_type']= "Opening Balance";
            $csvData[$i]['narration']= "Opening Balance";
            $csvData[$i]['txn_code']= "";
            $csvData[$i]['amount']=	"";
            $csvData[$i]['mode']= "";
            $csvData[$i]['balance']= Util::numberFormat($rptData[0]['opening_bal']);
            $i++;
            $closingBal=$rptData[0]['opening_bal'];
            foreach($rptData as $data){
                    $csvData[$i]['date_created']= Util::returnDateFormatted($data['date_created'], "Y-m-d", "d-m-Y", "-");
                    $csvData[$i]['agent_name']=	$agentName;
                    $csvData[$i]['txn_type']= $data['txn_type'];
                    $csvData[$i]['narration']= $data['narration'];
                    $csvData[$i]['txn_code']= $data['txn_code'];
                    $csvData[$i]['amount']= Util::numberFormat($data['amount']);
                    $csvData[$i]['mode']= ucfirst($data['mode']);
                    if(strtolower($data['mode']) == 'cr') {
                        $data['balance'] = $closingBal + $data['amount'];
                        $csvData[$i]['balance']= Util::numberFormat($data['balance']);
                    } else {
                        $data['balance'] = $closingBal - $data['amount'];
                        $csvData[$i]['balance']= Util::numberFormat($data['balance']);
                    }
                    $closingBal = $data['balance'];
                    $i++;
            }
            $csvData[$i]['date_created'] = $qurStr['to_date'];
            $csvData[$i]['agent_name'] = $agentName;
            $csvData[$i]['txn_type'] = "Closing Balance";
            $csvData[$i]['narration'] = "Closing Balance";
            $csvData[$i]['txn_code'] = "";
            $csvData[$i]['amount'] =	"";
            $csvData[$i]['mode'] = "";
            $csvData[$i]['balance'] = Util::numberFormat($closingBal);
             $columns = array(
                            'Transaction Date',
                            'Agent Name',
                            'Transaction Type',
                            'Narration', 	
                            'Transaction Ref No',
                            'Amount',
                            'Dr/Cr',
                            'Balance', 
                             );
                              
             $objCSV = new CSV();
             try{
                    $objCSV->export($csvData, $columns, 'agent_details_balance_sheet');exit;
             }catch (Exception $e) {
                App_Logger::log($e->getMessage() , Zend_Log::ERR);
                $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                $this->_redirect($this->formatURL('/reports/balancesheet?from_date='.$qurStr['from_date'].'&to_date='.$qurStr['to_date'])); 
              }
             
           } else {
                     $this->_helper->FlashMessenger( array('msg-error' => 'No Transactions!') );
                     $this->_redirect($this->formatURL('/reports/balancesheet?from_date='.$qurStr['from_date'].'&to_date='.$qurStr['to_date'])); 
           }             
    
       }
      
	
        public function bclistingAction(){
         $this->title = 'Application Status Report';              
         // Get our form and validate it
//         $form = new BcListingForm(array('action' => $this->formatURL('/reports/bclisting'),
//                                                    'method' => 'POST',
//                                             )); 
        $user = Zend_Auth::getInstance()->getIdentity();
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
        $qurStr['agent_id'] = $user->id;
      
             
                          
                 $this->view->title = $this->getReportTitle('Training Center BC Listing', $qurStr['dur'],0,FALSE,$qurStr['bank_unicode']);
                 
              
                 $qurData['product_id'] = $qurStr['product_id'];
                 $qurData['bank_unicode'] = $qurStr['bank_unicode'];
                 $qurData['agent_id'] = $qurStr['agent_id'];
                 $objReports = new Reports();
                 $bclist = $objReports->getBcListDetails($qurData);
                 $this->view->paginator = $objReports->paginateByArray($bclist, $page, $paginate = NULL);
                 $this->view->formData = $qurStr;
             
             
          
            $this->view->form = $form;
            
    }
    /* exportcustomerregistrationAction function is responsible to create the csv file on fly with agent fund requests report data
     * and let user download that file.
     */
    
     public function exportbclistingAction(){
        
         // Get our form and validate it
//         $form = new BcListingForm(array('action' => $this->formatURL('/reports/bclisting'),
//                                                    'method' => 'POST',
//                                             )); 
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        $qurStr['product_id'] = $this->_getParam('product_id');
        $qurStr['agent_id'] = $this->_getParam('agent_id');
        
             
                 $qurData['product_id'] = $qurStr['product_id'];
                 $qurData['bank_unicode'] = $qurStr['bank_unicode'];
                 $qurData['agent_id'] = $qurStr['agent_id'];
                 $objReports = new Reports();
                 $exportData = $objReports->exportgetBcListDetails($qurData);
// column names & indexes
                  $columns = array(
            'Institution Name',
            'Center ID',
            'Linked BOI Branch ID',
            'Terminal ID 1',
            'Terminal ID 2',
            'Terminal ID 3',
            'Name',
            'Email',
            'Mobile',
        );


        $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'training_center_bc_list');exit;
                 }catch (Exception $e) {
                                         App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                         $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                         $this->_redirect($this->formatURL('/corp_boi_reports/customerregistration?dur='.$qurStr['dur'].'&sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&product_id='.$qurStr['product_id'].'&bank_unicode='.$qurStr['bank_unicode'].'&status='.$qurStr['status'].'&ref_num='.$qurStr['ref_num'].'&agent_id='.$qurStr['agent_id'].'&nsdc_enrollment_no='.$qurStr['nsdc_enrollment_no'])); 
                                       }
                 
                      
           
       }
       
       
        /* agentfundrequestsAction function is responsible to find agent fund requests info and display
        */
        public function agentfundingAction(){
         $this->title = 'Partner Funding Report';              
         // Get our form and validate it
         $form = new AgentFundingReportForm(array('action' => $this->formatURL('/reports/agentfunding'),
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
                    $this->view->title = 'Partner Funding Report for '.$fromArr[0];
	            $this->view->title .= ' to '.$toArr[0];
                    $this->view->from = $qurData['from'];
                    $this->view->to   = $qurData['to'];
                }
                 $objReports = new Reports();
                 $fundingDetails = $objReports->getAgentFunding($durationDates, $user->id);
                 $this->view->paginator = $objReports->paginateByArray($fundingDetails, $page, $paginate);
              }
                 $this->view->formData = $qurStr;
                 
          }
            $this->view->form = $form;
            
    }
    
    
    /* exportagentfundrequestsAction function is responsible to create the csv file on fly with agent fund requests report data
     * and let user download that file.
     */
    
     public function exportagentfundingAction(){
        
        // Get our form and validate it
         $form = new AgentFundingReportForm(array('action' => $this->formatURL('/reports/agentfundrequests'),
                                                    'method' => 'POST',
                                             )); 
         
        $user = Zend_Auth::getInstance()->getIdentity();
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['to_date'] = $this->_getParam('to_date');
        $qurStr['from_date'] = $this->_getParam('from_date');
        
        
             
              if($form->isValid($qurStr)){ 
                if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                    $durationDates = Util::getDurationRangeAllDates($qurData);$from = Util::returnDateFormatted($qurData['from'], "Y-m-d", "d-m-Y", "-");
                    
                }
                 $qurData['agent_id'] = $user->id;
                 
                 $objReports = new Reports();
                 $fundingDetails = $objReports->getAgentFunding($durationDates, $user->id);
                
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
                        $resp = $objCSV->export($fundingDetails, $columns, 'partner_funding');exit;
                 } 
                 catch (Exception $e) {
                    App_Logger::log($e->getMessage() , Zend_Log::ERR);
                    $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                    $this->_redirect($this->formatURL('/reports/agentfunding?sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'])); 
                }
                 
               } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Invalid data found!') );
                    $this->_redirect($this->formatURL('/reports/agentfunding?sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'])); 
                 }             
            
       }
		
       
    public function w2wtransferAction() {
	$this->title = 'Wallet to wallet transfer Report';
	$this->view->heading = 'Wallet to wallet transfer Report';
	$user = Zend_Auth::getInstance()->getIdentity(); 
	$userModel = new Agents();
	$agentProducts = $userModel->getAgentBinding($user->id,date("Y-m-d")); 
	$prdList = array('' => 'All products');
	foreach ($agentProducts as $prds){
	    $prdList[$prds['product_id']] = $prds['product_name'];
	}
	// Get our form and validate it
	$form = new Wallet2WalletTransferAgentForm(array(
	    'action' => $this->formatURL('/reports/w2wtransfer'),
	    'method' => 'POST',
	));
	$form->getElement('product_id')->addmultiOptions($prdList);
	$sub = $this->_getParam('sub'); 
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
		    $this->view->title = $this->getReportTitle('Wallet to wallet transfer Report for ', $qurStr['duration'],0,FALSE,'');
		} else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
		    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-","-","to");
		    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-","-","from");
		    $qurStr['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
		    $qurStr['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-"); 
		    $this->view->title = 'Wallet to wallet transfer Report for '.Util::returnDateFormatted($qurStr['from'], "Y-m-d", "d-m-Y", "-");
		    $this->view->title .= ' to '.Util::returnDateFormatted($qurStr['to'], "Y-m-d", "d-m-Y", "-");
		    $this->view->from = $qurStr['from'];
		    $this->view->to   = $qurStr['to'];
		    $this->view->on_page = $qurStr['on_page'];
		} 
		$qurData['product_id'] = $qurStr['product_id'];
		$qurData['duration'] =  $qurStr['duration'];
		$qurData['agent_id'] =  $user->id;
		$page = $this->_getParam('page'); 
		$objReports = new Remit_Ratnakar_WalletTransfer();
		$w2wTxns = $objReports->getListWalletTranfer($qurData);
		$paginator = $objReports->paginateByArray($w2wTxns, $page, $paginate = NULL);
		$this->view->paginator=$paginator;
	    }
	}
	$this->view->form = $form;
	$this->view->sub = $sub;
	$this->view->formData = $qurStr;
    }
       
    public function exportw2wtransferAction(){
	$user = Zend_Auth::getInstance()->getIdentity(); 
	$userModel = new Agents();
	$agentProducts = $userModel->getAgentBinding($user->id,date("Y-m-d")); 
	$prdList = array('' => 'All products');
	foreach ($agentProducts as $prds){
	    $prdList[$prds['product_id']] = $prds['product_name'];
	}
	// Get our form and validate it
	$form = new Wallet2WalletTransferAgentForm(array(
	    'action' => $this->formatURL('/reports/w2wtransfer'),
	    'method' => 'POST',
	));
	$form->getElement('product_id')->addmultiOptions($prdList); 
	
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
		'product_id' =>  $qurStr['product_id'],
		'duration' =>  $qurStr['duration'],
		'agent_id' => $user->id
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
		'Receiver Mobile Number',
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
		$this->_redirect($this->formatURL('/reports/w2wtransfer?to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&sub=1&product_id='.$qurStr['product_id'].'&duration='.$qurStr['duration']));
	    }
	} else {
	    $this->_helper->FlashMessenger( array('msg-error' => 'Invalid data found') );
	    $this->_redirect($this->formatURL('/reports/w2wtransfer?to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&sub=1&product_id='.$qurStr['product_id'].'&duration='.$qurStr['duration'])); 
	}
    }
    
    
}