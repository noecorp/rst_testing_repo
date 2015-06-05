<?php
/**
 * Ratnakar Remit Bank Reports
 *
 * @package frontend_controllers
 * @copyright company
 */

class Remit_Ratnakar_ReportsController extends App_Agent_Controller
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
    
   
       
  
   
       
    /* remittancereportAction function will show the agent  remittance, refund, refund fee, service tax
     */
    public function remittancereportAction()
    {   $user = Zend_Auth::getInstance()->getIdentity();
        $this->title = 'Remittance Report';
        // Get our form and validate it
        $form = new Remit_Ratnakar_RemittanceReportForm(array('action' => $this->formatURL('/remit_ratnakar_reports/remittancereport'),
                                                              'method' => 'POST',
                                             ));  
        $request = $this->getRequest();  
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['sub'] = $this->_getParam('sub');
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $qurStr['bank_unicode'] = $user->bank_unicode;
        $src = $this->_getParam('src');
               
        if($qurStr['sub']!=''){             
            if($form->isValid($qurStr)){ 
                $qurData['agent_id'] = $user->id;
                if ($qurStr['dur'] != '') {
                    $durationArr = Util::getDurationDates($qurStr['dur']);
                    $qurData['duration'] = $qurStr['dur'];
                    
                    $objAgent = new Agents();
                    $agentInfo = $objAgent->findById($qurData['agent_id']);

                    $fromDate = explode(' ', Util::returnDateFormatted($durationArr['from'], "d-m-Y", "Y-m-d", "-"));
                    $toDate = explode(' ', Util::returnDateFormatted($durationArr['to'], "d-m-Y", "Y-m-d", "-"));

                    $this->view->agentInfo = $agentInfo;
                    $this->view->from = $fromDate[0];
                    $this->view->to   = $toDate[0];  
                    $this->view->title = $this->getReportTitle('Remittance Report', $qurStr['dur'], $qurData['agent_id']);
                
                 }else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                    $objBank = new Banks();
                    $bankInfo = $objBank->getBankbyUnicode($qurStr['bank_unicode']);
                    $this->view->title = 'Remittance Report of '.$bankInfo->name.' for '.Util::returnDateFormatted($qurData['from'], "Y-m-d", "d-m-Y", "-");
                    $this->view->title .= ' to '.Util::returnDateFormatted($qurData['to'], "Y-m-d", "d-m-Y", "-");
                    $this->view->from = $qurData['from'];
                    $this->view->to   = $qurData['to'];
                }
                
                $qurData['bank_unicode'] =  $qurStr['bank_unicode']; 
                $page = $this->_getParam('page');

                $objReports = new Remit_Reports();
                $agentWiseTxns = $objReports->getAgentWiseRemittance($qurData);
                $paginator = $objReports->paginateByArray($agentWiseTxns, $page, $paginate = NULL);
				
                $this->view->paginator=$paginator;
                
            } 
        }
        $this->view->form = $form;
        $this->view->formData = $qurStr; 
        $this->view->callingRprtDur = $qurStr['dur'];
        $this->view->src = $src;
    } 
        
        
        
        
     /* exportremittancereportAction function is responsible to create the csv file on fly with agent wise remittances,
     * refund, remitter fee, service tax report data and let user download that file.
     */
    
     public function exportremittancereportAction(){
         $user = Zend_Auth::getInstance()->getIdentity();       
        // Get our form and validate it
        $form = new Remit_Ratnakar_RemittanceReportForm(array('action' => $this->formatURL('/remit_ratnakar_reports/exportremittancereport'),
                                                              'method' => 'POST',
                                             )); 
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $qurStr['id'] = $user->id;
        $qurStr['bank_unicode'] = $user->bank_unicode;
        if($qurStr['id']>0 && $qurStr['dur']!='' || ($qurStr['to_date'] !='' && $qurStr['from_date']!='')){             
              if($form->isValid($qurStr)){ 
                $qurData['agent_id'] = $user->id;
                if ($qurStr['dur'] != '') {
                   $qurData['duration'] = $qurStr['dur'];

                } else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                }
                 $qurData['bank_unicode'] =  $qurStr['bank_unicode']; 
                 $objReports = new Remit_Reports();
                 $exportData = $objReports->exportRATAgentWiseRemittanceFromAgent($qurData);
               
                 $columns = array(
                                    'Transaction Date',
				    'Super Distributor Code',
				    'Super Distributor Name',
				    'Distributor Code',
				    'Distributor Name',
				    'Agent Code',
				    'Agent Mobile Number',
				    'Agent Email ID',
				    'Agent Name',
				    'Agent City',
				    'Agent Pincode',
				    'Transaction Code',
				    'Transaction Amount',
				    'Customer Mobile Number',
				    'Transaction Reference Number', 
				    'Refund/Reversed Trx Ref Number',
				    'Remitter Name',
				    'Remitter Mobile Number',
				    'Remitter Email',
				    'Remitter Registration Date',
				    'Bene Name',
				    'Bene Bank Name',
				    'Bene IFSC Code', 
				    'Beneficiary Account Number', 
				    'Current Transaction Status',
				    'Reason',
				    'Reason Code',
				    'UTR No.',
				    'Batch name',
				    'Transfer Mode'
                                   );
                                  
                 $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'agent_remittance');exit;
                 } catch (Exception $e) {
                                            App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                            $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                            $this->_redirect($this->formatURL('/remit_ratnakar_reports/remittancereport?dur='.$qurStr['dur'].'&sub=1&id='.$qurStr['id'].'&bank_unicode='.$qurStr['bank_unicode'].'&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'])); 
                                        }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid data found') );
                         $this->_redirect($this->formatURL('/remit_ratnakar_reports/remittancereport?dur='.$qurStr['dur'].'&sub=1&id='.$qurStr['id'].'&bank_unicode='.$qurStr['bank_unicode'].'&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'])); 
                      }             
          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Data missing') );
                    $this->_redirect($this->formatURL('/remit_ratnakar_reports/remittancereport?dur='.$qurStr['dur'].'&sub=1&id='.$qurStr['id'].'&bank_unicode='.$qurStr['bank_unicode'].'&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'])); 
                 }    
       }
       
      /* distributorremittancereportAction function will show the agent  remittance, refund, refund fee, service tax
     */
    public function distributorremittancereportAction()
    {   
       $user = Zend_Auth::getInstance()->getIdentity();
        $this->title = 'Remittance Report';
        // Get our form and validate it
        $form = new Remit_Ratnakar_DistributorRemittanceReportForm(array('action' => $this->formatURL('/remit_ratnakar_reports/distributorremittancereport'),
                                                              'method' => 'POST',
                                             ));  
        $request = $this->getRequest();  
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['sub'] = $this->_getParam('sub');
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $qurStr['agent_id']  = $this->_getParam('agent_id');
        $qurStr['bank_unicode'] = $user->bank_unicode;
        $src = $this->_getParam('src');
               
        if($qurStr['sub']!=''){             
            if($form->isValid($qurStr)){ 
                $qurData['user_id'] = $user->id;
                $qurData['user_type'] = $user->user_type;
                $qurData['agent_id'] = $qurStr['agent_id'];
                if ($qurStr['dur'] != '') {
                    $durationArr = Util::getDurationDates($qurStr['dur']);
                    $qurData['duration'] = $qurStr['dur'];
                    
                    $objAgent = new Agents();
                    $agentInfo = $objAgent->findById($qurData['agent_id']);

                    $fromDate = explode(' ', Util::returnDateFormatted($durationArr['from'], "d-m-Y", "Y-m-d", "-"));
                    $toDate = explode(' ', Util::returnDateFormatted($durationArr['to'], "d-m-Y", "Y-m-d", "-"));

                    $this->view->agentInfo = $agentInfo;
                    $this->view->from = $fromDate[0];
                    $this->view->to   = $toDate[0];  
                    $this->view->title = $this->getReportTitle('Remittance Report', $qurStr['dur'], $qurData['agent_id']);
                
                 }else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                    $objBank = new Banks();
                    $bankInfo = $objBank->getBankbyUnicode($qurStr['bank_unicode']);
                    $this->view->title = 'Remittance Report of '.$bankInfo->name.' for '.Util::returnDateFormatted($qurData['from'], "Y-m-d", "d-m-Y", "-");
                    $this->view->title .= ' to '.Util::returnDateFormatted($qurData['to'], "Y-m-d", "d-m-Y", "-");
                    $this->view->from = $qurData['from'];
                    $this->view->to   = $qurData['to'];
                }
                
                $qurData['bank_unicode'] =  $qurStr['bank_unicode']; 
                $page = $this->_getParam('page');

                $objReports = new Remit_Reports();
                // $agentWiseTxns = $objReports->getAgentWiseRemittance($qurData);
                 $objRemitter = new Remit_Ratnakar_Remitter();
                 $agentWiseTxns = $objRemitter->getRatnakarMultiRemittance($qurData);
                // Util::debug($agentWiseTxns);exit;
              
                $paginator = $objReports->paginateByArray($agentWiseTxns, $page, $paginate = NULL);
                $this->view->paginator=$paginator;
                
            } 
        }
        $this->view->form = $form;
        $this->view->formData = $qurStr; 
        $this->view->callingRprtDur = $qurStr['dur'];
        $this->view->src = $src;
    }
    
       
     /* exportdistributorremittancereportAction function is responsible to create the csv file on fly with agent wise remittances,
     * refund, remitter fee, service tax report data and let user download that file.
     */
    
     public function exportdistributorremittancereportAction(){
         $user = Zend_Auth::getInstance()->getIdentity();       
        // Get our form and validate it
        $form = new Remit_Ratnakar_DistributorRemittanceReportForm(array('action' => $this->formatURL('/remit_ratnakar_reports/exportdistributorremittancereport'),
                                                              'method' => 'POST',
                                             )); 
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $qurStr['agent_id']  = $this->_getParam('agent_id');
        $qurStr['id'] = $user->id;
        $qurStr['bank_unicode'] = $user->bank_unicode;
      
        if($qurStr['id']>0 && $qurStr['dur']!='' || ($qurStr['to_date'] !='' && $qurStr['from_date']!='')){             
              if($form->isValid($qurStr)){ 
                $qurData['user_id'] = $user->id;
                $qurData['user_type'] = $user->user_type;
                $qurData['agent_id'] = $qurStr['agent_id'];
               
                if ($qurStr['dur'] != '') {
                   $qurData['duration'] = $qurStr['dur'];

                } else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                }
                 $qurData['bank_unicode'] =  $qurStr['bank_unicode']; 
                 $objReports = new Remit_Reports();
                 $exportData = $objReports->exportAgentWiseRemittanceFromDistributorAgent($qurData);
               
                 $columns = array(
				    'Transaction Date',
				    'Super Distributor Code',
				    'Super Distributor Name',
				    'Distributor Code',
				    'Distributor Name',
				    'Agent Code',
				    'Agent Mobile Number',
				    'Agent Email ID',
				    'Agent Name',
				    'Agent City',
				    'Agent Pincode',
				    'Transaction Code',
				    'Transaction Amount',
				    'Customer Mobile Number',
				    'Transaction Reference Number', 
				    'Refund/Reversed Trx Ref Number',
				    'Remitter Name',
				    'Remitter Mobile Number',
				    'Remitter Email',
				    'Remitter Registration Date',
				    'Bene Name',
				    'Bene Bank Name',
				    'Bene IFSC Code', 
				    'Beneficiary Account Number', 
				    'Current Transaction Status',
				    'Reason',
				    'Reason Code',
				    'UTR No.',
				    'Batch name',
				    'Transfer Mode'
                                   );
                                  
                 $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'agent_remittance');exit;
                 } catch (Exception $e) {
                                            App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                            $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                            $this->_redirect($this->formatURL('/remit_ratnakar_reports/distributorremittancereport?dur='.$qurStr['dur'].'&sub=1&id='.$qurStr['id'].'&bank_unicode='.$qurStr['bank_unicode'].'&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'])); 
                                        }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid data found') );
                         $this->_redirect($this->formatURL('/remit_ratnakar_reports/distributorremittancereport?dur='.$qurStr['dur'].'&sub=1&id='.$qurStr['id'].'&bank_unicode='.$qurStr['bank_unicode'].'&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'])); 
                      }             
          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Data missing') );
                    $this->_redirect($this->formatURL('/remit_ratnakar_reports/distributorremittancereport?dur='.$qurStr['dur'].'&sub=1&id='.$qurStr['id'].'&bank_unicode='.$qurStr['bank_unicode'].'&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'])); 
                 }    
       }
     
     /* superdistributorremittancereportAction function will show the agent  remittance, refund, refund fee, service tax
     */
    public function superdistributorremittancereportAction()
    {   $user = Zend_Auth::getInstance()->getIdentity();
        $this->title = 'Remittance Report';
        // Get our form and validate it
        $form = new Remit_Ratnakar_SuperDistributorRemittanceReportForm(array('action' => $this->formatURL('/remit_ratnakar_reports/superdistributorremittancereport'),
                                                              'method' => 'POST',
                                             ));  
        $request = $this->getRequest();  
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['sub'] = $this->_getParam('sub');
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $qurStr['agent_id']  = $this->_getParam('agent_id');
        $qurStr['dist_id']  = $this->_getParam('dist_id');
        $qurStr['bank_unicode'] = $user->bank_unicode;
        $src = $this->_getParam('src');
               
        if($qurStr['sub']!=''){             
            if($form->isValid($qurStr)){ 
                //$qurData['agent_id'] = $user->id;
                $qurData['user_id'] = $user->id;
                $qurData['user_type'] = $user->user_type;
                $qurData['agent_id'] = $qurStr['agent_id'];
                $qurData['dist_id'] = $qurStr['dist_id'];
               
                if ($qurStr['dur'] != '') {
                    $durationArr = Util::getDurationDates($qurStr['dur']);
                    $qurData['duration'] = $qurStr['dur'];
                    
                    $objAgent = new Agents();
                    $agentInfo = $objAgent->findById($qurData['agent_id']);

                    $fromDate = explode(' ', Util::returnDateFormatted($durationArr['from'], "d-m-Y", "Y-m-d", "-"));
                    $toDate = explode(' ', Util::returnDateFormatted($durationArr['to'], "d-m-Y", "Y-m-d", "-"));

                    $this->view->agentInfo = $agentInfo;
                    $this->view->from = $fromDate[0];
                    $this->view->to   = $toDate[0];  
                    $this->view->title = $this->getReportTitle('Remittance Report', $qurStr['dur'], $qurData['agent_id']);
                
                 }else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                    $objBank = new Banks();
                    $bankInfo = $objBank->getBankbyUnicode($qurStr['bank_unicode']);
                    $this->view->title = 'Remittance Report of '.$bankInfo->name.' for '.Util::returnDateFormatted($qurData['from'], "Y-m-d", "d-m-Y", "-");
                    $this->view->title .= ' to '.Util::returnDateFormatted($qurData['to'], "Y-m-d", "d-m-Y", "-");
                    $this->view->from = $qurData['from'];
                    $this->view->to   = $qurData['to'];
                }
                
                $qurData['bank_unicode'] =  $qurStr['bank_unicode']; 
                $page = $this->_getParam('page');
                
                $objReports = new Remit_Reports();
                // $agentWiseTxns = $objReports->getAgentWiseRemittance($qurData);
                 $objRemitter = new Remit_Ratnakar_Remitter();
                 $agentWiseTxns = $objRemitter->getRatnakarMultiRemittance($qurData);
                 $paginator = $objReports->paginateByArray($agentWiseTxns, $page, $paginate = NULL);
                 $this->view->paginator=$paginator;
                
            } 
        }
        $this->view->form = $form;
        $this->view->formData = $qurStr; 
        $this->view->callingRprtDur = $qurStr['dur'];
        $this->view->src = $src;
    }
    
     /* exportsuperdistributorremittancereportAction function is responsible to create the csv file on fly with agent wise remittances,
     * refund, remitter fee, service tax report data and let user download that file.
     */
    
     public function exportsuperdistributorremittancereportAction(){
         $user = Zend_Auth::getInstance()->getIdentity();       
        // Get our form and validate it
        $form = new Remit_Ratnakar_DistributorRemittanceReportForm(array('action' => $this->formatURL('/remit_ratnakar_reports/exportsuperdistributorremittancereport'),
                                                              'method' => 'POST',
                                             )); 
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $qurStr['agent_id']  = $this->_getParam('agent_id');
        $qurStr['dist_id']  = $this->_getParam('dist_id');
        $qurStr['id'] = $user->id;
        $qurStr['bank_unicode'] = $user->bank_unicode;
      
        if($qurStr['id']>0 && $qurStr['dur']!='' || ($qurStr['to_date'] !='' && $qurStr['from_date']!='')){             
              if($form->isValid($qurStr)){ 
                //$qurData['agent_id'] = $user->id;
                $qurData['user_id'] = $user->id;
                $qurData['user_type'] = $user->user_type;
                $qurData['agent_id'] = $qurStr['agent_id'];
                $qurData['dist_id'] = $qurStr['dist_id'];
                if ($qurStr['dur'] != '') {
                   $qurData['duration'] = $qurStr['dur'];

                } else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                }
                 $qurData['bank_unicode'] =  $qurStr['bank_unicode']; 
                 $objReports = new Remit_Reports();
                 $exportData = $objReports->exportAgentWiseRemittanceFromSuperDistributorAgent($qurData);
               
                 $columns = array(
				    'Transaction Date',                                    
                                    'Distributor Name',	
                                    'Distributor Code',
                                    'Partner Name',
                                    'Partner Code',               
                                    'Partner City',
                                    'Transaction Narration',
                                    'Transaction Amount',                           
                                    'Mobile Number',
                                    'Transaction Ref Number',  
                                    'Current Transaction Status',
                                    'UTR No.',
                                   );
                                  
                 $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'agent_remittance');exit;
                 } catch (Exception $e) {
                                            App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                            $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                            $this->_redirect($this->formatURL('/remit_ratnakar_reports/superdistributorremittancereport?dur='.$qurStr['dur'].'&sub=1&id='.$qurStr['id'].'&bank_unicode='.$qurStr['bank_unicode'].'&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'])); 
                                        }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid data found') );
                         $this->_redirect($this->formatURL('/remit_ratnakar_reports/superdistributorremittancereport?dur='.$qurStr['dur'].'&sub=1&id='.$qurStr['id'].'&bank_unicode='.$qurStr['bank_unicode'].'&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'])); 
                      }             
          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Data missing') );
                    $this->_redirect($this->formatURL('/remit_ratnakar_reports/superdistributorremittancereport?dur='.$qurStr['dur'].'&sub=1&id='.$qurStr['id'].'&bank_unicode='.$qurStr['bank_unicode'].'&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'])); 
                 }    
       }
     
       
    /*
     * Remittance Commisson report agent wise
     */
    public function remittancecommissionAction(){
        $this->title = 'Remittance Commission Report';           
         // Get our form and validate it
        $form = new Remit_Ratnakar_RemittanceCommForm(array('action' => $this->formatURL('/remit_ratnakar_reports/remittancecommission'),
                                                    'method' => 'POST',
                                         )); 
             
        $user = Zend_Auth::getInstance()->getIdentity(); 
        $qurStr['id'] = $user->id;
        $qurStr['duration'] = $this->_getParam('duration');
        $qurStr['from_date'] = $this->_getParam('from_date');
        $qurStr['to_date'] = $this->_getParam('to_date');
        $qurStr['btn_submit'] = $this->_getParam('btn_submit');
        $qurStr['bank_unicode'] = $user->bank_unicode;
        $page = $this->_getParam('page');
        $objComm = new CommissionReport();
         
         if($qurStr['btn_submit']){     
            
              if($form->isValid($qurStr)){ 
                  if ($qurStr['duration'] != '') {
                 $pageTitle = $this->getReportTitle('Remittance Commission Report', $qurStr['duration'], $qurStr['id']);
                 
                 $durationArr = Util::getDurationDates($qurStr['duration']);
                 $qurData['from'] = $durationArr['from'];
                 $qurData['to'] = $durationArr['to'];  
                 
                 } else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                   
                    $objBank = new Banks();
                    $bankInfo = $objBank->getBankbyUnicode($qurStr['bank_unicode']);
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-","-",'to');
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-","-",'from');
                    $from = Util::returnDateFormatted($qurData['from'], "Y-m-d", "d-m-Y", "-");
                    $to = Util::returnDateFormatted($qurData['to'], "Y-m-d", "d-m-Y", "-");
                    $fromArr = explode(" ",$from);
                    $toArr = explode(" ",$to);
                    
                    
                    $pageTitle = 'Remittance Commission Report of '.$bankInfo->name.' for '.$fromArr[0];
                    $pageTitle .= ' to '.$toArr[0];
                 }
                 
                 
                 
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
         $form = new Remit_Ratnakar_RemittanceCommForm(array('action' => $this->formatURL('/remit_ratnakar_reports/exportremittancecommission'),
                                                    'method' => 'POST',
                                         )); 
         $qurStr['duration'] = $this->_getParam('duration');
         $objComm = new CommissionReport();
         $qurStr['bank_unicode'] = $user->bank_unicode;
         $qurStr['to_date']  = $this->_getParam('to_date');
         $qurStr['from_date']  = $this->_getParam('from_date');
         if($qurStr['duration'] != '' || ($qurStr['to_date'] !='' && $qurStr['from_date']!='')){           
                  if ($qurStr['duration'] != '') {
                    $durationArr = Util::getDurationDates($qurStr['duration']);
                    $qurData['from'] = $durationArr['from'];
                    $qurData['to'] = $durationArr['to'];
                } else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                }
                 $qurData['agent_id'] = $user->id; 
                 $qurData['txn_type'] = "'".TXNTYPE_REMITTER_REGISTRATION."','".TXNTYPE_REMITTANCE_FEE."','".TXNTYPE_REMITTANCE_REFUND_FEE."'";
                 $qurData['bank_unicode'] =  $qurStr['bank_unicode']; 
                 $exportData = $objComm->getCommission($qurData);
                
                 $columns = array(
                                    'Date',
                                    'Partner Code', 
                                    'Partner Name', 
                                    'Partner City', 
                                    'Partner Pincode', 
                                    'Transaction Narration',                                    
                                    'Product Code',
                                    'Transaction Amount',
                                    'Transaction Fee',
                                    'Transaction Service Tax',
                                    'Commission Plan',
                                    'Commission Amount',
                                    'Partner Saving Account No.',
                                    'Partner IFSC Code',
                                   
                                 );
                                  
                 $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'remittance_commission_report');exit;
                 } catch (Exception $e) {
                                            App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                            $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                             $this->_redirect($this->formatURL('/remit_ratnakar_reports/remittancecommission?duration='.$qurStr['duration'].'&sub=1&bank_unicode='.$qurStr['bank_unicode'].'&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'])); 
                                        }
                 
                           
          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Data missing') );
                    $this->_redirect($this->formatURL('/remit_ratnakar_reports/remittancecommission?duration='.$qurStr['duration'].'&sub=1&bank_unicode='.$qurStr['bank_unicode'].'&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'])); 
                 }    
       }
       
    /* distributormisreportAction function will show the agent  remittance, refund, refund fee, service tax
     */
    public function distributormisreportAction()
    {   
        $user = Zend_Auth::getInstance()->getIdentity();
        $this->title = 'MIS Report';
        // Get our form and validate it
        $form = new Remit_Ratnakar_DistributorMISReportForm(array('action' => $this->formatURL('/remit_ratnakar_reports/distributormisreport'),
                                                              'method' => 'POST',
                                             ));  
        $request = $this->getRequest();  
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['sub'] = $this->_getParam('sub');
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $qurStr['agent_id']  = $this->_getParam('agent_id');
        $qurStr['bank_unicode'] = $user->bank_unicode;
        $src = $this->_getParam('src');
               
        if($qurStr['sub']!=''){             
            if($form->isValid($qurStr)){ 
                $qurData['user_id'] = $user->id;
                $qurData['user_type'] = $user->user_type;
                $qurData['agent_id'] = $qurStr['agent_id'];
                if ($qurStr['dur'] != '') {
                    $durationArr = Util::getDurationDates($qurStr['dur']);
                    $qurData['duration'] = $qurStr['dur'];
                    
                    $objAgent = new Agents();
                    $agentInfo = $objAgent->findById($qurData['agent_id']);

                    $fromDate = explode(' ', Util::returnDateFormatted($durationArr['from'], "d-m-Y", "Y-m-d", "-"));
                    $toDate = explode(' ', Util::returnDateFormatted($durationArr['to'], "d-m-Y", "Y-m-d", "-"));

                    $this->view->agentInfo = $agentInfo;
                    $this->view->from = $fromDate[0];
                    $this->view->to   = $toDate[0];  
                    $this->view->title = $this->getReportTitle('Remittance MIS Report', $qurStr['dur'], $qurData['agent_id'],FALSE,$qurStr['bank_unicode']);
                
                 }else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-","-","to");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-","-","from");
                    $objBank = new Banks();
                    $bankInfo = $objBank->getBankbyUnicode($qurStr['bank_unicode']);
                    $this->view->title = 'Remittance MIS Report of '.$bankInfo->name.' for '.Util::returnDateFormatted($qurData['from'], "Y-m-d", "d-m-Y", "-");
                    $this->view->title .= ' to '.Util::returnDateFormatted($qurData['to'], "Y-m-d", "d-m-Y", "-");
                    $this->view->from = $qurData['from'];
                    $this->view->to   = $qurData['to'];
                }
                
                $qurData['bank_unicode'] =  $qurStr['bank_unicode']; 
                $qurData['dist_report'] = FLAG_YES;
                $page = $this->_getParam('page');

                
                 $objRemitter = new Remit_Ratnakar_Remitter();
                 $agentWiseTxns = $objRemitter->getRatnakarDistMultiMIS($qurData);
                // Util::debug($agentWiseTxns);exit;
              
                $paginator = $objRemitter->paginateByArray($agentWiseTxns, $page, $paginate = NULL);
                $this->view->paginator=$paginator;
                
            } 
        }
        $this->view->form = $form;
        $this->view->formData = $qurStr; 
        $this->view->callingRprtDur = $qurStr['dur'];
        $this->view->src = $src;
    }
    
       
     /* exportdistributormisreportAction function is responsible to create the csv file on fly with agent wise remittances,
     * refund, remitter fee, service tax report data and let user download that file.
     */
    
     public function exportdistributormisreportAction(){
         $user = Zend_Auth::getInstance()->getIdentity();       
        // Get our form and validate it
        $form = new Remit_Ratnakar_DistributorMISReportForm(array('action' => $this->formatURL('/remit_ratnakar_reports/exportdistributormisreport'),
                                                              'method' => 'POST',
                                             )); 
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $qurStr['agent_id']  = $this->_getParam('agent_id');
        $qurStr['dist_id']  = $this->_getParam('dist_id');
        $qurStr['bank_unicode'] = $user->bank_unicode;
      
        if($qurStr['dur']!='' || ($qurStr['to_date'] !='' && $qurStr['from_date']!='')){             
              if($form->isValid($qurStr)){ 
                 if ($qurStr['dur'] != '') {
                   $qurData['duration'] = $qurStr['dur'];

                } else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-","-","to");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-","-","from");
                }
                 $qurData['agent_id'] = $qurStr['agent_id'];
                 $qurData['dist_id'] = $qurStr['dist_id'];
                 $qurData['bank_unicode'] =  $qurStr['bank_unicode'];
                 $qurData['user_type'] = $user->user_type;
                 $qurData['user_id'] = $user->id;
                 $qurData['dist_report'] = FLAG_YES;
                 $objRemitter = new Remit_Ratnakar_Remitter();
                 $exportData = $objRemitter->getRatnakarDistMultiMIS($qurData);
                
                
                 $columns = array(
                    'Transaction Date',
                    'Partner Code',
                    'Partner Name',
                    'No. of Transactions',
                    'Total Transaction Amount',
                );

                $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'distributor_remittance_mis');exit;
                 } catch (Exception $e) {
                                            App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                            $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                            $this->_redirect($this->formatURL('/remit_ratnakar_reports/distributormisreport?dur='.$qurStr['dur'].'&sub=1&id='.$qurStr['id'].'&bank_unicode='.$qurStr['bank_unicode'].'&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&agent_id='.$qurStr['agent_id'])); 
                                        }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid data found') );
                         $this->_redirect($this->formatURL('/remit_ratnakar_reports/distributormisreport?dur='.$qurStr['dur'].'&sub=1&id='.$qurStr['id'].'&bank_unicode='.$qurStr['bank_unicode'].'&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&agent_id='.$qurStr['agent_id'])); 
                      }             
          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Data missing') );
                    $this->_redirect($this->formatURL('/remit_ratnakar_reports/distributormisreport?dur='.$qurStr['dur'].'&sub=1&id='.$qurStr['id'].'&bank_unicode='.$qurStr['bank_unicode'].'&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&agent_id='.$qurStr['agent_id'])); 
                 }    
       }
       
        /* superdistributorremittancereportAction function will show the agent  remittance, refund, refund fee, service tax
     */
    public function superdistributormisreportAction()
    {   $user = Zend_Auth::getInstance()->getIdentity();
   
        $this->title = 'MIS Report';
        // Get our form and validate it
        $form = new Remit_Ratnakar_SuperDistributorMISReportForm(array('action' => $this->formatURL('/remit_ratnakar_reports/superdistributormisreport'),
                                                              'method' => 'POST',
                                             ));  
        $request = $this->getRequest();  
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['sub'] = $this->_getParam('sub');
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $qurStr['agent_id']  = $this->_getParam('agent_id');
        $qurStr['dist_id']  = $this->_getParam('dist_id');
        $product = App_DI_Definition_BankProduct::getInstance(BANK_RATNAKAR_REMIT);
        $bankUnicode = $product->bank->unicode;
        $qurStr['bank_unicode'] = $bankUnicode;
        $src = $this->_getParam('src');
               
        if($qurStr['sub']!=''){             
            if($form->isValid($qurStr)){ 
               
                $qurData['user_id'] = $user->id;
                $qurData['user_type'] = $user->user_type;
                $qurData['agent_id'] = $qurStr['agent_id'];
                $qurData['dist_id'] = $qurStr['dist_id'];
                
                if ($qurStr['dur'] != '') {
                    $durationArr = Util::getDurationDates($qurStr['dur']);
                    $qurData['duration'] = $qurStr['dur'];
                    
                    $objAgent = new Agents();
                    $agentInfo = $objAgent->findById($qurData['agent_id']);

                    $fromDate = explode(' ', Util::returnDateFormatted($durationArr['from'], "d-m-Y", "Y-m-d", "-"));
                    $toDate = explode(' ', Util::returnDateFormatted($durationArr['to'], "d-m-Y", "Y-m-d", "-"));

                    $this->view->agentInfo = $agentInfo;
                    $this->view->from = $fromDate[0];
                    $this->view->to   = $toDate[0];  
                    $this->view->title = $this->getReportTitle('Remittance MIS Report', $qurStr['dur'], $qurData['agent_id'],FALSE,$qurStr['bank_unicode']);
                
                 }else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                     
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-","-","to");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-","-","from");
                    $objBank = new Banks();
                    $bankInfo = $objBank->getBankbyUnicode($qurStr['bank_unicode']);
                    $this->view->title = 'Remittance  MIS Report of '.$bankInfo->name.' for '.Util::returnDateFormatted($qurData['from'], "Y-m-d", "d-m-Y", "-");
                    $this->view->title .= ' to '.Util::returnDateFormatted($qurData['to'], "Y-m-d", "d-m-Y", "-");
                    $this->view->from = $qurData['from'];
                    $this->view->to   = $qurData['to'];
                  
                }
                
                $qurData['bank_unicode'] =  $qurStr['bank_unicode']; 
               

                 $objRemitter = new Remit_Ratnakar_Remitter();
                 
                try {
                    $agentWiseTxns = $objRemitter->getRatnakarMultiMIS($qurData);
                 } catch (Exception $e) {
                    App_Logger::log($e->getMessage() , Zend_Log::ERR);
                    $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                    $this->_redirect($this->formatURL('/remit_ratnakar_reports/superdistributormisreport?dur='.$qurStr['dur'].'&agent_id='.$qurStr['agent_id'].'&dist_id='.$qurStr['dist_id'].'&bank_unicode='.$qurStr['bank_unicode'].'&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date']));
                }                
                $paginator = $objRemitter->paginateByArray($agentWiseTxns, $page, $paginate = NULL);
                $this->view->paginator=$paginator;
                
            } 
        }
        $this->view->form = $form;
        $this->view->formData = $qurStr; 
        $this->view->callingRprtDur = $qurStr['dur'];
        $this->view->src = $src;
    }
    
     /* exportsuperdistributorremittancereportAction function is responsible to create the csv file on fly with agent wise remittances,
     * refund, remitter fee, service tax report data and let user download that file.
     */
    
     public function exportsuperdistributormisreportAction(){
        $user = Zend_Auth::getInstance()->getIdentity();       
        // Get our form and validate it
        $form = new Remit_Ratnakar_SuperDistributorMISReportForm(array('action' => $this->formatURL('/remit_ratnakar_reports/exportsuperdistributormisreport'),
                                                              'method' => 'POST',
                                             )); 
        
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $qurStr['agent_id']  = $this->_getParam('agent_id');
        $qurStr['dist_id']  = $this->_getParam('dist_id');
        $qurStr['bank_unicode'] = $user->bank_unicode;
      
        if( $qurStr['dur']!='' || ($qurStr['to_date'] !='' && $qurStr['from_date']!='')){             
              if($form->isValid($qurStr)){ 
              
                if ($qurStr['dur'] != '') {
                   $qurData['duration'] = $qurStr['dur'];

                } else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-","-","to");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-","-","from");
                }
                 $qurData['agent_id'] = $qurStr['agent_id'];
                 $qurData['dist_id'] = $qurStr['dist_id'];
                 $qurData['bank_unicode'] =  $qurStr['bank_unicode'];
                 $qurData['user_type'] = $user->user_type;
                 $qurData['user_id'] = $user->id;
                 $objRemitter = new Remit_Ratnakar_Remitter();
                 $exportData = $objRemitter->getRatnakarMultiMIS($qurData);
               
                 $columns = array(
                    'Transaction Date',
                    'Distributor Code',
                    'Distributor Name',
                    'Partner Code',
                    'Partner Name',
                    'Transaction Count',
                    'Total Transaction Amount',
                );

                $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'agent_remittance_mis');exit;
                 } catch (Exception $e) {
                                            App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                            $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                            $this->_redirect($this->formatURL('/remit_ratnakar_reports/superdistributormisreport?dur='.$qurStr['dur'].'&sub=1&agent_id='.$qurStr['agent_id'].'&dist_id='.$qurStr['dist_id'].'&bank_unicode='.$qurStr['bank_unicode'].'&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'])); 
                                        }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid data found') );
                         $this->_redirect($this->formatURL('/remit_ratnakar_reports/superdistributormisreport?dur='.$qurStr['dur'].'&sub=1&agent_id='.$qurStr['agent_id'].'&dist_id='.$qurStr['dist_id'].'&bank_unicode='.$qurStr['bank_unicode'].'&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'])); 
                      }             
          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Data missing') );
                    $this->_redirect($this->formatURL('/remit_ratnakar_reports/superdistributormisreport?dur='.$qurStr['dur'].'&sub=1&agent_id='.$qurStr['agent_id'].'&dist_id='.$qurStr['dist_id'].'&bank_unicode='.$qurStr['bank_unicode'].'&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'])); 
                 }    
       }
      
  }
