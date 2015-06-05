<?php
/**
 * Ops can view Reports 
 *
 * @package frontend_controllers
 * @copyright company
 */

class Corp_Ratnakar_ReportsController extends App_Corporate_Controller
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
        $user = Zend_Auth::getInstance()->getIdentity();
        if(!isset($user->id)) {
           $this->_redirect($this->formatURL('/profile/login'));
           exit;
        }
        
    }
    
    /**
     * Controller's entry point
     *
     * @access public
     * @return void
     */
    public function indexAction(){
       
    }
    
//Paytronics load report
       
     public function loadreportAction(){
         $this->title = 'Load Report'; 
         $user = Zend_Auth::getInstance()->getIdentity();
         
         // Get our form and validate it
         $form = new Corp_Ratnakar_LoadReportForm(array('action' => $this->formatURL('/corp_ratnakar_reports/loadreport'),
                                                    'method' => 'POST',
                                             )); 
        $bankUnicodeArr = Util::bankUnicodesArray();
        $page = $this->_getParam('page');
        $request = $this->_getAllParams();
        $sub = $this->_getParam('sub');
        
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        $qurStr['product_id'] = $this->_getParam('product_id');
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
                 
                 $objReports = new Reports();
                 $cardload = $objReports->getLoadRequests($qurData);
                 $this->view->paginator = $objReports->paginateByArray($cardload, $page, $paginate = NULL);
                 $this->view->formData = $qurStr;
              }   
             $form->getElement('product')->setValue($qurData['product_id']);
          }
            $this->view->form = $form;
            //$this->view->user_type = $user->user_type;
            
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
        
	$bankUnicodeArr = Util::bankUnicodesArray(); 
             
              if($form->isValid($qurStr)){ 
                  if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", 'from');
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", 'to');
                    
                   
                }
                 $qurData['product_id'] = $qurStr['product_id'];
                 $qurData['bank_unicode'] = $qurStr['bank_unicode'];
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
                );
                }
                $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'load_report');exit;
                 }catch (Exception $e) {
                                         App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                         $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                         $this->_redirect($this->formatURL('/corp_ratnakar_reports/loadreport?sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&product_id='.$qurStr['product_id'].'&bank_unicode='.$qurStr['bank_unicode'].'&status='.$qurStr['status'])); 
                                       }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid Paramaters!') );
                         $this->_redirect($this->formatURL('/corp_ratnakar_reports/loadreport?sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&product_id='.$qurStr['product_id'].'&bank_unicode='.$qurStr['bank_unicode'].'&status='.$qurStr['status'])); 
                      }             
                 
       }
    
    /*
     * balancesyncexception() report will return load request which are failed at ecs during card mapping  
     */
    public function balancesyncexceptionAction(){
        $this->title = 'Balance Sync Exception Report'; 
        
        // Get our form and validate it
        $form = new Corp_Ratnakar_BalanceSyncExceptionReportForm(array('action' => $this->formatURL('/corp_ratnakar_reports/balancesyncexception'), 'method' => 'POST'));
       $page = $this->_getParam('page');
       $sub = $this->_getParam('sub');

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

                   $this->view->title = 'Balance Sync Exception Report from '.Util::returnDateFormatted($qurFrm['from'], "Y-m-d", "d-m-Y", "-");
                   $this->view->title .= ' to '.Util::returnDateFormatted($qurFrm['to'], "Y-m-d", "d-m-Y", "-");
                   $this->view->from = $qurFrm['from'];
                   $this->view->to   = $qurFrm['to'];
                }
                
                $objReports = new Reference();
                $loadexceptions = $objReports->getBalanceSyncExceptions($qurData);
                $this->view->paginator = $objReports->paginateByArray($loadexceptions, $page, $paginate = NULL);
                $this->view->formData = $qurStr;
             }
        }
        $this->view->form = $form;
    }
    
    public function exporbalancesyncexceptionAction(){
        
        // Get our form and validate it
        $form = new Corp_Ratnakar_BalanceSyncExceptionReportForm(array('action' => $this->formatURL('/corp_ratnakar_reports/balancesyncexception'), 'method' => 'POST')); 

        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
             
        if($form->isValid($qurStr)){ 
            if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", 'from');
                $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", 'to');
            }

            $objReports = new Reference();
            $exportData = $objReports->exportgetBalanceSyncException($qurData);
                 
                $columns = array(
                    'Agent Code',
                    'Agent Name',
                    'Card Pack ID',
                    'Amount',
                    'Error Reason',
                    'Date & Time',
                    'ECS Customer registration status'
                );

                $objCSV = new CSV();
                try{
                    $objCSV->export($exportData, $columns, 'balance_sync_exception_report');exit;
                } catch (Exception $e) {
                    App_Logger::log($e->getMessage() , Zend_Log::ERR);
                    $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                    $this->_redirect($this->formatURL('/corp_ratnakar_reports/balancesyncexception?sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'])); 
                }
            } else {
                $this->_helper->FlashMessenger( array('msg-error' => 'Invalid Paramaters!') );
                $this->_redirect($this->formatURL('/corp_ratnakar_reports/balancesyncexception?sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'])); 
            }   
       }
}