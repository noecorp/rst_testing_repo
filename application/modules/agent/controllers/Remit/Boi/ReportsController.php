<?php
/**
 * MVC Axis Bank Reports
 *
 * @package frontend_controllers
 * @copyright company
 */

class Remit_Boi_ReportsController extends App_Operation_Controller
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
    
   
       
  
   
       
        /* remittancereportAction function will show the agent  remittance, refund, refund fee, service tax
     */
    public function remittancereportAction()
    {   $user = Zend_Auth::getInstance()->getIdentity();       
        $this->title = 'Remittance Report';
        // Get our form and validate it
        $form = new Remit_Boi_RemittanceReportForm(array('action' => $this->formatURL('/remit_boi_reports/remittancereport'),
                                                              'method' => 'POST',
                                             ));  
        $request = $this->getRequest();  
        //$formData  = $this->_request->getPost();//$form->getValues();//
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['sub'] = $this->_getParam('sub');
        $qurStr['bank_unicode'] = $user->bank_unicode;
        $src = $this->_getParam('src');
        
         if($qurStr['sub']!=''){             
              if($form->isValid($qurStr)){ 
                 $qurData['agent_id'] = $user->id;
                 $durationArr = Util::getDurationDates($qurStr['dur']);
                 $qurData['duration'] = $qurStr['dur'];
                 $qurData['bank_unicode'] =  $qurStr['bank_unicode']; 
                 
                 $objAgent = new Agents();
                 $agentInfo = $objAgent->findById($qurData['agent_id']);
                 
                 $fromDate = explode(' ', Util::returnDateFormatted($durationArr['from'], "d-m-Y", "Y-m-d", "-"));
                 $toDate = explode(' ', Util::returnDateFormatted($durationArr['to'], "d-m-Y", "Y-m-d", "-"));
                
                 //$this->view->agent_name = $agentInfo->name;
                 $this->view->agentInfo = $agentInfo;
                 
                 $this->view->from = $fromDate[0];
                 $this->view->to   = $toDate[0];  
                 $this->view->title = $this->getReportTitle('Remittance Report', $qurStr['dur'], $qurData['agent_id']);
                 $page = $this->_getParam('page');
                
                 $objReports = new Remit_Reports();
                 $agentWiseTxns = $objReports->getAgentWiseRemittance($qurData);
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
        
        
        
        
     /* exportremittancereportAction function is responsible to create the csv file on fly with agent wise remittances,
     * refund, remitter fee, service tax report data and let user download that file.
     */
    
     public function exportremittancereportAction(){
         $user = Zend_Auth::getInstance()->getIdentity();       
        // Get our form and validate it
        $form = new Remit_Boi_RemittanceReportForm(array('action' => $this->formatURL('/remit_boi_reports/exportremittancereport'),
                                                              'method' => 'POST',
                                             )); 
        //$formData  = $this->_request->getPost();//$form->getValues();//
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['id'] = $user->id;
        $qurStr['bank_unicode'] = $user->bank_unicode;
         if($qurStr['id']>0 && $qurStr['dur']!=''){             
              if($form->isValid($qurStr)){ 
                 $qurData['agent_id'] = $user->id;
                 $durationArr = Util::getDurationDates($qurStr['dur']);
                 $qurData['duration'] = $qurStr['dur'];
                 $qurData['bank_unicode'] =  $qurStr['bank_unicode']; 
                 //$qurData['from'] = $durationArr['from'];
                 //$qurData['to'] = $durationArr['to'];                 
                 
                 $objReports = new Remit_Reports();
                 $exportData = $objReports->exportAgentWiseRemittanceFromAgent($qurData);
               
                 $columns = array(
                                    'Date',                                    
                                    'Agent Name',
                                    'Agent Code',
                                    'Agent City',
                                    'Agent Pincode',
                                    'Transaction Naration',
                                    'Transaction Amount',                           
                                    'Mobile Number',
                                    'Product Code', 
                                    'Transaction Ref Number',   
                                   );
                                  
                 $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'agent_remittance');exit;
                 } catch (Exception $e) {
                                            App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                            $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                            $this->_redirect($this->formatURL('/remit_boi_reports/exportremittancereport?dur='.$qurStr['dur'].'&sub=1&id='.$qurStr['id'].'&bank_unicode='.$qurStr['bank_unicode'])); 
                                        }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid data found') );
                         $this->_redirect($this->formatURL('/remit_boi_reports/exportremittancereport?dur='.$qurStr['dur'].'&sub=1&id='.$qurStr['id'].'&bank_unicode='.$qurStr['bank_unicode'])); 
                      }             
          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Data missing') );
                    $this->_redirect($this->formatURL('/remit_boi_reports/exportremittancereport?dur='.$qurStr['dur'].'&sub=1&id='.$qurStr['id'].'&bank_unicode='.$qurStr['bank_unicode'])); 
                 }    
       }
         /*
     * Remittance Commisson report agent wise
     */
    public function remittancecommissionAction(){
        $this->title = 'Remittance Commission Report';           
         // Get our form and validate it
        $form = new Remit_Boi_RemittanceCommForm(array('action' => $this->formatURL('/remit_boi_reports/remittancecommission'),
                                                    'method' => 'POST',
                                         )); 
             
        $user = Zend_Auth::getInstance()->getIdentity(); 
        $qurStr['id'] = $user->id;
        $qurStr['duration'] = $this->_getParam('duration');
        $qurStr['btn_submit'] = $this->_getParam('btn_submit');
        $qurStr['bank_unicode'] = $user->bank_unicode;
        $page = $this->_getParam('page');
        
         
         if($qurStr['btn_submit']){     
            
              if($form->isValid($qurStr)){ 
                 
                 $pageTitle = $this->getReportTitle('Remittance Commission Report', $qurStr['duration'], $qurStr['id']);
                 
                 $objComm = new CommissionReport();
                 $durationArr = Util::getDurationDates($qurStr['duration']);
                 $qurData['from'] = $durationArr['from'];
                 $qurData['to'] = $durationArr['to'];  
                 $qurData['agent_id'] = $qurStr['id'];  
                 $qurData['txn_type'] = "'".TXNTYPE_REMITTER_REGISTRATION."','".TXNTYPE_REMITTANCE_FEE."','".TXNTYPE_REMITTANCE_REFUND_FEE."'";
                 $qurData['bank_unicode'] =  $qurStr['bank_unicode']; 
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
     * Export Consolidated Remittance Commisson report for agent wise
     */
    
     public function exportremittancecommissionAction(){
         $user = Zend_Auth::getInstance()->getIdentity(); 
         // Get our form and validate it
         $form = new Remit_Boi_RemittanceCommForm(array('action' => $this->formatURL('/remit_boi_reports/remittancecommission'),
                                                    'method' => 'POST',
                                         )); 
         $qurStr['duration'] = $this->_getParam('duration');
         $objComm = new CommissionReport();
         $qurStr['bank_unicode'] = $user->bank_unicode;
         if($qurStr['duration']!='' ){           
              if($form->isValid($qurStr)){ 
                 
                 $durationArr = Util::getDurationDates($qurStr['duration']);
                 $qurData['from'] = $durationArr['from'];
                 $qurData['to'] = $durationArr['to'];  
                 $qurData['agent_id'] = $user->id; 
                 $qurData['txn_type'] = "'".TXNTYPE_REMITTER_REGISTRATION."','".TXNTYPE_REMITTANCE_FEE."','".TXNTYPE_REMITTANCE_REFUND_FEE."'";
                 $qurData['bank_unicode'] =  $qurStr['bank_unicode']; 
                 $exportData = $objComm->getCommission($qurData);
                
                 $columns = array(
                                    'Date',
                                    'Agent Code', 
                                    'Agent Name', 
                                    'Agent City', 
                                    'Agent Pincode', 
                                    'Transaction Narration',                                    
                                    'Product Code',
                                    'Transaction Amount',
                                    'Transaction Fee',
                                    'Transaction Service Tax',
                                    'Commission Plan',
                                    'Commission Amount',
                                    'Agent Saving Account No.',
                                    'Agent IFSC Code',
                                   
                                 );
                                  
                 $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'remittance_commission_report');exit;
                 } catch (Exception $e) {
                                            App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                            $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                             $this->_redirect($this->formatURL('/remit_boi_reports/remittancecommission?duration='.$qurStr['duration'].'&sub=1&bank_unicode='.$qurStr['bank_unicode'])); 
                                        }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid data found') );
                         $this->_redirect($this->formatURL('/remit_boi_reports/remittancecommission?duration='.$qurStr['duration'].'&sub=1&bank_unicode='.$qurStr['bank_unicode'])); 
                      }             
          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Data missing') );
                    $this->_redirect($this->formatURL('/remit_boi_reports/remittancecommission?duration='.$qurStr['duration'].'&sub=1&bank_unicode='.$qurStr['bank_unicode'])); 
                 }    
       }
  }