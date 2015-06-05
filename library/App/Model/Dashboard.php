<?php

class Dashboard extends App_Model
{
    /**
     * Column for the primary key
     *
     * @var string
     * @access protected
     */
    protected $_primary = '';
    
    /**
     * Holds the table's name
     *
     * @var string
     * @access protected
     */
    protected $_name = '';//DbTable::TABLE_AGENT_BALANCE;
    
    private $_msg;
    
    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    //protected $_rowClass = 'App_Table_CardholderUser';
    
    /* getOpsDashboardStats() will return the all stats of ops dashboard
     */
    public function getOpsDashboardStats(){
        /***** initialization section *****/
        $duration = 'month';
        $dates = Util::getDurationDates($duration);
        
        $toArr = explode(" ", $dates['to']);
        $to = $toArr[0];
        $fromArr = explode(" ", $dates['from']);
        $from = $fromArr[0];
        $cuDate = date('Y-m-d');
        $param = array('to'=> $to, 'from'=>$from);
        
        $objRemitter = new Remit_Boi_Remitter();
        $objRemitRequest = new Remit_Remittancerequest();
        $objAgentUser = new AgentUser();
        $objCardLoads = new CardLoads();
        $objFundRequest = new FundRequest();
        $objCHUser = new Mvc_Axis_CardholderUser();
        $commissionReport = new CommissionReport();
        $objRATRemitter = new Remit_Ratnakar_Remitter();
        /***** initialization section ends here *****/
        
        
        // getting remitter registered count
        $remitterInfo = $objRemitter->getRemitterRegistrationsCount($param);
        $totalRemitterCount = $remitterInfo['total_remitter_count'];
        
         // getting total remittance done successfully with its count
        $remittanceInfo = $objRemitRequest->getAgentTotalRemittanceFeeSTax($param, array(STATUS_SUCCESS));
        $totalSuccessRemittance = $remittanceInfo['agent_total_remittance'];
        $totalSuccessRemittanceCount = $remittanceInfo['agent_total_remittance_count'];
        
        
         // getting total remittance failed for current month with its count
        $remittanceInfo = $objRemitRequest->getAgentTotalRemittanceFeeSTax($param, array(STATUS_FAILURE, STATUS_REFUND));
        $totalFailureRemittance = $remittanceInfo['agent_total_remittance'];
        $totalFailureRemittanceCount = $remittanceInfo['agent_total_remittance_count'];
        
         // getting total remittance Pending for current month with its count
        $remittanceInfo = $objRemitRequest->getAgentTotalRemittanceFeeSTax($param, array(STATUS_IN_PROCESS, STATUS_PROCESSED));
        $totalPendingRemittance = $remittanceInfo['agent_total_remittance'];
        $totalPendingRemittanceCount = $remittanceInfo['agent_total_remittance_count'];
        
        
        // getting total batch files created of the day
        $batchFilesParam = array('batchDate'=>$cuDate);
        $batchFilesInfo = $objRemitRequest->getTotalNEFTRequest($batchFilesParam);
        $totalBatchFilesCount = $batchFilesInfo['total_batch_files'];
        
        
        // getting total batch files created of the day
        $unprocessedBatchParam = array('neftStatus'=>FLAG_NO);
        $unprocessedBatchInfo = $objRemitRequest->getTotalNEFTRequest($unprocessedBatchParam);
        $totalUnprocessedBatchCount = $unprocessedBatchInfo['total_batch_files'];
        
        // agents approved
        $paramAgents = array('dateTo'=>$dates['to'], 'dateFrom'=>$dates['from']);
        $appAgents = $objAgentUser->getApprovedAgentsCount($paramAgents);
        $totalApprovedAgentsCount = $appAgents['approved_agents_count'];

        // agents pending
        $pendingAgents = $objAgentUser->getPendingAgentsCount($paramAgents);
        $totalPendingAgentsCount = $pendingAgents['pending_agents_count'];
        
        // agents fund approved     
        $agentApprovedFund = $objFundRequest->getTotalAgentApprovedFund(array('dateTo'=>$to, 'dateFrom'=>$from));
        $totalAgentApprovedFund = $agentApprovedFund['total_agent_approved_fund'];

        // agents pending fund
        $agentPendingFund= $objAgentUser->getAgentPendingFund($paramAgents);
        $totalAgentPendingFund = $agentPendingFund['agent_pending_fund'];
        
        // agents load reload to cardholders
        $agentLoadReload= $objCardLoads->getAgentTotalLoadReload(array('dateTo'=>$to, 'dateFrom'=>$from));
        $totalAgentLoadReload = $agentLoadReload['total_agent_load_reload'];

        // approved/closed cardholders count
        $chCount= $objCHUser->getApprovedClosedCHCount($paramAgents);
        $totalCardholdersRegistered = $chCount['cardholders_count'];
        
        // highest earning agent of month
        $agentCommissionArray = $commissionReport->getHighestEarningAgent(array('dateFrom'=>$dates['from'], 'dateTo'=>$dates['to']));
        $highestEarningAgent = isset($agentCommissionArray[0]['agent_name'])?$agentCommissionArray[0]['agent_name']:'';
        
       
        // consolidating final array of dashboard stats data
        $dashboardData = array(
                                'totalRemitterCount'=>$totalRemitterCount,
                                'totalSuccessRemittance'=>$totalSuccessRemittance,
                                'totalSuccessRemittanceCount'=>$totalSuccessRemittanceCount,
                                'totalFailureRemittance'=>$totalFailureRemittance,
                                'totalFailureRemittanceCount'=>$totalFailureRemittanceCount,
                                'totalPendingRemittance'=>$totalPendingRemittance,
                                'totalPendingRemittanceCount'=>$totalPendingRemittanceCount,
                                'totalBatchFilesCount'=>$totalBatchFilesCount,
                                'totalUnprocessedBatchCount'=>$totalUnprocessedBatchCount,
                                'totalApprovedAgentsCount'=>$totalApprovedAgentsCount,
                                'totalPendingAgentsCount'=>$totalPendingAgentsCount,
                                'totalAgentApprovedFund'=>$totalAgentApprovedFund,
                                'totalAgentPendingFund'=>$totalAgentPendingFund,
                                'totalAgentLoadReload'=>$totalAgentLoadReload,
                                'totalCardholdersRegistered'=>$totalCardholdersRegistered,
                                'highestEarningAgent'=>$highestEarningAgent,
                              );
      
        return $dashboardData;
    }

    
    /* getAgentDashboardStats function will return the agent dashboard stats regarding load/reload, commission, 
     * total loads reloads etc....
     */
    
    public function agentDashboardStats(){
       
       $objAgentBalance = new AgentBalance();
       $objAgentVirtual = new AgentVirtualBalance();
       $objCommReport = new CommissionReport();
       $user = Zend_Auth::getInstance()->getIdentity();
       $agentId = $user->id;
       $agentUser = new AgentUser();
       $remitters = new Remit_Remitter();
       $objRemittanceRequest = new Remit_Remittancerequest();
       $commissionReport = new CommissionReport();
       $cardloads = new CardLoads();
       $objRATRemitter = new Remit_Ratnakar_Remitter();
       $objRatnakarRemitRequest = new Remit_Ratnakar_Remittancerequest();
       $weekAllDates = Util::getDurationAllDates('week');
       $week = Util::getDurationDates('week');
       // agent balanace
       $agentStats['agentBal'] = $objAgentBalance->getAgentBalance($agentId);
       $agentStats['agentVirtualBal'] = $objAgentVirtual->getAgentBalance($agentId); 
       // agent's cardholders count
       //$agentStats['chCount'] = $this->getRegisteredCardholderCount($agentId);
     
       // agent's total load n cardholders count
       $duration = Util::getDurationDates('month');
       $chLoadParam = array('agentId'=>$agentId, 'dateFrom'=>$duration['from'], 'dateTo'=>$duration['to']);
       $agentStats['chLoadCount'] = $agentUser->getAgentLoadAndCount($chLoadParam);
       
       // agent's earning of month till date
       $dateFromArr = explode(' ', $duration['from']);
       $dateFrom = $dateFromArr[0];
       $dateToArr = explode(' ', $duration['to']);
       $dateTo = $dateToArr[0];
       
       // agent's reload n cardholders count
       $chloadReloadParam = array('agent_id'=>$agentId, 'dateFrom'=>$dateFrom, 'dateTo'=>$dateTo);
       $cardLoadReload = $cardloads->getAgentTotalLoadReload($chloadReloadParam);
       
       $agentStats['chloadReloadamt'] = $cardLoadReload['total_agent_load_reload'];
     
       $commParam = array('agentId'=>$agentId, 'dateFrom'=>$dateFrom, 'dateTo'=>$dateTo,'bank_unicode' => $user->bank_unicode );
       $agentComm = $objCommReport->getAgentCommission($commParam);
       $agentStats['total_agent_commission'] = $agentComm['total_agent_commission'];
       /********** Agent Commission/earnings over the week****************/
       $agentCommission = $objCommReport->getAgentComm(array('agentId'=>$agentId, 'from'=>$week['from'], 'to'=>$week['to']));
       $graphdatacomm = '';
//       $graphdatacommArr = '';
       $weekcommArr = array();
       $graphdataArrcomm = '';

       if(empty($agentCommission)) {
           foreach($weekAllDates as $val){
               $date = explode(" ",$val['from']);
               $graphdatacomm .="['".Util::returnDateFormatted($date[0], 'Y-m-d', 'd-m-Y', '-')."', 0],";
               }
           
           $agentCommissionArr = substr($graphdatacomm ,0, -1);
       }
       else{// get all days of the week to get 0 value on days on which data is not there
           $i = 0;
           foreach($weekAllDates as $val){
            
               $date = explode(" ",$val['from']); 
               $weekcommArr[$i]['date'] = $date[0];
               $weekcommAmt = 0;
               // Agent commission Array with date and amount
               foreach($agentCommission as $value){
               if($date[0] == $value['date']){
               
                $weekcommAmt = $value['amount'];
                  break ;
               }
               
            
               } // End of Agent remittance Array with date and amount
           $weekcommArr[$i]['amount'] = $weekcommAmt;    
           $graphdataArrcomm .="['".Util::returnDateFormatted($weekcommArr[$i]['date'], 'Y-m-d', 'd-m-Y', '-')."', ".$weekcommArr[$i]['amount']."],";

               $i++;
             
           }//end of foreach of week days
                  
           $agentCommissionArr = substr($graphdataArrcomm ,0, -1);

           }
//       echo $agentCommissionArr;
       $agentStats['agent_commission'] = $agentCommissionArr;
       
       /********** End of Agent Commission/earnings over the week****************/
       // *************Get agent's remittance related data***************/
       // Remittance done over the week by the agent
       
       $agentRemittance = $objRemittanceRequest->getAgentRemittance(array('agentId'=>$agentId, 'from'=>$week['from'], 'to'=>$week['to'],'bank_unicode' => $user->bank_unicode));
       $graphdata = '';
       $graphdataArr = '';
       $weekArr = array();
       if(empty($agentRemittance)) {
           foreach($weekAllDates as $val){
               $date = explode(" ",$val['from']);
               $graphdata .="['".Util::returnDateFormatted($date[0], 'Y-m-d', 'd-m-Y', '-')."', 0],";
               }
           
           $agentRemittanceArr = substr($graphdata ,0, -1);
       }
       else{// get all days of the week to get 0 value on days on which data is not there
           $i = 0;
           foreach($weekAllDates as $val){
            
               $date = explode(" ",$val['from']); 
               $weekArr[$i]['date_created'] = $date[0];
               $weekArrAmt = 0;
               // Agent remittance Array with date and amount
               foreach($agentRemittance as $value){
                   
               if($date[0] == $value['date_created']){
                   $weekArrAmt = $value['amount']; 
                  break ;
               }
               
            
               } // End of Agent remittance Array with date and amount
               
               $weekArr[$i]['amount'] = $weekArrAmt;
               $graphdataArr .="['".Util::returnDateFormatted($weekArr[$i]['date_created'], 'Y-m-d', 'd-m-Y', '-')."', ".$weekArr[$i]['amount']."],";

               $i++;
             
           }//end of foreach of week days
                  
           $agentRemittanceArr = substr($graphdataArr ,0, -1);

           }
         
      $agentStats['agent_remittance'] = $agentRemittanceArr;
        // END of Remittance done over the week by the agent
        
       /************** Remitters registered over the week*/
       
       $agentRemittersrgn =  $remitters->getRemittersCount(array('agentId'=>$agentId, 'from'=>$week['from'], 'to'=>$week['to'],'bank_unicode'=> $user->bank_unicode));
       $graphdatargn = '';
       $graphdatargnArr = '';
       $weekrgnArr = array();
       if(empty($agentRemittersrgn)) {
           foreach($weekAllDates as $val){
               $date = explode(" ",$val['from']);
               $graphdatargn .="['".Util::returnDateFormatted($date[0], 'Y-m-d', 'd-m-Y', '-')."', 0],";
               }
           
           $agentRemittersrgnArr = substr($graphdatargn ,0, -1);
       }
       else{// get all days of the week to get 0 value on days on which data is not there
           $i = 0;
           foreach($weekAllDates as $val){
            $date = explode(" ",$val['from']); 
               $weekrgnArr[$i]['date_created'] = $date[0];
               $weekArrrgncnt = 0;
               // Agent remittance Array with date and amount
               foreach($agentRemittersrgn as $value){
               if($date[0] == $value['date_created']){
                   $weekArrrgncnt = $value['remitter_count']; 
                  break ;
               }
               
            
               } // End of Agent remittance Array with date and amount
               
               $weekrgnArr[$i]['remitter_count'] = $weekArrrgncnt;
               $graphdatargnArr .="['".Util::returnDateFormatted($weekrgnArr[$i]['date_created'], 'Y-m-d', 'd-m-Y', '-')."', ".$weekrgnArr[$i]['remitter_count']."],";
            //echo $graphdatargnArr;
               $i++;
             
           }//end of foreach of week days
                  
           $agentRemittersrgnArr = substr($graphdatargnArr,0, -1);

           }
         
      $agentStats['agent_remittersrgn'] = $agentRemittersrgnArr;
       
       /************** End of Remitters registered over the week*/
       //Total Remitter registered of the Month
       $totalRemitters = $remitters->getRemittersRgnCount(array('agentId'=>$agentId, 'from'=>$duration['from'], 'to'=>$duration['to'],'bank_unicode'=> $user->bank_unicode));
       
       $agentStats['remitter_count'] = $totalRemitters['remitter_count'];
    
//Total Remittance done of this Month (Amount/Count)
       
       $remittanceArr = $objRemittanceRequest->getAgentRemittanceCountandSum(array('agentId'=>$agentId, 'from'=>$duration['from'], 'to'=>$duration['to'],'bank_unicode' => $user->bank_unicode));
       $agentStats['remittance_count'] = $remittanceArr['count'];
       $agentStats['remittance_total'] = $remittanceArr['total'];
       
    /*
     * Total Remittance done of the day
     */   
       $agentProduct = $agentUser->getAgentBindingProducts($user->id);

        $arrProductUnicode = array();
               if(count($agentProduct) > 0){
                   foreach($agentProduct as $aprod) {
                       $arrProductUnicode[] = $aprod['product_unicode'];
                   }
               }
        $prodRemitRatnakar = App_DI_Definition_BankProduct::getInstance(BANK_RATNAKAR_REMIT);
        $prodRemitRatnakarUnicode = $prodRemitRatnakar->product->unicode;
        $remitUnicodes = Util::productUnicodesArray(PROGRAM_TYPE_REMIT);
        if(in_array($prodRemitRatnakarUnicode, $arrProductUnicode)) {
        $allowRatRemit = TRUE;
        }
      
         $date_today = Util::getDurationDates('today');
        if($allowRatRemit){
           
            if(isset($user->id) && $agentUser->isSuperAgent($user->id)) {
             $allagentList = $objRATRemitter->getAllAgentList(array('user_id'=>$user->id,'user_type'=>$user->user_type,'bank_unicode' => $user->bank_unicode));    
             if($allagentList != '' || !empty($allagentList)){
             $todayRemittanceArr = $objRatnakarRemitRequest->getRemittanceSum(array('agent_id_list'=>$allagentList, 'from'=>$date_today['from'], 'to'=>$date_today['to'],'bank_unicode' => $user->bank_unicode));
             $agentStats['today_remittance_count'] = $todayRemittanceArr['count'];
             $agentStats['today_remittance_total'] = $todayRemittanceArr['total'];
             }
             else{
               $agentStats['today_remittance_count'] = 0;
                 $agentStats['today_remittance_total'] = 0;  
             }
            }elseif(isset($user->id) && $agentUser->isDistributorAgent($user->id)){
              
             $allagentList = $objRATRemitter->getAllAgentList(array('user_id'=>$user->id,'user_type'=>$user->user_type,'bank_unicode' => $user->bank_unicode));    
             if($allagentList != '' || !empty($allagentList)){
             $todayRemittanceArr = $objRatnakarRemitRequest->getRemittanceSum(array('agent_id_list'=>$allagentList, 'from'=>$date_today['from'], 'to'=>$date_today['to'],'bank_unicode' => $user->bank_unicode));
             $agentStats['today_remittance_count'] = $todayRemittanceArr['count'];
             $agentStats['today_remittance_total'] = $todayRemittanceArr['total'];
             }else{
                 $agentStats['today_remittance_count'] = 0;
                 $agentStats['today_remittance_total'] = 0;
             }
            }else{
           $todayRemittanceArr = $objRemittanceRequest->getAgentRemittanceCountandSum(array('agentId'=>$agentId, 'from'=>$date_today['from'], 'to'=>$date_today['to'],'bank_unicode' => $user->bank_unicode));
           $agentStats['today_remittance_count'] = $todayRemittanceArr['count'];
           $agentStats['today_remittance_total'] = $todayRemittanceArr['total'];

            } 

            /*
             * Month Transaction 
             */
            if(isset($user->id) && $agentUser->isSuperAgent($user->id)) {
             $allagentList = $objRATRemitter->getAllAgentList(array('user_id'=>$user->id,'user_type'=>$user->user_type,'bank_unicode' => $user->bank_unicode));    
             if($allagentList != '' || !empty($allagentList)){
             $monthRemittanceArr = $objRatnakarRemitRequest->getRemittanceSum(array('agent_id_list'=>$allagentList, 'from'=>$duration['from'], 'to'=>$duration['to'],'bank_unicode' => $user->bank_unicode));
             $agentStats['month_remittance_count'] = $monthRemittanceArr['count'];
             $agentStats['month_remittance_total'] = $monthRemittanceArr['total'];
             }
             else{
                 $agentStats['month_remittance_count'] = 0;
             $agentStats['month_remittance_total'] = 0;
             }
            }elseif(isset($user->id) && $agentUser->isDistributorAgent($user->id)){
             $allagentList = $objRATRemitter->getAllAgentList(array('user_id'=>$user->id,'user_type'=>$user->user_type,'bank_unicode' => $user->bank_unicode));    
             if($allagentList != '' || !empty($allagentList)){
             $monthRemittanceArr = $objRatnakarRemitRequest->getRemittanceSum(array('agent_id_list'=>$allagentList, 'from'=>$duration['from'], 'to'=>$duration['to'],'bank_unicode' => $user->bank_unicode));
             $agentStats['month_remittance_count'] = $monthRemittanceArr['count'];
             $agentStats['month_remittance_total'] = $monthRemittanceArr['total'];
             }
             else{
                $agentStats['month_remittance_count'] = 0;
             $agentStats['month_remittance_total'] = 0; 
             }
            }else{
           $monthRemittanceArr = $objRemittanceRequest->getAgentRemittanceCountandSum(array('agentId'=>$agentId, 'from'=>$duration['from'], 'to'=>$duration['to'],'bank_unicode' => $user->bank_unicode));
           $agentStats['month_remittance_count'] = $monthRemittanceArr['count'];
           $agentStats['month_remittance_total'] = $monthRemittanceArr['total'];

            }
        }else{
           $todayRemittanceArr = $objRemittanceRequest->getAgentRemittanceCountandSum(array('agentId'=>$agentId, 'from'=>$date_today['from'], 'to'=>$date_today['to'],'bank_unicode' => $user->bank_unicode));
           $agentStats['today_remittance_count'] = $todayRemittanceArr['count'];
           $agentStats['today_remittance_total'] = $todayRemittanceArr['total'];
           $monthRemittanceArr = $objRemittanceRequest->getAgentRemittanceCountandSum(array('agentId'=>$agentId, 'from'=>$duration['from'], 'to'=>$duration['to'],'bank_unicode' => $user->bank_unicode));
           $agentStats['month_remittance_count'] = $monthRemittanceArr['count'];
           $agentStats['month_remittance_total'] = $monthRemittanceArr['total'];
            
        }
             // Agent's Commission
       //Aaj Ki kamaaii
       /*
        * For Super Distrobutor and Distributor Agents
        */
       
       $today = date('Y-m-d h:m:s');
       $commission = $commissionReport->getAgentCommission(array('agentId'=>$agentId, 'dateFrom'=>$today, 'dateTo'=>$today,'bank_unicode' => $user->bank_unicode));
       $agentStats['today_commission'] = $commission['total_agent_commission'];
       // Top highest earning agent for the month

       $agentCommissionarray = $commissionReport->getHighestEarningAgent(array('dateFrom'=>$dateFrom, 'dateTo'=>$dateTo));
       
       $numOfAgents = count($agentCommissionarray);
       $i = 0; 
       $numOfAgents = count($agentCommissionarray);

       foreach($agentCommissionarray as $val){

            if(in_array($agentId,$val)){
                break;
            }
            $i++;

       }
        if($numOfAgents > 0){
          $rank = ($i/$numOfAgents) * 100;
       
       switch ($rank) {
            case $rank <= 30:
                $msg = 'Congratulations, you are among the top highest earning agents for the Month!';
                break;
            case $rank > 30 && $rank < 60 :
                $msg = 'You are among the top performers, keep up the good work!';
                break;
            case $rank > 60:
                $msg = 'You can do better!';
                break;
       } 
       $agentStats['agent_msg'] = $msg;
       $agentStats['highest_earning_agent'] = $agentCommissionarray[0]['agent_name'];
     }
    
       return $agentStats; 
     }

      /* getBankDashboardStats() will return the all stats of ops dashboard
     */
    public function getBankDashboardStats(){
        /***** initialization section *****/
        $duration = 'month';
        $dates = Util::getDurationDates($duration);
        $cuDate = date('Y-m-d');
        $param = array('to'=> $dates['to'], 'from'=> $dates['from']);
        
        $toArr = explode(" ", $dates['to']);
        $to = $toArr[0];
        $fromArr = explode(" ", $dates['from']);
        $from = $fromArr[0];
        $paramDate = array('to'=> $to, 'from'=>$from);
        
        $customerModel = new Corp_Kotak_Customers();
        /***** initialization section ends here *****/
        $appApproved = $customerModel->applicationApprovedCount($param);
        
        $phyAppAccepted = $customerModel->physicalAppAcceptedCount($paramDate);
        
        $appPendingCount = $customerModel->applicationPendingCount();
        
        $pendingPhy = $customerModel->pendingPhysical();
       
        // consolidating final array of dashboard stats data
        $dashboardData = array(
                                'applicationApprovedCount' => $appApproved['count'],
                                'physicalAppAcceptedCount' => $phyAppAccepted['count'],
                                'applicationPendingCount' => $appPendingCount['count'],
                                'pendingPhysical' => $pendingPhy['count'],
                              );
      
        return $dashboardData;
    }
    /* getAgentDashboardStats function will return the agent dashboard stats regarding load/reload, commission, 
     * total loads reloads etc....
     */
    
     public function corporateDashboardStats(){
       
       $objCorporateBalance = new CorporateBalance();
       $ratcardHolders = new Corp_Ratnakar_Cardholders();
       $ratcardLoads = new Corp_Ratnakar_Cardload();
       $koratcardHolders = new Corp_Kotak_Customers();
       $koratcardLoads = new Corp_Kotak_Cardload();
       $user = Zend_Auth::getInstance()->getIdentity();
       $corporateId = $user->id;
       
       // agent balanace
       $corporateStats['corporateBal'] = $objCorporateBalance->getCorporateBalance($corporateId);
       $employeesEnrolled = 0;
       $employeesEnrolled = $employeesEnrolled + $ratcardHolders->getCardholderCount(array('by_corporate_id'=>$corporateId));
       $employeesEnrolled = $employeesEnrolled + $koratcardHolders->getCustomerCount(array('by_corporate_id'=>$corporateId));
       $corporateStats['employeesEnrolled'] = $employeesEnrolled;
       
       $employeesActive = 0;
       $employeesActive = $employeesActive + $ratcardHolders->getCardholderCount(array('by_corporate_id'=>$corporateId,'status'=>STATUS_ACTIVE));
       $employeesActive = $employeesActive + $koratcardHolders->getCustomerCount(array('by_corporate_id'=>$corporateId,'status'=>STATUS_ACTIVE));
       $corporateStats['employeesActive'] = $employeesActive;
       
       $totalloadamount = 0;
       $totalloadamount = $totalloadamount + $ratcardLoads->getCardloadCount(array('by_corporate_id'=>$corporateId,'txn_type'=>TXNTYPE_RAT_CORP_CORPORATE_LOAD,'status'=>STATUS_LOADED));
       $data = $koratcardLoads->getTotalLoad(array('by_corporate_id'=>$corporateId,'txn_type'=>TXNTYPE_KOTAK_CORP_CORPORATE_LOAD,'status'=>STATUS_LOADED));
       $totalloadamount = $totalloadamount + $data->total_load_amount;
       
       $corporateStats['totalloadamount'] = $totalloadamount;
       //echo "<pre>";print_r($corporateStats);
       return $corporateStats; 
     }
    
}