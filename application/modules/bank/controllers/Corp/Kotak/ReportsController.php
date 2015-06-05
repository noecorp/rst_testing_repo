<?php
/**
 * Allows user to see reports
 *
 * @category backoffice
 * @package backoffice_controllers
 * @copyright company
 */

class Corp_Kotak_ReportsController extends App_Bank_Controller
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
     
       /* 
        * balancesheetAction generates agent balance sheet for specific period
        */
       public function applicationsAction(){
	       	$this->title = 'Applications';                       
	       	$user = Zend_Auth::getInstance()->getIdentity();
	       	$form = new Corp_Kotak_ApplicationsForm(array('action' => $this->formatURL('/corp_kotak_reports/applications'),
	                                                    'method' => 'POST',
	                                             )); 
	  			
	  	$request = $this->_getAllParams();
	        $qurStr['dur'] = $this->_getParam('dur');
	        $qurStr['sub'] = $this->_getParam('sub');
	        $qurStr['to_date']  = $this->_getParam('to_date');
	        $qurStr['from_date']  = $this->_getParam('from_date');
	        $qurStr['bank_status']  = $this->_getParam('bank_status');
	        $qurStr['product_id']  = $this->_getParam('product_id');
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
	                 $this->view->title = $this->getReportTitle('Applications ', $qurStr['dur']);
	                 $durationDates = Util::getDurationDates($qurStr['dur']);
	                } else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
	                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-",'-','to');
	                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-",'-','from');
	                    $durationDates = array('to' => $qurData['to'], 'from' => $qurData['from']);
	                    $from = Util::returnDateFormatted($qurData['from'], "Y-m-d", "d-m-Y", "-");
                            $to = Util::returnDateFormatted($qurData['to'], "Y-m-d", "d-m-Y", "-");
                            $fromArr = explode(" ",$from);
                            $toArr = explode(" ",$to);
                            $this->view->title = 'Applications '.$fromArr[0];
	                    $this->view->title .= ' to '.$toArr[0];
	                    $this->view->from = $qurData['from'];
	                    $this->view->to   = $qurData['to'];
	                }
	                 $durationDates['product_id'] = $qurStr['product_id'];
	                 $objReports = new Reports();
	                 $rptData = $objReports->getApplications($durationDates,$qurStr['bank_status']);
                         $paginator = $objReports->paginateByArray($rptData, $page, $paginate = NULL);
	                 
	                 $this->view->paginator = $paginator;
	                 $this->view->formData = $qurStr;
	              } 
	        }
	         
	        $this->view->form = $form;
       	
       }
       
       /* exportbalancesheetAction function creates csv file for agent balance sheet report data
     */
    
     public function exportapplicationsAction(){
     	$user = Zend_Auth::getInstance()->getIdentity();
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['bank_status']  = $this->_getParam('bank_status');
        $qurStr['product_id']  = $this->_getParam('product_id');
        $form = new Corp_Kotak_ApplicationsForm(array('action' => $this->formatURL('/corp_kotak_reports/applications'),
                                              'method' => 'POST',
                                       )); 
             
          if($form->isValid($qurStr)){ 
           if ($qurStr['dur'] != '') {
            $durationDates = Util::getDurationDates($qurStr['dur']);
            } else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                            $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-",'-','to');
	                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-",'-','from');
	                    $durationDates = array('to' => $qurData['to'], 'from' => $qurData['from']);
	                    
                
            }
             $durationDates['product_id'] = $qurStr['product_id'];
             $objReports = new Reports();
             $exportData = $objReports->exportgetApplications($durationDates,$qurStr['bank_status']);
//             echo '<pre>';print_r($exportData);exit('hghghgh');       
             $columns = array(
                'First Name',
                'Last Name',
                'Member Id',
                'Card Number',
                'Card pack Id',
                'Date of Birth',
                'Mobile',
                'Email',
                'Bank Status',
                'Submission Date -Maker',
                'Submission Date -Checker',
                'Authorized Date',
                'Documents Submitted'
            );

            $objCSV = new CSV();
             try{
                 
                    $resp = $objCSV->export($exportData, $columns, 'applications');exit;
             }catch (Exception $e) {
                                     App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                     $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                     $this->_redirect($this->formatURL('/corp_kotak_reports/applications?sub=1&dur='.$qurStr['dur'].'&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&bank_status='.$qurStr['bank_status'].'&product_id='.$qurStr['product_id'])); 
                                   }
             
           } else {
                     $this->_helper->FlashMessenger( array('msg-error' => 'Invalid duration!') );
                     $this->_redirect($this->formatURL('/corp_kotak_reports/applications?sub=1&dur='.$qurStr['dur'].'&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&bank_status='.$qurStr['bank_status'].'&product_id='.$qurStr['product_id'])); 
           }             
    
       }
       
        /* remitterregnAction: load / reload report for all agents of banks
     * takes duration as argument, currently yesterday / today / WTD / MTD
     */
    public function remitterregnAction(){
        
       $this->title = 'Remitter Registration Report';  
       // Get our form and validate it
        $form = new Corp_Kotak_RemitterRegnForm(array('action' => $this->formatURL('/corp_kotak_reports/remitterregn'),
                                              'method' => 'POST',
                                       )); 
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        $qurStr['duration'] = $this->_getParam('duration');
        $qurStr['btn_submit'] = $this->_getParam('sub');
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $qurStr['submit'] = $this->_getParam('submit');
        $page = $this->_getParam('page');
        
         if($qurStr['btn_submit']){          
             
              if($form->isValid($qurStr)){ 
                  if ($qurStr['duration'] != ''){ 
                 $durationArr = Util::getDurationDates($qurStr['duration']);
                 $qurData['from'] = $durationArr['from'];
                 $qurData['to'] = $durationArr['to'];                 
                                  
                 $fromDate = explode(' ', Util::returnDateFormatted($durationArr['from'], "d-m-Y", "Y-m-d", "-"));
                 $toDate = explode(' ', Util::returnDateFormatted($durationArr['to'], "d-m-Y", "Y-m-d", "-"));
                 $this->view->from = $fromDate[0];
                 $this->view->to   = $toDate[0];                 
                 $this->view->title = $this->getReportTitle('Remitter Registration Report', $qurStr['duration'],0,FALSE,$qurStr['bank_unicode']);
                  }
                else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $objBank = new Banks();
                    $bankInfo = $objBank->getBankbyUnicode($qurStr['bank_unicode']);
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                    $this->view->title = 'Remitter Registration Report of '. $bankInfo->name.' for '.Util::returnDateFormatted($qurData['from'], "Y-m-d", "d-m-Y", "-");
                    $this->view->title .= ' to '.Util::returnDateFormatted($qurData['to'], "Y-m-d", "d-m-Y", "-");
                    $this->view->from = $qurData['from'];
                    $this->view->to   = $qurData['to'];
                }

                 
                 $qurData['bank_unicode'] =  $qurStr['bank_unicode'];
                 $remitReportModel = new Remit_Reports();
                 //$dataArr = $remitterRegnModel->getRemitterRegistrations($qurData, $this->_getPage());
                 $dataArr = Util::toArray($remitReportModel->getRemitterRegistrations($qurData, $this->_getPage()));
                 $dataArr = $remitReportModel->paginateByArray($dataArr, $page, $paginate = NULL);
                 $this->view->btnSubmit = $qurStr['btn_submit'];
                 $this->view->paginator = $dataArr;
               }   
               
          }
            $this->view->form = $form;
            $this->view->formData = $qurStr; 
    }
    
   
    /* exportremitterregnAction function is responsible to create the csv file on fly with remitter regn data
     * and let user download that file.
     */
    
     public function exportremitterregnAction(){
        $qurStr['duration'] = $this->_getParam('duration');
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        $form = new Corp_Kotak_RemitterRegnForm(array('action' => $this->formatURL('/corp_kotak_reports/remitterregn'),
                                              'method' => 'POST',
                                       )); 
        
         if($qurStr['duration']!='' || $qurStr['to_date'] !='' && $qurStr['from_date']!=''){    
             
              if($form->isValid($qurStr)){ 
                    if($qurStr['duration']!=''){ 
                 $durationArr = Util::getDurationDates($qurStr['duration']);
                 $qurData['from'] = $durationArr['from'];
                 $qurData['to'] = $durationArr['to'];  
                    }
                 else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                   
                }

                 $qurData['bank_unicode'] =  $qurStr['bank_unicode'];  
                 
                 $remitReportModel = new Remit_Reports();
                 $exportData = Util::toArray($remitReportModel->getRemitterRegistrations($qurData));
                 $j = 0;
                 foreach($exportData as $data){
          	 $formattedArr[$j] = array(
                     'date_created' =>$data['date_created'] ,'name' => $data['name'],'mobile' => $data['mobile'],
                     'agent_code' => $data['agent_code'], 'agent_name' =>$data['agent_name'],'estab_city' => $data['estab_city'],'estab_state' => $data['estab_state'],'ecs_product_code' => $data['ecs_product_code'],
                     'bank_name' => $data['bank_name'],'address' =>$data['address']       
                 );
                 $j++;
                 }
                 $columns = array(
                                    'Date',
                                    'Remitter Name',
//                                    'Cust ID',
                                    'Mobile Number',
                                    'Agent Code',
                                    'Agent Name',
                                    'Agent City',
                                    'Agent State',
                                    'Product Code',
                                    'Bank Name',
                                    'Remitter  Address',  
                                 );
                                  
                 $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($formattedArr, $columns, 'remitter_registration');exit;
                 }catch (Exception $e) {
                                         App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                         $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                         $this->_redirect($this->formatURL('/corp_kotak_reports/remitterregn?duration='.$qurStr['duration'].'&bank_unicode='.$qurStr['bank_unicode'].'&sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'])); 
                                       }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid Params!') );
                         $this->_redirect($this->formatURL('/corp_kotak_reports/remitterregn?duration='.$qurStr['duration'].'&bank_unicode='.$qurStr['bank_unicode'].'&sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'])); 
                      }             
          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Invalid Params!') );
                    $this->_redirect($this->formatURL('/corp_kotak_reports/remitterregn?duration='.$qurStr['duration'].'&bank_unicode='.$qurStr['bank_unicode'].'&sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'])); 
                 }    
       }
      
	/* beneregistrationAction function generates data for bank beneficiary registration for a specific period */
    public function beneregistrationAction() {
        $user = Zend_Auth::getInstance()->getIdentity();
        $this->title = 'Beneficiary Registration Report';
        // Get our form and validate it
        $form = new Corp_Kotak_BeneficiaryRegistrationForm(array('action' => $this->formatURL('/corp_kotak_reports/beneregistration'),
            'method' => 'POST',
        ));

        $page = $this->_getParam('page');
        $request = $this->_getAllParams();
        $sub = $this->_getParam('sub');
        $qurStr['bank_unicode'] = $user->unicode;
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
    
    /* exportbeneregistrationAction function creates csv file for bank beneficiary registration report data */
    public function exportbeneregistrationAction()
    {
        $user = Zend_Auth::getInstance()->getIdentity();
        $qurStr['to_date'] = $this->_getParam('to_date');
        $qurStr['from_date'] = $this->_getParam('from_date');
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['bank_unicode'] = $user->unicode;
        $form = new Corp_Kotak_BeneficiaryRegistrationForm(array('action' => $this->formatURL('/corp_kotak_reports/exportbeneregistration'),
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
                $this->_redirect($this->formatURL('/corp_kotak_reports/exportbeneregistration?to_date=' . $qurStr['to_date'] . '&from_date=' . $qurStr['from_date']));
            }
        } else {
            $this->_helper->FlashMessenger(array('msg-error' => 'Invalid data'));
            $this->_redirect($this->formatURL('/corp_kotak_reports/exportbeneregistration?to_date=' . $qurStr['to_date'] . '&from_date=' . $qurStr['from_date']));
        }
    }
}