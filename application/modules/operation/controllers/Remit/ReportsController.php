<?php
/**
 * MVC Axis Bank Reports
 *
 * @package frontend_controllers
 * @copyright company
 */

class Remit_ReportsController extends App_Operation_Controller
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
    
    /* remitterregnAction: load / reload report for all agents
     * takes duration as argument, currently yesterday / today / WTD / MTD
     */
    public function remitterregnAction(){
        $this->title = 'Remitter Registration Report';  
        // Get our form and validate it
        $form = new Remit_RemitterRegnForm(array(
            'action' => $this->formatURL('/remit_reports/remitterregn'),
            'method' => 'POST'
        ));
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        $qurStr['duration'] = $this->_getParam('duration');
        $qurStr['btn_submit'] = $this->_getParam('sub');
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $qurStr['mobile_no']  = $this->_getParam('mobile_no');
        $qurStr['submit'] = $this->_getParam('submit');
        $page = $this->_getParam('page');
        if($qurStr['btn_submit']) {
            if($form->isValid($qurStr)){
                if ($qurStr['duration'] != ''){
                    $durationArr = Util::getDurationDates($qurStr['duration']);
                    $qurData['from'] = $durationArr['from'];
                    $qurData['to'] = $durationArr['to'];                 

                    $fromDate = explode(' ', Util::returnDateFormatted($durationArr['from'], "d-m-Y", "Y-m-d", "-"));
                    $toDate = explode(' ', Util::returnDateFormatted($durationArr['to'], "d-m-Y", "Y-m-d", "-"));
                    $this->view->from = $fromDate[0];
                    $this->view->to   = $toDate[0];                 
                    $this->view->title = $this->getReportTitle(
                            'Remitter Registration Report', $qurStr['duration'],0,FALSE,$qurStr['bank_unicode']
                            );
                } else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $objBank = new Banks();
                    $bankInfo = $objBank->getBankbyUnicode($qurStr['bank_unicode']);
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                    $this->view->title = 'Remitter Registration Report of '. $bankInfo->name.' for '.Util::returnDateFormatted($qurData['from'], "Y-m-d", "d-m-Y", "-");
                    $this->view->title .= ' to '.Util::returnDateFormatted($qurData['to'], "Y-m-d", "d-m-Y", "-");
                    $this->view->from = $qurData['from'];
                    $this->view->to   = $qurData['to'];
                }

                $qurData['mobile_no'] =  $qurStr['mobile_no'];
                $qurData['bank_unicode'] =  $qurStr['bank_unicode'];
                $remitReportModel = new Remit_Reports();
                $dataArr = Util::toArray($remitReportModel->getRemitterRegistrations($qurData, $this->_getPage()));
                $dataArr = $remitReportModel->paginateByArray($dataArr, $page, $paginate = NULL);
                $this->view->btnSubmit = $qurStr['btn_submit'];
                $this->view->paginator = $dataArr;
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
    
    /* exportremitterregnAction function is responsible to create the csv file on fly with remitter regn data
     * and let user download that file.
     */
    
    public function exportremitterregnAction(){
        $qurStr['duration'] = $this->_getParam('duration');
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        $qurStr['mobile_no'] = $this->_getParam('mobile_no');
        $form = new Remit_RemitterRegnForm(array(
            'action' => $this->formatURL('/remit_reports/remitterregn'),
            'method' => 'POST',
        )); 
        
        if($qurStr['duration']!='' || $qurStr['to_date'] !='' && $qurStr['from_date']!='') {
            if($form->isValid($qurStr)){ 
                if($qurStr['duration']!=''){ 
                    $durationArr = Util::getDurationDates($qurStr['duration']);
                    $qurData['from'] = $durationArr['from'];
                    $qurData['to'] = $durationArr['to'];  
                } else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                }
                $qurData['bank_unicode'] =  $qurStr['bank_unicode'];  
                $qurData['mobile_no'] =  $qurStr['mobile_no'];
                $remitReportModel = new Remit_Reports();
                $exportData = Util::toArray($remitReportModel->getRemitterRegistrations($qurData));
                $j = 0;
                foreach($exportData as $data){
                    $formattedArr[$j] = array(
                        'date_created' => $data['date_created'],
                        'sup_dist_code' => $data['sup_dist_code'],
                        'sup_dist_name' => $data['sup_dist_name'],
                        'dist_code' => $data['dist_code'],
                        'dist_name' => $data['dist_name'],
                        'name' => $data['name'],
                        'mobile' => $data['mobile'],
                        'agent_code' => $data['agent_code'],
                        'agent_name' => $data['agent_name'],
                        'estab_city' => $data['estab_city'],
                        'estab_state' => $data['estab_state'],
                        'product_name' => $data['product_name'],
                        'bank_name' => $data['bank_name'],
                        'address' => $data['address']                            
                    );
                    $j++;
                }
                $columns = array(
                    'Date',
                    'Super Distributor Code',
                    'Super Distributor Name',
                    'Distributor Code',
                    'Distributor Name',
                    'Remitter Name', 
                    'Mobile Number',
                    'Agent Code',
                    'Agent Name',
                    'Agent City',
                    'Agent State',
                    'Product Name',
                    'Bank Name',
                    'Remitter  Address',
                );
                                  
                $objCSV = new CSV();
                try{
                    $resp = $objCSV->export($formattedArr, $columns, 'remitter_registration');exit;
                }catch (Exception $e) {
                    App_Logger::log($e->getMessage() , Zend_Log::ERR);
                    $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                    $this->_redirect($this->formatURL('/remit_reports/remitterregn?duration='.$qurStr['duration'].'&bank_unicode='.$qurStr['bank_unicode'].'&sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'])); 
                }  
            } else {
                $this->_helper->FlashMessenger( array('msg-error' => 'Invalid Params!') );
                $this->_redirect($this->formatURL('/remit_reports/remitterregn?duration='.$qurStr['duration'].'&bank_unicode='.$qurStr['bank_unicode'].'&sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'])); 
            }             
        } else {
            $this->_helper->FlashMessenger( array('msg-error' => 'Invalid Params!') );
            $this->_redirect($this->formatURL('/remit_reports/remitterregn?duration='.$qurStr['duration'].'&bank_unicode='.$qurStr['bank_unicode'].'&sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'])); 
        }    
    }
       
    public function remittancereportAction(){
        $this->title = 'Remittance Transaction Report';  
        // Get our form and validate it
        $form = new Remit_RemittanceReportForm(array('action' => $this->formatURL('/remit_reports/remittancereport'),
                                               'method' => 'POST',
                                       ));  
        $fileObject = new Files();
        $sub = $this->_getParam('sub');
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
	$qurStr['product_id'] = $this->_getParam('product_id');
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $qurStr['mobile_no']  = $this->_getParam('mobile_no');
        $qurStr['txn_no']  = $this->_getParam('txn_no');
        $page = $this->_getParam('page');
        
        if($sub!=''){  
                  
            if($form->isValid($qurStr)){ 
                //$request = $this->_request->getRawBody(); 
                //print_r($request);exit;
                $durationArr = Util::getDurationDates($qurStr['dur']);
                    if ($qurStr['dur'] != '') {
                        $qurData['duration'] = $qurStr['dur'];
                        $fromDate = explode(' ', Util::returnDateFormatted($durationArr['from'], "d-m-Y", "Y-m-d", "-"));
                        $toDate = explode(' ', Util::returnDateFormatted($durationArr['to'], "d-m-Y", "Y-m-d", "-"));
                        $this->view->title = $this->getReportTitle('Remittance Transaction Report', $qurStr['dur'],0,FALSE,$qurStr['bank_unicode']);
                        $this->view->from = $fromDate[0];
                        $this->view->to   = $toDate[0];    
                        $qurData['from'] = Util::returnDateFormatted($fromDate[0], "d-m-Y", "Y-m-d", "-");
                        $qurData['to'] = Util::returnDateFormatted($toDate[0], "d-m-Y", "Y-m-d", "-");
                    } else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                        $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                        $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                        $qurStr['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                        $qurStr['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                        $objBank = new Banks();
                        $bankInfo = $objBank->getBankbyUnicode($qurStr['bank_unicode']);
                        $this->view->title = 'Remittance Transaction Report of '.$bankInfo->name.' for '.Util::returnDateFormatted($qurStr['from'], "Y-m-d", "d-m-Y", "-");
                        $this->view->title .= ' to '.Util::returnDateFormatted($qurStr['to'], "Y-m-d", "d-m-Y", "-");
                        $this->view->from = $qurStr['from'];
                        $this->view->to   = $qurStr['to'];
                    }
                
                    $qurData['bank_unicode'] =  $qurStr['bank_unicode'];   
		    $qurData['product_id'] =  $qurStr['product_id'];  
                    $qurData['mobile_no'] = $qurStr['mobile_no'];
                    $qurData['txn_no'] = $qurStr['txn_no'];
                    $qurData['status'] = STATUS_PENDING;
                   
                    $objReports = new Remit_Reports();
                    $return = $objReports->SaveRemittanceTxnDetails($qurData);
                    
                    if($return['status'] == 'in_process'){
                        $msg = 'Request is being processed. Please check in sometime.'; 
                        $this->view->recordexistpaginator = $return['rs'];
                    }
                    elseif($return['status'] == 'submitted'){
                        $msg = 'Request is submitted successfully. Please check in sometime.'; 
                    }
                    elseif($return['status'] == 'processed'){
                        $msg = 'Request has already been processed.'; 
                        $this->view->recordexistpaginator = $return['rs'];
                    }
                    
                    if($msg != ''){
                        $this->_helper->FlashMessenger(
                            array( 'msg-success' => $msg )
                        );
                    }
                }
            }
	    $form->getElement('product')->setValue($qurStr['product_id']);
            $this->view->form = $form;
            $this->view->formData = $qurStr;
            $this->view->paginator = $fileObject->getRemittanceReportByLabel(REMITTANCE_TRANSACTION_FILE, $page);  
    }
         
   
    /* exportremittancereportAction function is responsible to create the csv file on fly with agent load/reload/remittance txns report data
     * and let user download that file.
     */
    
     public function exportremittancereportAction(){
set_time_limit(0);
ini_set("memory_limit","800M");
       //ini_set('max_execution_time' 600);
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $qurStr['mobile_no'] =  $this->_getParam('mobile_no');
        $qurStr['txn_no'] =  $this->_getParam('txn_no');
                 
        $form = new Remit_RemittanceReportForm(array('action' => $this->formatURL('/remit_reports/exportremittancereport'),
                                              'method' => 'POST',
                                       )); 
        $bankBoi = App_DI_Definition_Bank::getInstance(BANK_BOI);
        $bankBoiUnicode = $bankBoi->bank->unicode;
        
        /**
        *  Start code: Getting Ratnakar Bank Unicode 
        */
        $RatnakarBank = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR);
        $bankRatnakarUnicode = $RatnakarBank->bank->unicode;
        /**
        * End code: Getting Ratnakar Bank Unicode 
        */
         if($qurStr['dur']!='' || ($qurStr['to_date'] !='' && $qurStr['from_date']!='')){    
             
              if($form->isValid($qurStr)){ 
                 $durationArr = Util::getDurationDates($qurStr['dur']);
                   if ($qurStr['dur'] != '') {
                    $qurData['duration'] = $qurStr['dur'];
                    
                } else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                    
                    
                }
                
                 $qurData['bank_unicode'] =  $qurStr['bank_unicode'];
                 $qurData['mobile_no'] =  $qurStr['mobile_no'];
                 $qurData['txn_no'] =  $qurStr['txn_no'];
                 
                 $objReports = new Remit_Reports();
                 $exportData = $objReports->exportRemittance($qurData);
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
                                    'Current Transaction Status',
                                    'Reason',
                                    'Reason Code'
                                 );
                 if(( $qurStr['bank_unicode'] == $bankRatnakarUnicode) ){
                     array_push($columns,'UTR No');
                    
                 }
                 if(( $qurStr['bank_unicode'] == $bankBoiUnicode) || ( $qurStr['bank_unicode'] == $bankRatnakarUnicode) ){
                     array_push($columns,'Batch Name');
                    
                 }

                 $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'remittance_transaction');exit;
                 }catch (Exception $e) {
                                         App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                         $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                         $this->_redirect($this->formatURL('/remit_reports/exportremittancereport?dur='.$qurStr['dur'].'&bank_unicode='.$qurStr['bank_unicode'].'&sub=1')); 
                                       }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid duration!') );
                         $this->_redirect($this->formatURL('/remit_reports/exportremittancereport?dur='.$qurStr['dur'].'&bank_unicode='.$qurStr['bank_unicode'].'&sub=1')); 
                      }             
          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Invalid duration!') );
                    $this->_redirect($this->formatURL('/remit_reports/exportremittancereport?dur='.$qurStr['dur'].'&bank_unicode='.$qurStr['bank_unicode'].'&sub=1')); 
                 }    
       }
   
       
        /* agentwiseremittancereportAction function will show the agent  remittance, refund, refund fee, service tax
     */
    public function agentwiseremittancereportAction()
    {   
        $this->title = 'Agent Wise Remittance Report';
        // Get our form and validate it
        $form = new Remit_AgentWiseRemittanceForm(array('action' => $this->formatURL('/remit_reports/agentwiseremittancereport'),
                                              'method' => 'POST',
                                       ));   
        $request = $this->getRequest();  
        //$formData  = $this->_request->getPost();//$form->getValues();//
        $qurStr['id'] = $this->_getParam('id');
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['sub'] = $this->_getParam('sub');
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        $src = $this->_getParam('src');
               
         if($qurStr['sub']!=''){             
              if($form->isValid($qurStr)){ 
                 $qurData['agent_id'] = $qurStr['id'];
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
                 $this->view->title = $this->getReportTitle('Remittance Report', $qurStr['dur'], $qurData['agent_id'],FALSE,$qurStr['bank_unicode']);
                 $page = $this->_getParam('page');
                
                 $objReports = new Remit_Reports();
                 $agentWiseTxns = $objReports->getAgentWiseRemittance($qurData);
                 $paginator = $objReports->paginateByArray($agentWiseTxns, $page, $paginate = NULL);
                 $form->getElement('agent_id')->setValue($qurData['agent_id']);
                 $this->view->paginator=$paginator;
                
              } 
          }
            $this->view->form = $form;
            $this->view->formData = $qurStr; 
            //$this->view->btnSubmit = $btnSubmit;
            $this->view->callingRprtDur = $qurStr['dur'];
            $this->view->src = $src;
        } 
        
        
        
        
     /* exportagentwiseremittancereportAction function is responsible to create the csv file on fly with agent wise remittances,
     * refund, remitter fee, service tax report data and let user download that file.
     */
    
     public function exportagentwiseremittancereportAction(){
        
        // Get our form and validate it
        $form = new Remit_AgentWiseRemittanceForm(array('action' => $this->formatURL('/remit_reports/exportagentwiseremittancereport'),
                                                              'method' => 'POST',
                                             )); 
        //$formData  = $this->_request->getPost();//$form->getValues();//
        $qurStr['id'] = $this->_getParam('id');
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        $bankBoi = App_DI_Definition_Bank::getInstance(BANK_BOI);
        $bankBoiUnicode = $bankBoi->bank->unicode;
        $RatnakarBank = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR);
        $bankRatnakarUnicode = $RatnakarBank->bank->unicode;
         if($qurStr['id']>0 && $qurStr['dur']!=''){             
              if($form->isValid($qurStr)){ 
                 $qurData['agent_id'] = $qurStr['id'];
                 $durationArr = Util::getDurationDates($qurStr['dur']);
                 $qurData['duration'] = $qurStr['dur'];
                 $qurData['bank_unicode'] =  $qurStr['bank_unicode'];  
                 //$qurData['from'] = $durationArr['from'];
                 //$qurData['to'] = $durationArr['to'];                 
                 
                 $objReports = new Remit_Reports();
                 $exportData = $objReports->exportAgentWiseRemittance($qurData);
               
                 $columns = array(
                                    'Date',                                    
                                    'Agent Name',
                                    'Agent Code',
                                    'Agent City',
                                    'Agent Pincode',
                                    'Transaction Naration',
                                    'Transaction Amount',                           
                                    'Mobile Number',
                                    'Transaction Ref Number',
                                    'Super Distributor Code',
                                    'Super Distributor Name',
                                    'Distributor Code',
                                    'Distributor Name',
                                    'UTR No.',
                                    'Remitter Name',
                                    'Remitter Mobile No',
                                    'Remitter Email',
                                    'Remitter Registration Date',
                                    'Bene Name',
                                    'Bene Bank Name',
                                    'Bene IFSC Code',
                                    'Current Transaction Status',
                                    'Response file - Returned date',
                                    'Rejection code', 
                                    'Rejection remarks',
                                    'Commission Plan',
                                    'Transaction Fee',
                                    'Transaction Service Tax',
                                    'Commission Amount'
                                   );
                  if( ($qurStr['bank_unicode'] == $bankBoiUnicode) || ($qurStr['bank_unicode'] == $bankRatnakarUnicode) ){
                     array_push($columns,'Batch Name');
                     array_push($columns,'Batch Date');
                     array_push($columns,'Batch Time');
                  }
                 $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'agent_wise_remittance');exit;
                 } catch (Exception $e) {
                                            App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                            $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                            $this->_redirect($this->formatURL('/remit_reports/exportagentwiseremittancereport?dur='.$qurStr['dur'].'&sub=1&id='.$qurStr['id'].'&bank_unicode='.$qurStr['bank_unicode'])); 
                                        }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid data found') );
                         $this->_redirect($this->formatURL('/remit_reports/exportagentwiseremittancereport?dur='.$qurStr['dur'].'&sub=1&id='.$qurStr['id'].'&bank_unicode='.$qurStr['bank_unicode'])); 
                      }             
          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Data missing') );
                    $this->_redirect($this->formatURL('/remit_reports/exportagentwiseremittancereport?dur='.$qurStr['dur'].'&sub=1&id='.$qurStr['id'].'&bank_unicode='.$qurStr['bank_unicode'])); 
                 }    
       }
       
     /*
     * Remittance Commisson report for all agents
     */

    public function remittancecommissionAction() {
        $this->title = 'Remittance Commission Report';
        // Get our form and validate it
        $form = new Remit_RemittanceCommForm(array('action' => $this->formatURL('/remit_reports/remittancecommission'),
            'method' => 'POST',
        ));


        $qurStr['duration'] = $this->_getParam('duration');
        $qurStr['btn_submit'] = $this->_getParam('btn_submit');
        $qurStr['submit'] = $this->_getParam('submit');
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $page = $this->_getParam('page');


        if ($qurStr['btn_submit']) {
            if ($form->isValid($qurStr)) {

                
                $objComm = new CommissionReport();
                if ($qurStr['duration'] != '') {
                   $pageTitle = $this->getReportTitle('Remittance Commission Report', $qurStr['duration'], 0, FALSE, $qurStr['bank_unicode']);

                    $durationArr = Util::getDurationDates($qurStr['duration']);
                    $qurData['from'] = $durationArr['from'];
                    $qurData['to'] = $durationArr['to'];
                } else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $objBank = new Banks();
                    $bankInfo = $objBank->getBankbyUnicode($qurStr['bank_unicode']);
                    
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                    $pageTitle = 'Remittance Commission Report of '.$bankInfo->name.' for '.Util::returnDateFormatted($qurData['from'], "Y-m-d", "d-m-Y", "-");
                    $pageTitle .= ' to '.Util::returnDateFormatted($qurData['to'], "Y-m-d", "d-m-Y", "-");
                 
                }
                $qurData['agent_id'] = 0;
                $qurData['bank_unicode'] = $qurStr['bank_unicode'];
                $qurData['txn_type'] = "'" . TXNTYPE_REMITTER_REGISTRATION . "','" . TXNTYPE_REMITTANCE_FEE . "','" . TXNTYPE_REMITTANCE_REFUND_FEE . "'";
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
     * Export Consolidated Remittance Commisson report for all agents
     */
    
     public function exportremittancecommissionAction() {

        // Get our form and validate it
        $form = new Remit_RemittanceCommForm(array('action' => $this->formatURL('/remit_reports/remittancecommission'),
            'method' => 'POST',
        ));
        $qurStr['duration'] = $this->_getParam('duration');
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $objComm = new CommissionReport();


        if ($qurStr['duration'] != '' || ($qurStr['to_date'] !='' && $qurStr['from_date']!='')) {
            if ($form->isValid($qurStr)) {

                if ($qurStr['duration'] != '') {
                    $durationArr = Util::getDurationDates($qurStr['duration']);
                    $qurData['from'] = $durationArr['from'];
                    $qurData['to'] = $durationArr['to'];
                } else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-","-",'to');
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-",'-','from');
                }
                $qurData['txn_type'] = "'" . TXNTYPE_REMITTER_REGISTRATION . "','" . TXNTYPE_REMITTANCE_FEE . "','" . TXNTYPE_REMITTANCE_REFUND_FEE . "'";
                $qurData['bank_unicode'] = $qurStr['bank_unicode'];
                $exportData = $objComm->getCommission($qurData);

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
                    'Transaction Narration',
                    'Transaction Amount',
                    'Transaction Fee',
                    'Transaction Service Tax',
                    'Commission Plan',
                    'Commission Amount',
                    'Agent Saving Account No',
                    'Agent IFSC Code',
                );

                $objCSV = new CSV();
                try {
                    $resp = $objCSV->export($exportData, $columns, 'remittance_commission_report');
                    exit;
                } catch (Exception $e) {
                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage(),));
                    $this->_redirect($this->formatURL('/remit_reports/remittancecommission?duration=' . $qurStr['duration'] . '&sub=1&bank_unicode='.$qurStr['bank_unicode'].'&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date']));
                }
            } else {
                $this->_helper->FlashMessenger(array('msg-error' => 'Invalid data found'));
                $this->_redirect($this->formatURL('/remit_reports/remittancecommission?duration=' . $qurStr['duration'] . '&sub=1&bank_unicode='.$qurStr['bank_unicode'].'&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date']));
            }
        } else {
            $this->_helper->FlashMessenger(array('msg-error' => 'Data missing'));
            $this->_redirect($this->formatURL('/remit_reports/remittancecommission?duration=' . $qurStr['duration'] . '&sub=1&bank_unicode='.$qurStr['bank_unicode'].'&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date']));
        }
    }

    /*
     * Remittance Commisson report agent wise
     */
    public function agentwiseremittancecommissionAction(){
        $this->title = 'Agent Wise Remittance Commission Report';           
         // Get our form and validate it
        $form = new Remit_AgentWiseRemittanceCommForm(array('action' => $this->formatURL('/remit_reports/agentwiseremittancecommission'),
                                                    'method' => 'POST',
                                         )); 
             
        
        $qurStr['id'] = $this->_getParam('id');
        $qurStr['duration'] = $this->_getParam('duration');
        $qurStr['btn_submit'] = $this->_getParam('btn_submit');
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        
        $page = $this->_getParam('page');
        
         
         if($qurStr['btn_submit']){     
            
              if($form->isValid($qurStr)){ 
                 
                 $pageTitle = $this->getReportTitle('Remittance Commission Report', $qurStr['duration'], $qurStr['id'],FALSE,$qurStr['bank_unicode']);
                 
                 $objComm = new CommissionReport();
                 $durationArr = Util::getDurationDates($qurStr['duration']);
                 $qurData['from'] = $durationArr['from'];
                 $qurData['to'] = $durationArr['to'];  
                 $qurData['agent_id'] = $qurStr['id'];  
                 $qurData['txn_type'] = "'".TXNTYPE_REMITTER_REGISTRATION."','".TXNTYPE_REMITTANCE_FEE."','".TXNTYPE_REMITTANCE_REFUND_FEE."'";
                 $qurData['bank_unicode'] = $qurStr['bank_unicode'];
                 $commArr = $objComm->getCommission($qurData);
                 
                 $paginator = $objComm->paginateByArray($commArr, $page, $paginate = NULL);
                 $this->view->paginator = $paginator;
                 $this->view->pageTitle = $pageTitle;
                 $this->view->btnSubmit = $qurStr['btn_submit'];
                 $form->getElement('agent_id')->setValue($qurData['agent_id']);
               }   
               
          }
            $this->view->form = $form;
            $this->view->formData = $qurStr; 
            
    }
    
        /*
     * Export Consolidated Remittance Commisson report for agent wise
     */
    
     public function exportagentwiseremittancecommissionAction(){
        
         // Get our form and validate it
         $form = new CommReportForm(array('action' => $this->formatURL('/remit_reports/agentwiseremittancecommission'),
                                                    'method' => 'POST',
                                         )); 
         $qurStr['duration'] = $this->_getParam('duration');
         $qurStr['agent_id'] = $this->_getParam('id');
         $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
         $objComm = new CommissionReport();
        
         
         if($qurStr['duration']!='' ){           
              if($form->isValid($qurStr)){ 
                 
                 $durationArr = Util::getDurationDates($qurStr['duration']);
                 $qurData['from'] = $durationArr['from'];
                 $qurData['to'] = $durationArr['to'];  
                 $qurData['agent_id'] = $qurStr['agent_id']; 
                 $qurData['txn_type'] = "'".TXNTYPE_REMITTER_REGISTRATION."','".TXNTYPE_REMITTANCE_FEE."','".TXNTYPE_REMITTANCE_REFUND_FEE."'";
                 $qurData['bank_unicode'] =  $qurStr['bank_unicode']; 
                 $exportData = $objComm->getCommission($qurData);
                
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
    'Transaction Narration',
    'Transaction Amount',
    'Transaction Fee',
    'Transaction Service Tax',
    'Commission Plan',
    'Commission Amount',
    'Agent Saving Account No',
    'Agent IFSC Code',
                                   
                                 );
                                  
                 $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'agent_wise_remittance_commission_report');exit;
                 } catch (Exception $e) {
                                            App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                            $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                             $this->_redirect($this->formatURL('/remit_reports/agentwiseremittancecommission?duration='.$qurStr['duration'].'&bank_unicode='.$qurStr['bank_unicode'].'&sub=1&bank_unicode='.$qurStr['bank_unicode'])); 
                                        }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid data found') );
                         $this->_redirect($this->formatURL('/remit_reports/agentwiseremittancecommission?duration='.$qurStr['duration'].'&bank_unicode='.$qurStr['bank_unicode'].'&sub=1&bank_unicode='.$qurStr['bank_unicode'])); 
                      }             
          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Data missing') );
                    $this->_redirect($this->formatURL('/remit_reports/agentwiseremittancecommission?duration='.$qurStr['duration'].'&bank_unicode='.$qurStr['bank_unicode'].'&sub=1&bank_unicode='.$qurStr['bank_unicode'])); 
                 }    
       }
       
       
       
        /* remittertransactionAction function is responsible to generate remitter transactions report
        * on duration and remitter id and duration basis
        */
       
        public function remittertransactionAction(){
        $this->title = 'Remitter Transactions Report';                         
         // Get our form and validate it
         $form = new Remit_RemitterTransactionForm(array('action' => $this->formatURL('/remit_reports/remittertransaction'),
                                                    'method' => 'POST',
                                             )); 
        
        $request = $this->_getAllParams();
        //$sub = $this->_getParam('sub');
        //$qurStr['phone']  = $this->_getParam('phone');
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['sub'] = $this->_getParam('sub');
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        $page = $this->_getParam('page');
        
        if($qurStr['sub']!=''){
              if($form->isValid($qurStr)){ 
        
       // $objRemit = new Remit_Boi_Remitter();
        try{
            //$remitterArr = $objRemit->getRemitter($qurStr['phone']);
            $queryParam = array(
                                      //  'remitter_id'=>$remitterArr->id,
                                        'duration'=>$qurStr['dur'],
                                        'bank_unicode' => $qurStr['bank_unicode']
                                    );
                // $remitInfo = $objRemit->getRemitterById($remitterArr->id);
                 //$title='Remittance Transaction Report For '. $remitInfo->name; 
                 $title='Remitters Transaction Report'; 
                 $this->view->title = $this->getReportTitle($title, $qurStr['dur'],0,FALSE,$qurStr['bank_unicode']);
                 
                 $objReports = new Remit_Reports();
                 $rptData = $objReports->getRemitterTransactions($queryParam);
                 
                 $paginator = $objReports->paginateByArray($rptData, $page, $paginate = NULL);
                 $this->view->paginator = $paginator;
                 $this->view->formData = $qurStr;
                 $this->view->sub = $qurStr['sub'];
          
        }
         catch (Exception $e ) {
                   App_Logger::log($e->getMessage(), Zend_Log::ERR);
                   $errMsg = $e->getMessage();
                   
                        $this->_helper->FlashMessenger(
                            array(
                                    'msg-error' => $errMsg,
                                 )
                            );
                }  
             
               
                
                
              } 
        }
         // }
            $this->view->form = $form;
            
    }
    
    
    
    /* exportremittertransactionAction() is responsible to generate remitter transaction report export csv data
        *  param :- duration and remitter id
        */
    
     public function exportremittertransactionAction(){
        
        // Get our form and validate it
        $form = new Remit_RemitterTransactionForm(array('action' => $this->formatURL('/remit_reports/remittertransaction'),
                                                    'method' => 'POST',
                                             )); 
   
       // $qurStr['phone'] = $this->_getParam('phone');
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');       
         if($qurStr['dur']!=''){             
              if($form->isValid($qurStr)){ 
                  
                $bankBoi = App_DI_Definition_Bank::getInstance(BANK_BOI);
                $bankBoiUnicode = $bankBoi->bank->unicode;

                /**
                *  Start code: Getting Ratnakar Bank Unicode 
                */
                $RatnakarBank = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR);
                $bankRatnakarUnicode = $RatnakarBank->bank->unicode;
                /**
                * End code: Getting Ratnakar Bank Unicode 
                */
                 $durationArr = Util::getDurationDates($qurStr['dur']);
                 $qurData['duration'] = $qurStr['dur'];
                 $qurData['bank_unicode'] =  $qurStr['bank_unicode'];  
                 $agentInfo = Array();
                 //$qurData['from'] = $durationArr['from'];
                 //$qurData['to'] = $durationArr['to'];                 
                 
                        
                 $objReports = new Remit_Reports();
                 $exportData = $objReports->exportRemitterTransactions($qurData);
                 $columns = array(
                                    'Transaction Date',
                                    'Remitter Registration Date',
                                    'Remitter Name',
                                    'Remitter Mobile',
                                    'Remit Amount',
                                    'Bene Name',
                                    'Bene Bank Name',
                                    'Bene IFSC Code',
                                 );
                 
                 if(( $qurStr['bank_unicode'] == $bankBoiUnicode) || ( $qurStr['bank_unicode'] == $bankRatnakarUnicode) ){
                     array_push($columns,'Batch Name');
                    
                 }
                 if(( $qurStr['bank_unicode'] == $bankRatnakarUnicode) ){
                     array_push($columns, 'UTR');
                     array_push($columns, 'Shmart reference number');
                     array_push($columns, 'Status');
                 }
                 $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'remitter_transactions');exit;
                 } catch (Exception $e) {
                                            App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                            $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                            $this->_redirect($this->formatURL('/remit_reports/remittertransaction?dur='.$qurStr['dur'].'&sub=1&bank_unicode='.$qurStr['bank_unicode'])); 
                                        }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid data found') );
                         $this->_redirect($this->formatURL('/remit_reports/remittertransaction?dur='.$qurStr['dur'].'&sub=1&bank_unicode='.$qurStr['bank_unicode'])); 
                      }             
          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Data missing') );
                    $this->_redirect($this->formatURL('/remit_reports/remittertransaction?dur='.$qurStr['dur'].'&sub=1&bank_unicode='.$qurStr['bank_unicode'])); 
                 }    
       }
       
       
       
       
    /* remittancerefundAction function is responsible to generate remittance refunds report
     * on duration
     */
       
    public function remittancerefundAction(){
        $this->title = 'Product Wise Refund Report';                         
        // Get our form and validate it
        $form = new Remit_RemittanceRefundForm(array(
            'action' => $this->formatURL('/remit_reports/remittancerefund'),
            'method' => 'POST',
        )); 
        
        $request = $this->_getAllParams();
        //$sub = $this->_getParam('sub');
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['sub'] = $this->_getParam('sub');
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        $page = $this->_getParam('page');
        $toDate='';
        $fromDate='';
        
        if($qurStr['sub']!=''){
            if($form->isValid($qurStr)){ 
                if($qurStr['dur']!=''){
                    $durationArr = Util::getDurationDates($qurStr['dur']);
                    $toArr = explode(" ", $durationArr['to']);
                    $toDate = $toArr[0];
                    $fromArr = explode(" ", $durationArr['from']);
                    $fromDate = $fromArr[0];
                } else if($qurStr['to_date']!='' && $qurStr['from_date']!=''){
                    $toDate = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                    $fromDate = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                } 
                  
                if($toDate!='' && $fromDate!=''){
                    $queryParam = array(
                        'to_date'=>$toDate,
                        'from_date'=>$fromDate,
                        'bank_unicode' => $qurStr['bank_unicode']
                    );
                    $objBank = new Banks();
                    $bankInfo = $objBank->getBankbyUnicode($qurStr['bank_unicode']);
                    $this->view->title = 'Product Wise Refund Report of '.$bankInfo->name.' for '.Util::returnDateFormatted($fromDate, "Y-m-d", "d-m-Y", "-");
                    $this->view->title .= ' to '.Util::returnDateFormatted($toDate, "Y-m-d", "d-m-Y", "-"); 
                    $objReports = new Remit_Reports();
                    $result = $objReports->getRemittanceRefunds($queryParam, $page);
                    $paginator = $objReports->paginateByArray($result, $page, $paginate = NULL);
                    $this->view->paginator = $paginator;

                    $this->view->formData = $qurStr;
                    $this->view->sub = $qurStr['sub'];
                } else {
                    $this->_helper->FlashMessenger( array( 'msg-error' => sprintf('Please select duration or date range'),)); 
                }
            }
        }
        $this->view->form = $form;
    }
    
    
    
        /* exportremittancerefundAction() is responsible to generate remittance refunds report export csv data
        *  param :- duration 
        */
    
     public function exportremittancerefundAction(){
        
        // Get our form and validate it
        $form = new Remit_RemittanceRefundForm(array('action' => $this->formatURL('/remit_reports/remittancerefund'),
                                                    'method' => 'POST',
                                             )); 
   
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');       
         if($qurStr['dur']!='' || ($qurStr['to_date'] !='' && $qurStr['from_date']!='')){             
              if($form->isValid($qurStr)){ 
                 if($qurStr['dur']!=''){
                      $durationArr = Util::getDurationDates($qurStr['dur']);
                      $toArr = explode(" ", $durationArr['to']);
                      $toDate = $toArr[0];
                      $fromArr = explode(" ", $durationArr['from']);
                      $fromDate = $fromArr[0];
                  } else if($qurStr['to_date']!='' && $qurStr['from_date']!=''){
                      $toDate = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                      $fromDate = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                  }
                  
                 $queryParam = array(
                                        'to_date'=>$toDate,
                                        'from_date'=>$fromDate,
                                        'bank_unicode' => $qurStr['bank_unicode']
                                    );                
                 
                        
                 $objReports = new Remit_Reports();
                 $exportData = $objReports->exportRemittanceRefunds($queryParam);
                 $columns = array(
                                    'Date of Return',
                                    'Super Distributor Code',
                                    'Super Distributor Name',
                                    'Distributor Code',
                                    'Distributor Name',
                                    'Agent Code',
                                    'Agent Name',
                                    'Remitter Name',
                                    'Remitter Mobile',
                                    'Remitter Email',   
                                    'Beneficiary Name',
                                    'Card No.',
                                    'CRN',
                                    'Beneficiary Bank Account',   
                                    'Transaction Reference Number',
                                    'Refund Transaction Reference Number',
                                    'Refund Amount',
                                    'Reason',
                                    'Fee Reversal',
                                    'Service Tax Reversal',
                                    'UTR no.',
                                    'Status'
                                );
                 

                 $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'remittance_refunds');exit;
                 } catch (Exception $e) {
                                            App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                            $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                            $this->_redirect($this->formatURL('/remit_reports/remittancerefund?dur='.$qurStr['dur'].'&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&sub=1')); 
                                        }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid data found') );
                         $this->_redirect($this->formatURL('/remit_reports/remittancerefund?dur='.$qurStr['dur'].'&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&sub=1')); 
                      }             
          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Data missing') );
                    $this->_redirect($this->formatURL('/remit_reports/remittancerefund?dur='.$qurStr['dur'].'&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&sub=1')); 
                 }    
       }
  /**
   * neftresponseAction() will show NEFT response data
   */     
        public function neftresponseAction(){
                
       $this->title = 'Remittance Response Report';  
       // Get our form and validate it
        $form = new Remit_NeftResponseForm(); 
        $sub = $this->_getParam('sub');
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $page = $this->_getParam('page');
//        $qurStr['sub'] = $sub;
         if($sub!=''){    
                  
              if($form->isValid($qurStr)){ 
                 
                 $durationArr = Util::getDurationDates($qurStr['dur']);
                 
                 
                 if ($qurStr['dur'] != '') {
                    $qurData['duration'] = $qurStr['dur'];
                    $qurData['from'] = $durationArr['from'];
                    $qurData['to'] = $durationArr['to'];
                    $fromDate = explode(' ', Util::returnDateFormatted($durationArr['from'], "d-m-Y", "Y-m-d", "-"));
                    $toDate = explode(' ', Util::returnDateFormatted($durationArr['to'], "d-m-Y", "Y-m-d", "-"));
                    $this->view->title = $this->getReportTitle('Remittance Response Report', $qurStr['dur'],0,FALSE,$qurStr['bank_unicode']);
                
                } else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                    $fromDate = $qurData['from'];
                    $toDate = $qurData['to'];
                    $objBank = new Banks();
                    $bankInfo = $objBank->getBankbyUnicode($qurStr['bank_unicode']);
                    $this->view->title = 'Remittance Response Report of '.$bankInfo->name.' for '.Util::returnDateFormatted($fromDate, "Y-m-d", "d-m-Y", "-");
                    $this->view->title .= ' to '.Util::returnDateFormatted($toDate, "Y-m-d", "d-m-Y", "-");
                 
                
                    
                }
                 $qurData['bank_unicode'] =  $qurStr['bank_unicode']; 
                 $this->view->from = $fromDate[0];
                 $this->view->to   = $toDate[0];   
                 $page = $this->_getParam('page');
                 $objRemitReports = new Remit_Reports();
                 $remReq = $objRemitReports->getRemittanceResponse( $qurData);
                 $paginator = $objRemitReports->paginateByArray($remReq, $page, $paginate = NULL);
                 $this->view->paginator = $paginator;
               }   
               
          }
            $this->view->form = $form;
            $this->view->formData = $qurStr; 
    }
         
   
    /* exportneftresponseAction function is responsible to create the csv file on fly with NEFT response data
     * 
     */
    
     public function exportneftresponseAction(){
        $bank = App_DI_Definition_Bank::getInstance(BANK_KOTAK);
        $bankKotakUnicode = $bank->bank->unicode;
        
        $bankRatnakar = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR);
        $bankRatnakarUnicode = $bankRatnakar->bank->unicode;
        /**
        * ****************************** Start code: Getting Ratnakar Bank Unicode *********************
        */
      //  $RatnakarBank = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR);
      //  $bankRatnakarUnicode = $RatnakarBank->bank->unicode;
       /**
        * ****************************** End code: Getting Ratnakar Bank Unicode *********************
        */
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $form = new Remit_NeftResponseForm(array('action' => $this->formatURL('/remit_reports/exportneftresponse'),
                                              'method' => 'POST',
                                       )); 
        
         if($qurStr['dur']!=''|| ($qurStr['to_date'] !='' && $qurStr['from_date']!='')){    
              $durationArr = Util::getDurationDates($qurStr['dur']);
              if($form->isValid($qurStr)){ 
                 if ($qurStr['dur'] != '') {
                    $qurData['duration'] = $qurStr['dur'];
                    $qurData['from'] = $durationArr['from'];
                    $qurData['to'] = $durationArr['to'];
                   
                } else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                }
                 $qurData['bank_unicode'] =  $qurStr['bank_unicode'];
                
                 $objRemitReports = new Remit_Reports();
                 $exportData = $objRemitReports->exportneftResponse($qurData);

             
                  if( ($qurStr['bank_unicode'] == $bankKotakUnicode) ){
                   $columns = array(
                                    'Date',
                                    'Remitter Name',
                                    'Remitter Mobile Number',
                                    'Bene Account',
                                    'Bene Name',
                                    'Transaction reference Number',
                                    'Amount',
                                    'Status',
                                    'Remarks',
                                 );
                  }elseif( ($qurStr['bank_unicode'] == $bankRatnakarUnicode) ){
                     $columns = array(
                                    'Date',  
                                    'Batch Name',
                                    'Batch Date',
                                    'Batch Time',
                                    'Returned date',
                                    'Remitter Name',
                                    'Remitter Mobile Number',
                                    'Bene Account',
                                    'Bene Name',
                                    'Transaction reference Number',
                                    'Bank UTR No.',
                                    'Amount',
                                    'Status',
                                    'Remarks',
                                    'Manual Remarks',
                                    'Rejection Code',
                                    'Rejection Remarks'
                                 );  
                     
                  }
                  else{
                        $columns = array(
                                    'Date',
                                    'Batch Name',
                                    'Remitter Name',
                                    'Remitter Mobile Number',
                                    'Bene Account',
                                    'Bene Name',
                                    'Transaction reference Number',
                                    'Amount',
                                    'Status',
                                    'Remarks',
                                 );  
                  }
                 $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'remittance_response');exit;
                 }catch (Exception $e) {
                                         App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                         $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                         $this->_redirect($this->formatURL('/remit_reports/neftresponse?dur='.$qurStr['dur'].'&sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&bank_unicode='.$qurStr['bank_unicode'])); 
                                       }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid duration!') );
                         $this->_redirect($this->formatURL('/remit_reports/neftresponse?dur='.$qurStr['dur'].'&sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&bank_unicode='.$qurStr['bank_unicode'])); 
                      }             
          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Invalid duration!') );
                    $this->_redirect($this->formatURL('/remit_reports/neftresponse?dur='.$qurStr['dur'].'&sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&bank_unicode='.$qurStr['bank_unicode'])); 
                 }    
       }
   
       
       /* remittancerefundyettoclaimAction function is responsible to generate remittance refund yet to claim 
        */
       
        public function remittancerefundyettoclaimAction(){
        $this->title = 'Remittance Refund Yet to claim Report';                         
         // Get our form and validate it
         $form = new Remit_RemittanceRefundYetToClaimForm(array('action' => $this->formatURL('/remit_reports/remittancerefundyettoclaim'),
                                                            'method' => 'POST',
                                                      )); 
        
        $request = $this->_getAllParams();
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['sub'] = $this->_getParam('sub');
        $qurStr['on_date'] = $this->_getParam('on_date');
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        $page = $this->_getParam('page');
//        
//        if($qurStr['sub']==''){
//           $qurStr['dur']='today';
//           $qurStr['sub']='btn_submit';
//        }
//        
        if($qurStr['sub']!=''){
              if($form->isValid($qurStr)){ 
        
        $objRemit = new Remit_Boi_Remitter();
        try{
            $title='Remittance Refund Yet To Claim Report';
            if ($qurStr['dur'] != '') {
                $queryParam = array(
                                'dur'=>$qurStr['dur'],
                                'bank_unicode' =>   $qurStr['bank_unicode']
                              );
                $this->view->pageTitle = $this->getReportTitle($title, $qurStr['dur'],0,FALSE,$qurStr['bank_unicode']);
            } elseif ($qurStr['on_date'] != '') {
                $qurData['from'] = Util::returnDateFormatted($qurStr['on_date'], "d-m-Y", "Y-m-d", "-");
                $qurData['to'] = Util::returnDateFormatted($qurStr['on_date'], "d-m-Y", "Y-m-d", "-");
                $queryParam = array(
                                'from'=>$qurData['from'],
                                'to'=>$qurData['to'],
                                'on_date'=>$qurStr['on_date'],
                                'bank_unicode' =>   $qurStr['bank_unicode']
                              );
                $this->view->pageTitle = $this->getReportTitle($title, $qurStr['on_date'],0,TRUE,$qurStr['bank_unicode']);
            }
            $objReports = new Remit_Reports();
            $rptData = $objReports->getRemittanceRefundYetToClaim($queryParam);

            $paginator = $objReports->paginateByArray($rptData, $page, $paginate = NULL);
            $this->view->paginator = $paginator;
        }
         catch (Exception $e ) {
                   App_Logger::log($e->getMessage(), Zend_Log::ERR);
                   $errMsg = $e->getMessage();
                   
                        $this->_helper->FlashMessenger(
                            array(
                                    'msg-error' => $errMsg,
                                 )
                            );
                }  
              } 
        }
         // }
        
            $this->view->form = $form;
            $this->view->formData = $qurStr;
            $this->view->sub = $qurStr['sub'];
            
    }
    
    
    
    /* exportremittancerefundyettoclaimAction() is responsible to generate remittance refund yet to claim report export csv data
     *  param :- duration
     */
    
     public function exportremittancerefundyettoclaimAction(){
        
        // Get our form and validate it
        $form = new Remit_RemittanceRefundYetToClaimForm(array('action' => $this->formatURL('/remit_reports/remittancerefundyettoclaim'),
                                                    'method' => 'POST',
                                             )); 
   
       // $qurStr['phone'] = $this->_getParam('phone');
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['on_date'] = $this->_getParam('on_date');
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');      
         if($qurStr['dur']!='' || $qurStr['on_date']!=''){             
              if($form->isValid($qurStr)){ 
                  if($qurStr['dur']!=''){ 
                      $qurData['dur'] = $qurStr['dur'];
                  } else {
                      $qurData['to'] = Util::returnDateFormatted($qurStr['on_date'], "d-m-Y", "Y-m-d", "-");
                      $qurData['from'] = Util::returnDateFormatted($qurStr['on_date'], "d-m-Y", "Y-m-d", "-");
                  }
                 
                 $qurData['bank_unicode'] =  $qurStr['bank_unicode'];
                 $objReports = new Remit_Reports();
                 $exportData = $objReports->exportRemittanceRefundYetToClaim($qurData);
                
                 $columns = array(
                                    'Date',
                                    'Remitter Name',
                                    'Remitter Mobile',
                                    'Remitter email',
                                    'Beneficiary Name', 
                                    'Beneficiary Bank Account',
                                    'Transaction Reference Number',
                                    'Refund Amount',
                                    'Reason / Neft Remarks',
                                    'Fee Reversal',
                                    'Service Tax Reversal',
                                    'Super Distributor Code',
                                    'Super Distributor Name',
                                    'Distributor Code',
                                    'Distributor Name',
                                    'Agent Code',
                                    'Agent Name',
                                    'Agent Bank',
                                    'Agent Mobile Number',
                                    'UTR',
                                    'Shmart Ref Number',
                                    'Date UTR',
                                    'Status UTR',
                                    'Date Status Response',
                                    'Status Response',
                                    'Transaction Status',
                                    'Batch Name',
                                    'Batch Date',
                                    'Neft Processed',
                                    'Neft Processed Date',
                                    'Status SMS',
                                    'Date Updated'
                                 );
                 

                 $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'remittance_refund_yettoclaim');exit;
                 } catch (Exception $e) {
                                            App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                            $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                            $this->_redirect($this->formatURL('/remit_reports/remittancerefundyettoclaim?dur='.$qurStr['dur'].'&sub=1&bank_unicode='.$qurStr['bank_unicode'])); 
                                        }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid data found') );
                         $this->_redirect($this->formatURL('/remit_reports/remittancerefundyettoclaim?dur='.$qurStr['dur'].'&sub=1&bank_unicode='.$qurStr['bank_unicode'])); 
                      }             
          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Data missing') );
                    $this->_redirect($this->formatURL('/remit_reports/remittancerefundyettoclaim?dur='.$qurStr['dur'].'&sub=1&bank_unicode='.$qurStr['bank_unicode'])); 
                 }    
       }
       
       
       
       
        /* remittanceexceptionAction function is responsible to generate remittance exception report
        *  on duration or date to and from
        */
       
        public function remittanceexceptionAction(){

        ini_set('max_execution_time',0);        

         $this->title = 'Remittance Exception Report';                         
         // Get our form and validate it
         $form = new Remit_RemittanceExceptionForm(array('action' => $this->formatURL('/remit_reports/remittanceexception'),
                                                    'method' => 'POST',
                                             )); 
        
        $request = $this->_getAllParams();
        //$sub = $this->_getParam('sub');
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['sub'] = $this->_getParam('sub');
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        $page = $this->_getParam('page');
	$qurStr['noofrecords'] = $this->_getParam('noofrecords');
        $toDate='';
        $fromDate='';
        
        if($qurStr['sub']!=''){
              if($form->isValid($qurStr)){
                  
                  if($qurStr['dur']!=''){
                      $durationArr = Util::getDurationDates($qurStr['dur']);
                      $toArr = explode(" ", $durationArr['to']);
                      $toDate = $toArr[0];
                      $fromArr = explode(" ", $durationArr['from']);
                      $fromDate = $fromArr[0];
                  } else if($qurStr['to_date']!='' && $qurStr['from_date']!=''){
                      $toDate = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                      $fromDate = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                  } 
                  
                 if($toDate!='' && $fromDate!=''){
                   
                
                 $queryParam = array(
                                        'to_date'=>$toDate,
                                        'from_date'=>$fromDate,
                                        'bank_unicode' =>  $qurStr['bank_unicode'],
					'noofrecords' => $qurStr['noofrecords']
                                    );
                 $objBank = new Banks();
                 $bankInfo = $objBank->getBankbyUnicode($qurStr['bank_unicode']);
                 $this->view->title = 'Remittance Exception Report of '.$bankInfo->name.' for '.Util::returnDateFormatted($fromDate, "Y-m-d", "d-m-Y", "-");
                 $this->view->title .= ' to '.Util::returnDateFormatted($toDate, "Y-m-d", "d-m-Y", "-");
                 
                 $objReports = new Remit_Reports();
                 $result = $objReports->getRemittanceException($queryParam, $page);
                 
                 $paginator = $objReports->paginateByArray($result, $page, $paginate = NULL);
                 $this->view->paginator = $paginator;
                 
                 $this->view->formData = $qurStr;
                 $this->view->sub = $qurStr['sub'];
                 } else {
                      $this->_helper->FlashMessenger( array( 'msg-error' => sprintf('Please select duration or date range'),)); 
                 }
                
              } 
        }
         // }
            $this->view->form = $form;
            
    }
    
    
    
        /* exportremittancerefundAction() is responsible to generate remittance refunds report export csv data
        *  param :- duration or date to and from
        */
    
     public function exportremittanceexceptionAction(){
        
        // Get our form and validate it
        $form = new Remit_RemittanceExceptionForm(array('action' => $this->formatURL('/remit_reports/remittanceexception'),
                                                        'method' => 'POST',
                                                 )); 
   
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');      
         if($qurStr['dur']!='' || ($qurStr['to_date'] !='' && $qurStr['from_date']!='')){             
              if($form->isValid($qurStr)){ 
                 if($qurStr['dur']!=''){
                      $durationArr = Util::getDurationDates($qurStr['dur']);
                      $toArr = explode(" ", $durationArr['to']);
                      $toDate = $toArr[0];
                      $fromArr = explode(" ", $durationArr['from']);
                      $fromDate = $fromArr[0];
                  } else if($qurStr['to_date']!='' && $qurStr['from_date']!=''){
                      $toDate = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                      $fromDate = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                  }
                  
                 $queryParam = array(
                                        'to_date'=>$toDate,
                                        'from_date'=>$fromDate,
                                        'bank_unicode' =>  $qurStr['bank_unicode']
                                    );                
                 
                        
                 $objReports = new Remit_Reports();
                 $exportData = $objReports->exportRemittanceException($queryParam);
                 $columns = array(
                                    'Date',
                                    'Remitter Name',
                                    'Remitter Mobile',
                                    'Remitter Email',   
                                    'Beneficiary Name',   
                                    'Amount',   
                                    'Transaction Count',   
                                    'Beneficiary Bank Account',
                                    'Super Distributor Code',
                                    'Super Distributor Name',
                                    'Distributor Code',
                                    'Distributor Name',
                                    'Agent Name',
                                    'Agent Code',
                                    'Bene Bank',
                                    'Bene IFSC'
                                 );
                 

                 $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'remittance_exception');exit;
                 } catch (Exception $e) {
                                            App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                            $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                            $this->_redirect($this->formatURL('/remit_reports/remittanceexception?dur='.$qurStr['dur'].'&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&sub=1&bank_unicode='.$qurStr['bank_unicode'])); 
                                        }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid data found') );
                         $this->_redirect($this->formatURL('/remit_reports/remittanceexception?dur='.$qurStr['dur'].'&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&sub=1&bank_unicode='.$qurStr['bank_unicode'])); 
                      }             
          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Data missing') );
                    $this->_redirect($this->formatURL('/remit_reports/remittanceexception?dur='.$qurStr['dur'].'&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&sub=1&bank_unicode='.$qurStr['bank_unicode'])); 
                 }    
       }
       
       
    public function remitkotakfailurereconAction()
    {
        $this->title = 'Remit Kotak Failure Recon Report';
        // Get our form and validate it
        $form = new Remit_Kotak_FailureReconForm(array('action' => $this->formatURL('/remit_reports/remitkotakfailurerecon'),
            'method' => 'POST',
        ));

        $page = $this->_getParam('page');
        $request = $this->_getAllParams();
        $sub = $this->_getParam('sub');
        $qurStr['sub'] = $sub;
        $qurStr['to_date']    = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        
        $fileObject = new Files();
        $type = $this->_getParam('type');
        $report_name = $this->_getParam('report_name');
        $file_name = $this->_getParam('file_name');
        
        if($type != '')
        {
            if($type == 'downloadreports' && $file_name != '') {
                $fileObject->setFilepath(UPLOAD_PATH_REMIT_KOTAK_FAILURE_RECON_REPORTS);

                $fileObject->setFilename($file_name);
                $fileObject->downloadCSVFile();
                $this->_helper->layout->disableLayout();
                $this->_helper->viewRenderer->setNoRender(TRUE);
            } 
            else{
                $this->_helper->FlashMessenger( array('msg-error' => 'File not found.') );                 
            }
        }
        
        if ($sub != '') {
            if ($form->isValid($qurStr)) {
 
                if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {

                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", "to");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", "from");
                    $titleDate['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                    $titleDate['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                    
                    $pageTitle = 'Remit Kotak Failure Recon Report from ' . Util::returnDateFormatted($titleDate['from'], "Y-m-d", "d-m-Y", "-");
                    $pageTitle .= ' to ' . Util::returnDateFormatted($titleDate['to'], "Y-m-d", "d-m-Y", "-");
                } else {
                    $this->_helper->FlashMessenger(array('msg-error' => 'Invalid date range'));
                }

                $reportsModel = new Remit_Reports();
                $dataArr = $reportsModel->getRemitKotakFailureRecon($qurData);

                $paginator = $reportsModel->paginateByArray($dataArr, $page, $paginate = NULL);
                $this->view->paginator = $paginator;
                $this->view->pageTitle = $pageTitle;
                $this->view->btnSubmit = $qurStr['sub'];
            }
        }
        $this->view->form = $form;
        $this->view->formData = $qurStr;
    }
    
    
    /* beneficiaryexceptionAction function is responsible to generate Beneficiary Exception report More than 1 Lac
    *  on date to and from
    */

    public function beneficiaryexceptionAction(){
        $this->title = 'Beneficiary Exception Report More Than 1 Lac';                         
        // Get our form and validate it
        $form = new Remit_BeneficiaryExceptionForm(array('action' => $this->formatURL('/remit_reports/beneficiaryexception'),
                                                'method' => 'POST',
                                         )); 

        $request = $this->_getAllParams();
        //$sub = $this->_getParam('sub');
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        //$qurStr['dur'] = $this->_getParam('dur');
        $qurStr['sub'] = $this->_getParam('sub');
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        $page = $this->_getParam('page');
        $toDate='';
        $fromDate='';

        if($qurStr['sub']!=''){
              if($form->isValid($qurStr)){

//                  if($qurStr['dur']!=''){
//                      $durationArr = Util::getDurationDates($qurStr['dur']);
//                      $toArr = explode(" ", $durationArr['to']);
//                      $toDate = $toArr[0];
//                      $fromArr = explode(" ", $durationArr['from']);
//                      $fromDate = $fromArr[0];
//                  } else 
                 
                if($qurStr['to_date']!='' && $qurStr['from_date']!=''){
                    $toDate = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                    $fromDate = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                } 

                if($toDate!='' && $fromDate!=''){

                    $queryParam = array(
                                           'to_date'=>$toDate,
                                           'from_date'=>$fromDate,
                                           'bank_unicode' =>  $qurStr['bank_unicode']
                                       );
                    $objBank = new Banks();
                    $bankInfo = $objBank->getBankbyUnicode($qurStr['bank_unicode']);
                    $this->view->title = 'Beneficiary Exception Report of '.$bankInfo->name.' for '.Util::returnDateFormatted($fromDate, "Y-m-d", "d-m-Y", "-");
                    $this->view->title .= ' to '.Util::returnDateFormatted($toDate, "Y-m-d", "d-m-Y", "-");

                    $objReports = new Remit_Reports();
                    $result = $objReports->getBeneficiaryException($queryParam, $page);

                    $paginator = $objReports->paginateByArray($result, $page, $paginate = NULL);
                    $this->view->paginator = $paginator;

                    $this->view->formData = $qurStr;
                    $this->view->sub = $qurStr['sub'];
                 } else {
                      $this->_helper->FlashMessenger( array( 'msg-error' => sprintf('Please select date range'),)); 
                 }

              } 
            }
            // }
            $this->view->form = $form;
    }
    
    
    
    /* exportbeneficiaryexceptionAction() is responsible to generate Beneficiary Exception report More than 1 Lac export csv data
    *  param :- date to and from
    */
    
    public function exportbeneficiaryexceptionAction(){
        
        // Get our form and validate it
        $form = new Remit_BeneficiaryExceptionForm(array('action' => $this->formatURL('/remit_reports/beneficiaryexception'),
                                                        'method' => 'POST',
                                                 )); 
   
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
       // $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');      
         if($qurStr['to_date'] !='' && $qurStr['from_date']!=''){             
              if($form->isValid($qurStr)){ 
                  /*
                 if($qurStr['dur']!=''){
                      $durationArr = Util::getDurationDates($qurStr['dur']);
                      $toArr = explode(" ", $durationArr['to']);
                      $toDate = $toArr[0];
                      $fromArr = explode(" ", $durationArr['from']);
                      $fromDate = $fromArr[0];
                  } else
                   */   
                      
                  if($qurStr['to_date']!='' && $qurStr['from_date']!=''){
                      $toDate = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                      $fromDate = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                  }
                  
                 $queryParam = array(
                                        'to_date'=>$toDate,
                                        'from_date'=>$fromDate,
                                        'bank_unicode' =>  $qurStr['bank_unicode']
                                    );                
                 
                        
                 $objReports = new Remit_Reports();
                 $exportData = $objReports->exportBeneficiaryException($queryParam);
                 $columns = array(
                                    'Remitter Name',
                                    'Remitter Mobile',
                                    'Remitter Address',   
                                    'Beneficiary Name',   
                                    'Amount',   
                                    'Transaction Count',   
                                    'Distributor Code',
                                    'Distributor Name',
                                    'Agent Name',
                                    'Agent Code',
                                    'Bank Name',
                                    'Beneficiary Bank Account',
                                    'Bene IFSC',
                                    'Beneficiary Bank Address 1',
                                    'Beneficiary Bank Address 2',
                                    'Product',
                                    'Product Code',
                                 );
                 

                 $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'beneficiary_exception_1_lac');exit;
                 } catch (Exception $e) {
                                            App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                            $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                            $this->_redirect($this->formatURL('/remit_reports/beneficiaryexception?to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&sub=1&bank_unicode='.$qurStr['bank_unicode'])); 
                                        }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid data found') );
                         $this->_redirect($this->formatURL('/remit_reports/beneficiaryexception?to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&sub=1&bank_unicode='.$qurStr['bank_unicode'])); 
                      }             
          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Data missing') );
                    $this->_redirect($this->formatURL('/remit_reports/beneficiaryexception?to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&sub=1&bank_unicode='.$qurStr['bank_unicode'])); 
                 }    
       }
    

     public function searchremitAction(){
        $this->title = 'Advanced Remittance Report';  
        // Get our form and validate it
        $form = new Remit_SearchRemitForm(array('action' => $this->formatURL('/remit_reports/searchremit'),
                                               'method' => 'POST',
                                       ));  
        
        $sub = $this->_getParam('sub');
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
	$qurStr['product_id'] = $this->_getParam('product_id');
        $qurStr['duration'] = $this->_getParam('duration');
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $qurStr['mobile_no']  = $this->_getParam('mobile_no');
        $qurStr['txn_no']  = $this->_getParam('txn_no');
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
                 $this->view->title = $this->getReportTitle('Remittance Transaction Report', $qurStr['duration'],0,FALSE,$qurStr['bank_unicode']);
                  }
                else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                        $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-","-","to");
                        $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-","-","from");
                        $qurStr['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                        $qurStr['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                        $objBank = new Banks();
                        $bankInfo = $objBank->getBankbyUnicode($qurStr['bank_unicode']);
                        $this->view->title = 'Remittance Transaction Report of '.$bankInfo->name.' for '.Util::returnDateFormatted($qurStr['from'], "Y-m-d", "d-m-Y", "-");
                        $this->view->title .= ' to '.Util::returnDateFormatted($qurStr['to'], "Y-m-d", "d-m-Y", "-");
                        $this->view->from = $qurStr['from'];
                        $this->view->to   = $qurStr['to'];
                        $this->view->on_page = $qurStr['on_page'];
                    }
                
                    $qurData['bank_unicode'] =  $qurStr['bank_unicode'];
		    $qurData['product_id'] = $qurStr['product_id'];
                    $qurData['duration'] =  $qurStr['duration'];  
                    $qurData['mobile_no'] = $qurStr['mobile_no'];
                    $qurData['txn_no'] = $qurStr['txn_no'];
                   if($qurData['txn_no'] == '' && $qurData['mobile_no']== '' && $qurStr['from_date'] == '' && $qurStr['duration'] == ''){
                    $this->_helper->FlashMessenger( array( 'msg-error' => sprintf('Please choose at least one option for search'),)); 
                   }
                    
                        
                        $page = $this->_getParam('page');
                        $objReports = new Remit_Reports();
                        $agentTxns = $objReports->getRemittanceDetails($qurData);
                        $paginator = $objReports->paginateByArray($agentTxns, $page, $paginate = NULL);
                        $this->view->paginator=$paginator;
                  
                }
            }
	    $form->getElement('product')->setValue($qurStr['product_id']);
            $this->view->form = $form;
            $this->view->sub = $sub;
            $this->view->formData = $qurStr;
           
    }
    
      public function exportsearchremitAction(){
        
        // Get our form and validate it
         $form = new Remit_SearchRemitForm(array('action' => $this->formatURL('/remit_reports/exportsearchremit'),
                                               'method' => 'POST',
                                       ));  
        
   
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        $qurStr['duration'] = $this->_getParam('duration');
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $qurStr['mobile_no']  = $this->_getParam('mobile_no');
        $qurStr['txn_no']  = $this->_getParam('txn_no');  
                     
              if($form->isValid($qurStr)){ 
                  if($qurStr['duration']!=''){ 
                 $durationArr = Util::getDurationDates($qurStr['duration']);
                 $fromDate = $durationArr['from'];
                 $toDate = $durationArr['to'];  
                    }
                 else if($qurStr['to_date']!='' && $qurStr['from_date']!=''){
                      
                      $fromDate = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-","-","from");
                      $toDate = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-","-","to");
                      
                  }
                  
                 $queryParam = array(
                                        
                                        'from'=>$fromDate,
                                        'to'=>$toDate,
                                        'bank_unicode' =>  $qurStr['bank_unicode'],
                                        'mobile_no' =>  $qurStr['mobile_no'],
                                        'txn_no' =>  $qurStr['txn_no']
                                    );                
                 
                        
                 $objReports = new Remit_Reports();
                 $exportData = $objReports->exportRemittanceDetails($queryParam);
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
		    'FTL Transaction ID',
                    'Refund/Reversed Trx Ref Number',
                    'Remitter Name',
                    'Remitter Mobile Number',
                    'Remitter Email',
                    'Remitter Registration Date',
                    'Bene Name',
                    'Bene Bank Name',
                    'Bene IFSC Code',
                    'Current Transaction Status',
                    'Reason',
                    'Reason Code'
                );


                $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'search_remittance');exit;
                 } catch (Exception $e) {
                                            App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                            $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                            $this->_redirect($this->formatURL('/remit_reports/searchremit?to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&sub=1&bank_unicode='.$qurStr['bank_unicode'].'&mobile_no='.$qurStr['mobile_no'].'&txn_no='.$qurStr['txn_no'].'&duration='.$qurStr['duration'])); 
                                        }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid data found') );
                         $this->_redirect($this->formatURL('/remit_reports/searchremit?to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&sub=1&bank_unicode='.$qurStr['bank_unicode'].'&mobile_no='.$qurStr['mobile_no'].'&txn_no='.$qurStr['txn_no'].'&duration='.$qurStr['duration'])); 
                      }             
         
       }

    /*
     * remitreconAction() gets remit txn recon data for Banks
     */
    public function remitreconAction()
    {
        $this->title = 'Remittance Transaction Recon Report';
        // Get our form and validate it
        $form = new Remit_ReconForm(array('action' => $this->formatURL('/remit_reports/remitrecon'),
            'method' => 'POST',
        ));

        $page = $this->_getParam('page');
        $sub = $this->_getParam('sub');
        $qurStr['sub'] = $sub;
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        $qurStr['to_date'] = $this->_getParam('to_date');
        $qurStr['from_date'] = $this->_getParam('from_date');
        
        if ($sub != '') {
            if ($form->isValid($qurStr)) {
 
                if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {

                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", "to");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", "from");
                    $titleDate['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                    $titleDate['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                    
                    $objBank = new Banks();
                    $bankInfo = $objBank->getBankbyUnicode($qurStr['bank_unicode']);
                    $pageTitle = 'Remittance Transaction Recon Report of '.$bankInfo->name.' from ' . Util::returnDateFormatted($titleDate['from'], "Y-m-d", "d-m-Y", "-");
                    $pageTitle .= ' to ' . Util::returnDateFormatted($titleDate['to'], "Y-m-d", "d-m-Y", "-");
                } else {
                    $this->_helper->FlashMessenger(array('msg-error' => 'Invalid date range'));
                }

                $qurData['bank_unicode'] = $qurStr['bank_unicode'];
                $reportsModel = new Remit_Reports();
                $dataArr = $reportsModel->getRemitRecon($qurData);

                $paginator = $reportsModel->paginateByArray($dataArr, $page, $paginate = NULL);
                $this->view->paginator = $paginator;
                $this->view->pageTitle = $pageTitle;
                $this->view->btnSubmit = $qurStr['sub'];
            }
        }
        $this->view->form = $form;
        $this->view->formData = $qurStr;
    }

  }
