<?php
/**
 * Allows user to see MVC Axis Reports
 *
 * @category backoffice
 * @package backoffice_controllers
 * @copyright company
 */

class Mvc_Axis_ReportsController extends App_Agent_Controller
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
     /* agentwiseloadAction function will search agent to cardholder loads and show 
     * the result to ops 
     */
    public function agentwiseloadAction()
    {   
        $this->title = 'Load/Reload Report';
        $user = Zend_Auth::getInstance()->getIdentity();
        // Get our form and validate it
        $form = new Mvc_Axis_AgentWiseLoadForm(array('action' => $this->formatURL('/mvc_axis_reports/agentwiseload'),
                                                              'method' => 'POST',
                                                        )); 
       
        $request = $this->getRequest();  
        //$formData  = $this->_request->getPost();//$form->getValues();//
        $qurStr['id'] = $user->id;
        $qurStr['dur'] = $this->_getParam('dur');
        $src = $this->_getParam('src');
         
         
         if($qurStr['id']>0 && $qurStr['dur']!=''){ 
           
              if($form->isValid($qurStr)){ 
              
                 $qurData['agent_id'] = $user->id;
                 $durationArr = Util::getDurationDates($qurStr['dur']);
                 $qurData['from'] = $durationArr['from'];
                 $qurData['to'] = $durationArr['to'];
                     
                 
                 $objAgent = new Agents();
                 $agentInfo = $objAgent->findById($user->id);
                 
                 $fromDate = explode(' ', Util::returnDateFormatted($durationArr['from'], "d-m-Y", "Y-m-d", "-"));
                 $toDate = explode(' ', Util::returnDateFormatted($durationArr['to'], "d-m-Y", "Y-m-d", "-"));
                
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
     
    /* exportagentwiseloadAction function is responsible to create the csv file on fly with agent wise load report data
     * and let user download that file.
     */
    
     public function exportagentwiseloadAction(){
        
        // Get our form and validate it
        $form = new Mvc_Axis_AgentWiseLoadForm(array('action' => $this->formatURL('/mvc_axis_reports/agentwiseload'),
                                                              'method' => 'POST',
                                                        )); 
        $request = $this->getRequest();  
        //$formData  = $this->_request->getPost();//$form->getValues();//
        $qurStr['id'] = $this->_getParam('id');
        $qurStr['dur'] = $this->_getParam('dur');
               
         if($qurStr['id']>0 && $qurStr['dur']!=''){             
              if($form->isValid($qurStr)){ 
                 $qurData['agent_id'] = $qurStr['id'];
                 $durationArr = Util::getDurationDates($qurStr['dur']);
                 $qurData['from'] = $durationArr['from'];
                 $qurData['to'] = $durationArr['to'];                 
                 $TXN_TYPE_LABELS = Zend_Registry::get("TXN_TYPE_LABELS");
                 $objReports = new Mvc_Reports();
                 $exportData = $objReports->exportAgentWiseLoadsFromAgent($qurData);
                
                
                 $columns = array(
                
                                        'Date',
                                        'Agent Name',
                                        'Agent Code',
                                        'Transaction Type',
                                        'Load/ Reload Amount',
                                        'Customer Name',
                                        'Customer Mobile Number',   
                                        'Product Code',   
                                        'Transaction Ref Number',   
                                 );
                                  
                 $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'agent_wise_load');exit;
                 } 
                catch (Exception $e) {
                    App_Logger::log($e->getMessage() , Zend_Log::ERR);
                    $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
//                                            $this->_redirect('/reports/agentwiseload?dur='.$qurStr['dur'].'&sub=1&id='.$qurStr['id']); 
                    $this->_redirect($this->formatURL('/mvc_axis_reports/agentwiseload?dur='.$qurStr['dur'].'&sub=1&id='.$qurStr['id'])); 
                }
                 
               } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Invalid data found!') );
    //                         $this->_redirect('/reports/agentwiseload?dur='.$qurStr['dur'].'&sub=1&id='.$qurStr['id']); 
                    $this->_redirect($this->formatURL('/mvc_axis_reports/agentwiseload?dur='.$qurStr['dur'].'&sub=1&id='.$qurStr['id'])); 
                 }             
          } else {
                $this->_helper->FlashMessenger( array('msg-error' => 'Data missing!') );
    //                    $this->_redirect('/reports/agentwiseload?dur='.$qurStr['dur'].'&sub=1&id='.$qurStr['id']); 
                $this->_redirect($this->formatURL('/mvc_axis_reports/agentwiseload?dur='.$qurStr['dur'].'&sub=1&id='.$qurStr['id'])); 
             }    
       }
       
       public function cardholderactivationsAction(){
        $this->title = 'Cardholder Activation Report';
        $user = Zend_Auth::getInstance()->getIdentity();
         // Get our form and validate it
        $form = new Mvc_Axis_CardholderActivationsForm(array('action' => $this->formatURL('/mvc_axis_reports/cardholderactivations'),
                                                    'method' => 'POST',
                                             )); 
  
        $request = $this->_getAllParams();
        $sub = $this->_getParam('sub');
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['sub'] = $sub;
        
         if($sub!=''){ 
             
              if($form->isValid($qurStr)){ 
                 
                 $durationArr = Util::getDurationDates($qurStr['dur']);
                 $qurData['from'] = $durationArr['from'];
                 $qurData['to'] = $durationArr['to'];
                 $qurData['agent_id'] = $user->id;
                 
                 $fromDate = explode(' ', Util::returnDateFormatted($durationArr['from'], "d-m-Y", "Y-m-d", "-"));
                 $toDate = explode(' ', Util::returnDateFormatted($durationArr['to'], "d-m-Y", "Y-m-d", "-"));
                 $this->view->from = $fromDate[0];
                 $this->view->to   = $toDate[0];                 
                 $this->view->title = $this->getReportTitle('Cardholder Activation Report', $qurStr['dur']);
                 
                 $objReports = new Mvc_Reports();
                 $this->view->paginator = $objReports->getCardholderActivations($qurData, $this->_getPage());
                  //echo '<pre>';print_r($this->view->paginator);exit;
                 $this->view->formData = $qurStr; 
                 //$this->view->agent_name = $agentInfo->name;
                 
              }                  
          }
            $this->view->form = $form;
            //$this->view->formData = $qurStr;
            //$this->view->formData = $formData; 
            //$this->view->duration = $duration;
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
                                    'Cardholder Name',
                                    'CRN',
                                    'Mobile Number',
                                    'Agent Code',
                                    'Agent Name',
                                    'Agent City', 
                                    'Product Code', 
                                    'Bank Name',
                                    //'Address',
                                   // 'Date',
                                   // 'Agent Pin Code',   
                                 );
                                  
                 $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'card_holder_activations');exit;
                 }
                 catch (Exception $e) {
                    App_Logger::log($e->getMessage() , Zend_Log::ERR);
                    $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
//                                         $this->_redirect('/reports/cardholderactivations?dur='.$qurStr['dur'].'&sub=1'); 
                    $this->_redirect($this->formatURL('/mvc_axis_reports/cardholderactivations?dur='.$qurStr['dur'].'&sub=1')); 
                  }
                 
               } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Invalid duration!') );
//                         $this->_redirect('/reports/cardholderactivations?dur='.$qurStr['dur'].'&sub=1'); 
                    $this->_redirect($this->formatURL('/mvc_axis_reports/cardholderactivations?dur='.$qurStr['dur'].'&sub=1')); 
                 }             
          } else {
            $this->_helper->FlashMessenger( array('msg-error' => 'Invalid duration!') );
//                    $this->_redirect('/reports/cardholderactivations?dur='.$qurStr['dur'].'&sub=1'); 
            $this->_redirect($this->formatURL('/mvc_axis_reports/cardholderactivations?dur='.$qurStr['dur'].'&sub=1')); 
         }    
       }
     /*
     * Load/Reload Commisson report for all agents
     */
    public function loadreloadcommAction(){
        $user = Zend_Auth::getInstance()->getIdentity();
        $this->title = 'Load Reload Commission Report';           
         // Get our form and validate it
        $form = new Mvc_Axis_LoadReloadCommForm(array('action' => $this->formatURL('/mvc_axis_reports/loadreloadcomm'),
                                                    'method' => 'POST',
                                         )); 
             
        $qurStr['id'] = $user->id;
        $qurStr['duration'] = $this->_getParam('duration');
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
                 $qurData['agent_id']  = $qurStr['id'];  
                 $qurData['txn_type'] = "'".TXNTYPE_FIRST_LOAD."','".TXNTYPE_CARD_RELOAD."'";
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
         $user = Zend_Auth::getInstance()->getIdentity();
         // Get our form and validate it
         $form = new Mvc_Axis_LoadReloadCommForm(array('action' => $this->formatURL('/mvc_axis_reports/exportagentwiseloadreloadcomm'),
                                                    'method' => 'POST',
                                         )); 
         $qurStr['duration'] = $this->_getParam('duration');
         $qurStr['id'] = $user->id;
         $objComm = new CommissionReport();
        
         
         if($qurStr['duration']!='' ){           
              if($form->isValid($qurStr)){ 
                 
                 $durationArr = Util::getDurationDates($qurStr['duration']);
                 $qurData['from'] = $durationArr['from'];
                 $qurData['to'] = $durationArr['to'];  
                 $qurData['agent_id'] = $qurStr['id']; 
                 $qurData['txn_type'] = "'".TXNTYPE_FIRST_LOAD."','".TXNTYPE_CARD_RELOAD."'";
                 $exportData = $objComm->getCommission($qurData);
                
                 $columns = array(
                                    'Date',
                                    'Agent Code', 
                                    'Agent Name', 
                                    'Agent City', 
                                    'Agent Pincode', 
                                    'Transaction Narration',                                    
                                    'Product Code',
                                    'Commission Plan',
                                    'Commission Amount',
                                   
                                 );
                                  
                 $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'loadreload_commission_report');exit;
                 } catch (Exception $e) {
                                            App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                            $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                             $this->_redirect($this->formatURL('/mvc_axis_reports/loadreloadcomm?duration='.$qurStr['duration'].'&sub=1&id='.$qurStr['agent_id'])); 
                                        }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid data found') );
                         $this->_redirect($this->formatURL('/mvc_axis_reports/loadreloadcomm?duration='.$qurStr['duration'].'&sub=1&id='.$qurStr['agent_id'])); 
                      }             
          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Data missing') );
                    $this->_redirect($this->formatURL('/mvc_axis_reports/loadreloadcomm?duration='.$qurStr['duration'].'&sub=1&id='.$qurStr['agent_id'])); 
                 }    
       }
       
}