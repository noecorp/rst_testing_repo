<?php

/**
 * Ops can view Reports 
 *
 * @package frontend_controllers
 * @copyright company
 */

class HistoryController extends App_Operation_Controller
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
    
    
    
    /* logsAction() will fetch the list of agent logs and show to interface
      */
     public function agentAction()
    {
        $this->view->pageTitle = 'Agent Logs';
        $objAgent = new Agents();
        $form = new AgentLogsForm(array('action' => $this->formatURL('/history/agent'),
                                                    'method' => 'POST',
                                             ));
        
        $request = $this->_getAllParams();
        $qurStr['sub'] = $this->_getParam('sub');
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['agent_id'] = $this->_getParam('agent_id');
        
       if($qurStr['sub']!=''){ 
             
              if($form->isValid($qurStr)){ 
                 $this->view->submit = $this->_getParam('submit');
                 //$formData  = $this->_request->getPost();
                 $durationArr = Util::getDurationDates($qurStr['dur']);
                 $qurDateFrom = explode(' ', $durationArr['from']);
                 $qurData['from'] = $qurDateFrom[0];
                 $qurDateTo = explode(' ', $durationArr['to']);
                 $qurData['to'] = $qurDateTo[0];
                 $qurData['agent_id'] = $qurStr['agent_id'];
                 $agentInfo = $objAgent->findById($qurStr['agent_id']);
                 $title = 'Agent Logs For '. $agentInfo->name;       
                 $this->view->title = Util::getListingTitle($title, $qurStr['dur']);
                 $objAgents = new Agents();
                 $this->view->paginator = $objAgents->getAgentLogs($qurData, $this->_getPage());
                 $this->view->formData = $qurStr; 
                 
              }                  
          }
            $this->view->form = $form;
        
    } 
    
    
    
    /* productAction() will fetch the list of product logs and show to interface
      */
     public function productAction()
    {
        $this->view->pageTitle = 'Product Logs';
        $objProduct = new Products();
        $form = new ProductLogsForm(array('action' => $this->formatURL('/history/product'),
                                                    'method' => 'POST',
                                             ));
        
        $request = $this->_getAllParams();
        $qurStr['sub'] = $this->_getParam('sub');
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['product_id'] = $this->_getParam('product_id');
        
        
        if($qurStr['sub']!=''){ 
             
              if($form->isValid($qurStr)){ 
                 $this->view->submit = $this->_getParam('submit');
                 //$formData  = $this->_request->getPost();
                 $durationArr = Util::getDurationDates($qurStr['dur']);
                 $qurDateFrom = explode(' ', $durationArr['from']);
                 $qurData['from'] = $qurDateFrom[0];
                 $qurDateTo = explode(' ', $durationArr['to']);
                 $qurData['to'] = $qurDateTo[0];
                 $qurData['product_id'] = $qurStr['product_id'];
                 $productInfo = $objProduct->findById($qurStr['product_id']);
                 $title = 'Product Logs For '. $productInfo->name; 
                 
                 $this->view->title = Util::getListingTitle($title, $qurStr['dur']);
                 //$objProducts = new Products();
                 $this->view->paginator = $objProduct->getProductLogs($qurData, $this->_getPage());
                 $this->view->formData = $qurStr; 
                 
              }                  
          }
            $this->view->form = $form;
        
    }
    
    
    
    /* bankAction() will show the list of bank logs for particular bank and date duration basis
    */
     public function bankAction()
    {
        $this->view->pageTitle = 'Bank Logs';
        $objBanks = new Banks();
        $form = new BankLogsForm(array('action' => $this->formatURL('/history/bank'),
                                                    'method' => 'POST',
                                             ));
        
        $request = $this->_getAllParams();
        $qurStr['sub'] = $this->_getParam('sub');
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['bank_id'] = $this->_getParam('bank_id');
        
        
        if($qurStr['sub']!=''){ 
             
              if($form->isValid($qurStr)){ 
                 $this->view->submit = $this->_getParam('submit');
                 //$formData  = $this->_request->getPost();
                 $durationArr = Util::getDurationDates($qurStr['dur']);
                 $qurDateFrom = explode(' ', $durationArr['from']);
                 $qurData['from'] = $qurDateFrom[0];
                 $qurDateTo = explode(' ', $durationArr['to']);
                 $qurData['to'] = $qurDateTo[0];
                 $qurData['bank_id'] = $qurStr['bank_id'];
                 $bankInfo = $objBanks->findById($qurStr['bank_id']);
                 $title = 'Bank Logs For '. $bankInfo->name; 

                 $this->view->title = Util::getListingTitle($title, $qurStr['dur']);

                 $this->view->paginator = $objBanks->getBankLogs($qurData, $this->_getPage());
                 $this->view->formData = $qurStr; 
                 
              }                  
          }
            $this->view->form = $form;
        
    }
    
    
    /* feeAction() will show the list of fee logs for particular bank and date duration basis
    */
     public function feeAction()
    {
        $this->view->pageTitle = 'Fee Logs';
        $objFeePlan = new FeePlan();
        $objAgent = new Agents();
        $form = new FeeLogsForm(array('action' => $this->formatURL('/history/fee'),
                                                    'method' => 'POST',
                                             ));
        
        $request = $this->_getAllParams();
        $qurStr['sub'] = $this->_getParam('sub');
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['agent_id'] = $this->_getParam('agent_id');
        
        
        if($qurStr['sub']!=''){ 
             
              if($form->isValid($qurStr)){ 
                 $this->view->submit = $this->_getParam('submit');
                 $qurData['duration'] = $qurStr['dur'];
                 $qurData['agent_id'] = $qurStr['agent_id'];
                 $agentInfo = $objAgent->findById($qurStr['agent_id']);
                 $title = 'Fee Logs For '. $agentInfo->name; 
                 
                 $this->view->title = Util::getListingTitle($title, $qurStr['dur']);
                 $result = $objFeePlan->getAgentFeePlanLogs($qurData, $this->_getPage());
                 $this->view->paginator = $objFeePlan->paginateByArray($result, $this->_getPage(), $paginate = NULL);
                 $this->view->formData = $qurStr; 
                 
              }                  
          }
            $this->view->form = $form;
        
    }
    

    
    /* commissionAction() will show the list of commission logs for particular agent and date duration basis
    */
     public function commissionAction()
    {
        $this->view->pageTitle = 'Commission Logs';
        $objCommPlan = new CommissionPlan();
        $objAgent = new Agents();
        $form = new CommissionLogsForm(array('action' => $this->formatURL('/history/commission'),
                                                    'method' => 'POST',
                                             ));
        
        $request = $this->_getAllParams();
        $qurStr['sub'] = $this->_getParam('sub');
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['agent_id'] = $this->_getParam('agent_id');
        
        
        if($qurStr['sub']!=''){ 
             
              if($form->isValid($qurStr)){ 
                 $this->view->submit = $this->_getParam('submit');
                 $qurData['duration'] = $qurStr['dur'];
                 $qurData['agent_id'] = $qurStr['agent_id'];
                 $agentInfo = $objAgent->findById($qurStr['agent_id']);
                 $title = 'Commission Logs For '. $agentInfo->name; 
                 
                 $this->view->title = Util::getListingTitle($title, $qurStr['dur']);
                 $result = $objCommPlan->getAgentCommissionPlanLogs($qurData, $this->_getPage());
                 $this->view->paginator = $objCommPlan->paginateByArray($result, $this->_getPage(), $paginate = NULL);
                 $this->view->formData = $qurStr; 
                 
              }                  
          }
            $this->view->form = $form;
        
    }
    
    
    /* cronAction() will show the list of cron logs for particular cron and date duration basis
    */
     public function cronAction()
    {
        $this->view->pageTitle = 'Cron Logs';
        $objCrons = new Crons();
        $form = new CronLogsForm(array('action' => $this->formatURL('/history/cron'),
                                                    'method' => 'POST',
                                             ));
        
        $request = $this->_getAllParams();
        $qurStr['sub'] = $this->_getParam('sub');
        $qurStr['from'] = trim($this->_getParam('from'));
        $qurStr['to'] = trim($this->_getParam('to'));
        $qurStr['cron_id'] = $this->_getParam('cron_id');
        
       
        if($qurStr['sub']!=''){ 
             if($qurStr['from'] !='' && $qurStr['to'] !=''){
               if($form->isValid($qurStr)){ 
                 
                 $this->view->submit = $this->_getParam('submit');
                 
                 $qurData['to'] = Util::returnDateFormatted($qurStr['to'], "d-m-Y", "Y-m-d", "-");
                 $qurData['from'] = Util::returnDateFormatted($qurStr['from'], "d-m-Y", "Y-m-d", "-");
                 $qurData['cron_id'] = $qurStr['cron_id'];
                 $cronInfo = $objCrons->getCronInfo(array('cron_id'=>$qurStr['cron_id']));
                 $this->view->title = 'Cron Logs For '. $cronInfo->name.' For '.$qurStr['from'].' to '.$qurStr['to']; 
                 
                 $result = $objCrons->getCronLogs($qurData);
                 $this->view->paginator = $objCrons->paginateByArray($result, $this->_getPage(), $paginate = NULL);
                 } 
                 
              } else {
                       $this->_helper->FlashMessenger( array( 'msg-error' => sprintf('Please specify from and to date range'),)); 
                     }
                 
                 $this->view->formData = $qurStr; 
                 $form->populate($qurStr);
          }
            $this->view->form = $form;
        
    }

}