<?php
/**
 * MVC Axis Bank Reports
 *
 * @package frontend_controllers
 * @copyright company
 */

class Mvc_Axis_ReportsController extends App_Operation_Controller
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
    
    /* Agent Load Reload Report: load / reload report for all agents
     * takes duration as argument, currently yesterday / today / WTD / MTD
     */
    public function agentloadreloadAction(){
       $this->title = 'Axis Load Reload Report';  
       // Get our form and validate it
        $form = new Mvc_Axis_AgentLoadReloadForm(); 
        $request = $this->getRequest();  
        $sub = $this->_getParam('sub');
        $qurStr['from_date'] = $this->_getParam('from_date');
        $qurStr['to_date'] = $this->_getParam('to_date');
        $qurStr['id'] = $this->_getParam('id');
        $qurStr['agent_id'] = $this->_getParam('id'); 
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        
         if($sub!=''){    
             
              if($form->isValid($qurStr)){ 
                 $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-","-","from");
                 $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-","-","to");
                 $qurData['id'] = $qurStr['id']; 
                 $qurData['agent_id'] = $qurStr['agent_id']; 
                 $qurData['bank_unicode'] = $qurStr['bank_unicode'];  
                 $objBank = new Banks();
                $bankInfo = $objBank->getBankbyUnicode($qurStr['bank_unicode']);
                $this->view->title = 'Load Reload of '.$bankInfo->name.' for '.$qurStr['from_date'];
                $this->view->title .= ' to '.$qurStr['to_date'];
                $this->view->from = $qurStr['from_date'];
                $this->view->to   = $qurStr['to_date'];
                 $objReports = new Mvc_Reports();
                 $this->view->paginator = $objReports->getAgentLoadReload($qurData, $this->_getPage());
               }   
               
          }
            $this->view->form = $form;
            $this->view->formData = $qurStr; 
    }
    
    /* agentwiseloadAction function will search agent to cardholder loads and show 
     * the result to ops 
     */
    public function agentwiseloadAction()
    {   
        $this->title = 'Axis Agent Wise Load Report';
        // Get our form and validate it
        $form = new Mvc_Axis_AgentWiseLoadForm(); 
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
                 $qurData['from'] = $durationArr['from'];
                 $qurData['to'] = $durationArr['to'];
                 
                 
                 $objAgent = new Agents();
                 $agentInfo = $objAgent->findById($qurData['agent_id']);
                 
                 $fromDate = explode(' ', Util::returnDateFormatted($durationArr['from'], "d-m-Y", "Y-m-d", "-"));
                 $toDate = explode(' ', Util::returnDateFormatted($durationArr['to'], "d-m-Y", "Y-m-d", "-"));
                
                 //$this->view->agent_name = $agentInfo->name;
                 $this->view->agentInfo = $agentInfo;
                 
                 $this->view->from = $fromDate[0];
                 $this->view->to   = $toDate[0];  
                 $this->view->title = $this->getReportTitle('Load/Reload Report', $qurStr['dur'], $qurData['agent_id']);
                 
                $objReports = new Mvc_Reports();
                $this->view->paginator = $objReports->getAgentWiseLoads($qurData, $this->_getPage());
              } 
          }
            $this->view->form = $form;
            $this->view->formData = $qurStr; 
            //$this->view->btnSubmit = $btnSubmit;
            $this->view->callingRprtDur = $qurStr['dur'];
            $this->view->src = $src;
        }        
         
       
    
    
    
    
     public function cardholderactivationsAction(){
         $this->title = 'Card Activation Report';
         // Get our form and validate it
        $form = new Mvc_Axis_CardholderActivationsForm(); 
  
        $request = $this->_getAllParams();
        $sub = $this->_getParam('sub');
        $qurStr['dur'] = $this->_getParam('dur');
//        $qurStr['sub'] = $sub;
        
         if($sub!=''){ 
             
              if($form->isValid($qurStr)){ 
                 $durationArr = Util::getDurationDates($qurStr['dur']);
                 $qurData['from'] = $durationArr['from'];
                 $qurData['to'] = $durationArr['to'];
                 $fromDate = explode(' ', Util::returnDateFormatted($durationArr['from'], "d-m-Y", "Y-m-d", "-"));
                 $toDate = explode(' ', Util::returnDateFormatted($durationArr['to'], "d-m-Y", "Y-m-d", "-"));
                 $this->view->from = $fromDate[0];
                 $this->view->to   = $toDate[0];                 
                 $this->view->title = $this->getReportTitle('Card Activation Report', $qurStr['dur']);
                 
                 $objReports = new Mvc_Reports();
                 $this->view->paginator = $objReports->getCardholderActivations($qurData, $this->_getPage());
                 $this->view->formData = $qurStr; 
                 //$this->view->agent_name = $agentInfo->name;
                 
              }                  
          }
            $this->view->form = $form;
            //$this->view->formData = $qurStr;
            //$this->view->formData = $formData; 
            //$this->view->duration = $duration;
    }
   
        
    private function getReportTitle($reportTitle, $dur, $agentId = 0, $singleDayOnly=false)
    {
        
        $title = $reportTitle;
        if($agentId > 0)
        {
            $objAgent = new Agents();
            $agentInfo = $objAgent->findById($agentId);
            $title .= ' For '. $agentInfo->name;
            
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
    
    /* exportagentloadreloadAction function is responsible to create the csv file on fly with agent load reload report data
     * and let user download that file.
     */
    
     public function exportagentloadreloadAction(){
        
        $qurStr['from_date'] = $this->_getParam('from_date');
        $qurStr['to_date'] = $this->_getParam('to_date');
        $qurStr['id'] = $this->_getParam('id');
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode'); 
        $form = new Mvc_Axis_AgentLoadReloadForm(array(
            'action' => $this->formatURL('/mvc_axis_reports/agentloadreload'),
            'method' => 'POST',
        )); 
        
         if($qurStr['from_date']!='' && $qurStr['to_date']!=''){    
             
              if($form->isValid($qurStr)){ 
                 $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-","-","from");
                 $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-","-","to"); 
                 $qurData['id'] = $qurStr['id'];
                 $qurData['bank_unicode'] = $durationArr['bank_unicode'];
                 
                 $objReports = new Mvc_Reports();
                 $exportData = $objReports->exportAgentLoadReload($qurData);
               
                 $columns = array(
                                    'Transaction Date',
                                    'Super Distributor Code',
                                    'Super Distributor Name',
                                    'Distributor Code',
                                    'Distributor Name',
                                    'Agent Code',
                                    'Agent Name',
                                    'Agent City',
                                    'Agent Pincode',
                                    'Transaction Code',
                                    'Load/Reload Amount',
                                    'CRN',
                                    'Customer Mobile Number',
                                    'Transaction Reference Number',   
                                 );
                                  
                 $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'agent_load_reload');exit;
                 }catch (Exception $e) {
                                         App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                         $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                         $this->_redirect($this->formatURL('/mvc_axis_reports/agentloadreload?dur='.$qurStr['dur'].'&sub=1')); 
                                       }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid duration!') );
                         $this->_redirect($this->formatURL('/mvc_axis_reports/agentloadreload?dur='.$qurStr['dur'].'&sub=1')); 
                      }             
          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Invalid duration!') );
                    $this->_redirect($this->formatURL('/mvc_axis_reports/agentloadreload?dur='.$qurStr['dur'].'&sub=1')); 
                 }    
       }
       
       
       
    /* exportagentwiseloadAction function is responsible to create the csv file on fly with agent wise load report data
     * and let user download that file.
     */
    
     public function exportagentwiseloadAction(){
        
        // Get our form and validate it
        $form = new Mvc_Axis_AgentWiseLoadForm(array('action' => $this->formatURL('/mvc_axis_reports/agentwiseload'),
                                                              'method' => 'POST',
                                                        )); 
        //$formData  = $this->_request->getPost();//$form->getValues();//
        $qurStr['id'] = $this->_getParam('id');
        $qurStr['dur'] = $this->_getParam('dur');
               
         if($qurStr['id']>0 && $qurStr['dur']!=''){             
              if($form->isValid($qurStr)){ 
                 $qurData['agent_id'] = $qurStr['id'];
                 $durationArr = Util::getDurationDates($qurStr['dur']);
                 $qurData['from'] = $durationArr['from'];
                 $qurData['to'] = $durationArr['to'];                 
                 
                 $objReports = new Mvc_Reports();
                 $exportData = $objReports->exportAgentWiseLoads($qurData);
                
                 $columns = array(
                                    'Date',                                    
                                    'Agent Name',
                                    'Agent Code',
                                    'Agent City',
                                    'Agent Pincode',
                                    'Transaction Type',
                                    'Load/ Reload Amount',                           
                                    'CRN',
                                    'Mobile Number', 
                                    'Transaction Ref Number',   
                                   );
                                  
                 $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'agent_wise_load');exit;
                 } catch (Exception $e) {
                                            App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                            $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                            $this->_redirect($this->formatURL('/mvc_axis_reports/agentwiseload?dur='.$qurStr['dur'].'&sub=1&id='.$qurStr['id'])); 
                                        }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid data found') );
                         $this->_redirect($this->formatURL('/mvc_axis_reports/agentwiseload?dur='.$qurStr['dur'].'&sub=1&id='.$qurStr['id'])); 
                      }             
          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Data missing') );
                    $this->_redirect($this->formatURL('/mvc_axis_reports/agentwiseload?dur='.$qurStr['dur'].'&sub=1&id='.$qurStr['id'])); 
                 }    
       }
       
    /* exportcardholderactivationsAction function is responsible to create the csv file on fly with card holder activations report data
     * and let user download that file.
     */
    
     public function exportcardholderactivationsAction(){
        
        // Get our form and validate it
        $form = new Mvc_Axis_CardholderActivationsForm(array('action' => $this->formatURL('/mvc_axis_reports/cardholderactivations'),
                                                    'method' => 'POST',
                                             )); 
  
        $qurStr['dur'] = $this->_getParam('dur');
        
         if($qurStr['dur']!=''){ 
             
              if($form->isValid($qurStr)){ 
                 $durationArr = Util::getDurationDates($qurStr['dur']);
                 $qurData['from'] = $durationArr['from'];
                 $qurData['to'] = $durationArr['to'];
                 
                 $objReports = new Mvc_Reports();
                 $exportData = $objReports->exportCardholderActivations($qurData);
               
                 $columns = array(
                                    'Date',                  
                                    'Cardholder Name',
                                    'CRN',
                                    'Mobile Number',
                                    'Agent Code',
                                    'Agent Name',
                                    'Agent City', 
                                    'Bank Name',
                                    //'Address',
                                   
                                    //'Agent Pin Code',   
                                 );
                                  
                 $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'card_holder_activations');exit;
                 }catch (Exception $e) {
                                         App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                         $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                         $this->_redirect($this->formatURL('/mvc_axis_reports/cardholderactivations?dur='.$qurStr['dur'].'&sub=1')); 
                                       }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid duration!') );
                         $this->_redirect($this->formatURL('/mvc_axis_reports/cardholderactivations?dur='.$qurStr['dur'].'&sub=1')); 
                      }             
          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Invalid duration!') );
                    $this->_redirect($this->formatURL('/mvc_axis_reports/cardholderactivations?dur='.$qurStr['dur'].'&sub=1')); 
                 }    
       }
    
      /*
     * Load/Reload Commisson report for all agents
     */
    public function loadreloadcommAction(){
        $this->title = 'Load/Reload Commission Report';           
         // Get our form and validate it
        $form = new Mvc_Axis_LoadReloadCommForm(array('action' => $this->formatURL('/mvc_axis_reports/loadreloadcomm'),
                                                    'method' => 'POST',
                                         )); 
             
        
        $qurStr['duration'] = $this->_getParam('duration');
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        $qurStr['btn_submit'] = $this->_getParam('btn_submit');
        $qurStr['submit'] = $this->_getParam('submit');
        $page = $this->_getParam('page');
        
         
         if($qurStr['btn_submit']){             
              if($form->isValid($qurStr)){ 
                 
                 $pageTitle = $this->getReportTitle('Load/Reload Commission Report', $qurStr['duration']);
                 
                 $objComm = new CommissionReport();
                 $durationArr = Util::getDurationDates($qurStr['duration']);
                 $qurData['from'] = $durationArr['from'];
                 $qurData['to'] = $durationArr['to'];  
                 $qurData['agent_id'] = 0;  
                 $qurData['txn_type'] = "'".TXNTYPE_FIRST_LOAD."','".TXNTYPE_CARD_RELOAD."'";
                 if($qurStr['bank_unicode']!=''){
                   $qurData['bank_unicode'] = $qurStr['bank_unicode']; 
                 }else{
                 
                 $bankAxis = App_DI_Definition_Bank::getInstance(BANK_AXIS);
                 $qurData['bank_unicode'] = $bankAxis->bank->unicode;
                 }
                 
                // $bankAxis = App_DI_Definition_Bank::getInstance(BANK_AXIS);
               //  $qurData['bank_unicode'] = $bankAxis->bank->unicode;
                 $commArr = $objComm->getCommission($qurData);
                 
                 $paginator = $objComm->paginateByArray($commArr, $page, $paginate = NULL);
                 $this->view->paginator = $paginator;
                 $this->view->pageTitle = $pageTitle;
                 $this->view->btnSubmit = $qurStr['btn_submit'];
               }   
               
          }
            $this->view->form = $form;
            $this->view->formData = $qurStr; 
            
    }
    
        /*
     * Export Consolidated Load/reload Commisson report for all agents
     */
    
     public function exportloadreloadcommAction(){
        
         // Get our form and validate it
         $form = new Mvc_Axis_LoadReloadCommForm(array('action' => $this->formatURL('/mvc_axis_reports/exportloadreloadcomm'),
                                                    'method' => 'POST',
                                         )); 
        $qurStr['duration'] = $this->_getParam('duration');
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
         $objComm = new CommissionReport();
        
         
         if($qurStr['duration']!='' ){           
              if($form->isValid($qurStr)){ 
                 
                 $durationArr = Util::getDurationDates($qurStr['duration']);
                 $qurData['from'] = $durationArr['from'];
                 $qurData['to'] = $durationArr['to'];  
                 $qurData['txn_type'] = "'".TXNTYPE_FIRST_LOAD."','".TXNTYPE_CARD_RELOAD."'";
                 
                 if($qurStr['bank_unicode']!=''){
                   $qurData['bank_unicode'] = $qurStr['bank_unicode']; 
                 }else{
                 
                 $bankAxis = App_DI_Definition_Bank::getInstance(BANK_AXIS);
                 $qurData['bank_unicode'] = $bankAxis->bank->unicode;
                 }
                 
                 $exportData = $objComm->getCommissionCSV($qurData);
                
                 $columns = array(
                                    'Date',
                                    'Agent Code', 
                                    'Agent Name', 
                                    'Agent City', 
                                    'Agent Pincode', 
                                    'Transaction Narration',
                                    'Commission Plan',
                                    'Commission Amount',
                                   
                                 );
                                  
                 $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'loadreload_commission_report');exit;
                 } catch (Exception $e) {
                                            App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                            $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                             $this->_redirect($this->formatURL('/mvc_axis_reports/exportloadreloadcomm?duration='.$qurStr['duration'].'&sub=1&bank_unicode='.$qurStr['bank_unicode'])); 
                                        }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid data found') );
                         $this->_redirect($this->formatURL('/mvc_axis_reports/exportloadreloadcomm?duration='.$qurStr['duration'].'&sub=1&bank_unicode='.$qurStr['bank_unicode'])); 
                      }             
          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Data missing') );
                    $this->_redirect($this->formatURL('/mvc_axis_reports/exportloadreloadcomm?duration='.$qurStr['duration'].'&sub=1&bank_unicode='.$qurStr['bank_unicode'])); 
                 }    
       }
       
        /*
     * Agent Wise Load/Reload Commisson report for all agents
     */
    public function agentwiseloadreloadcommAction(){
        $this->title = 'Agent Wise Load/Reload Commission';           
         // Get our form and validate it
        $form = new Mvc_Axis_AgentWiseLoadReloadCommForm(array('action' => $this->formatURL('/mvc_axis_reports/agentwiseloadreloadcomm'),
                                                    'method' => 'POST',
                                         )); 
             
        $qurStr['id'] = $this->_getParam('id');
        $qurStr['duration'] = $this->_getParam('duration');
        $qurStr['btn_submit'] = $this->_getParam('btn_submit');
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        $qurStr['submit'] = $this->_getParam('submit');
        $page = $this->_getParam('page');
        
                
         
         if($qurStr['btn_submit']){   
              if($form->isValid($qurStr)){ 
                 $pageTitle = $this->getReportTitle('Load/Reload Commission Report', $qurStr['duration'], $qurStr['id']);
                 
                 $objComm = new CommissionReport();
                 $durationArr = Util::getDurationDates($qurStr['duration']);
                 $qurData['from'] = $durationArr['from'];
                 $qurData['to'] = $durationArr['to'];  
                 $qurData['agent_id']  = $qurStr['id'];  
                 $qurData['txn_type'] = "'".TXNTYPE_FIRST_LOAD."','".TXNTYPE_CARD_RELOAD."'";
                 if($qurStr['bank_unicode']!=''){
                   $qurData['bank_unicode'] = $qurStr['bank_unicode']; 
                 }else{
                 
                 $bankAxis = App_DI_Definition_Bank::getInstance(BANK_AXIS);
                 $qurData['bank_unicode'] = $bankAxis->bank->unicode;
                 }
                 $commArr = $objComm->getCommission($qurData);
                 
                 $paginator = $objComm->paginateByArray($commArr, $page, $paginate = NULL);
                 $form->getElement('aid')->setValue($qurData['agent_id']);
                 $this->view->paginator = $paginator;
                 $this->view->pageTitle = $pageTitle;
                 $this->view->btnSubmit = $qurStr['btn_submit'];
               }   
               
          }
            $this->view->form = $form;
            $this->view->formData = $qurStr; 
            
    }
    
        /*
     * Export Consolidated Load/reload Commisson report for all agents
     */
    
     public function exportagentwiseloadreloadcommAction(){
        
         // Get our form and validate it
         $form = new Mvc_Axis_AgentWiseLoadReloadCommForm(array('action' => $this->formatURL('/mvc_axis_reports/exportagentwiseloadreloadcomm'),
                                                    'method' => 'POST',
                                         )); 
         
         $qurStr['duration'] = $this->_getParam('duration');
         $qurStr['id'] = $this->_getParam('id');
         $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
         $objComm = new CommissionReport();
         
         
         if($qurStr['duration']!='' ){           
              if($form->isValid($qurStr)){ 
                
                 $durationArr = Util::getDurationDates($qurStr['duration']);
                 $qurData['from'] = $durationArr['from'];
                 $qurData['to'] = $durationArr['to'];  
                 $qurData['agent_id'] = $qurStr['id']; 
                 $qurData['txn_type'] = "'".TXNTYPE_FIRST_LOAD."','".TXNTYPE_CARD_RELOAD."'"; 
                 
                  if($qurStr['bank_unicode']!=''){
                   $qurData['bank_unicode'] = $qurStr['bank_unicode']; 
                 }else{
                 
                 $bankAxis = App_DI_Definition_Bank::getInstance(BANK_AXIS);
                 $qurData['bank_unicode'] = $bankAxis->bank->unicode;
                 }
                 
                 $exportData = $objComm->getCommissionCSV($qurData);
                
                 $columns = array(
                                    'Date',
                                    'Agent Code', 
                                    'Agent Name', 
                                    'Agent City', 
                                    'Agent Pincode', 
                                    'Transaction Narration',
                                    'Commission Plan',
                                    'Commission Amount',
                                   
                                 );
                                  
                 $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'loadreload_commission_report');exit;
                 } catch (Exception $e) {
                                            App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                            $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                             $this->_redirect($this->formatURL('/mvc_axis_reports/agentwiseloadreloadcomm?duration='.$qurStr['duration'].'&sub=1&id='.$qurStr['agent_id'].'&bank_unicode='.$qurStr['bank_unicode'])); 
                                        }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid data found') );
                         $this->_redirect($this->formatURL('/mvc_axis_reports/agentwiseloadreloadcomm?duration='.$qurStr['duration'].'&sub=1&id='.$qurStr['agent_id'].'&bank_unicode='.$qurStr['bank_unicode'])); 
                      }             
          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Data missing') );
                    $this->_redirect($this->formatURL('/mvc_axis_reports/agentwiseloadreloadcomm?duration='.$qurStr['duration'].'&sub=1&id='.$qurStr['agent_id'].'&bank_unicode='.$qurStr['bank_unicode'])); 
                 }    
       }
       
       
  }