<?php

class Validator_AgentBalance extends Validator_LimitValidator
{
    
  /**
     * Column for the primary key
     *
     * @var string
     * @access protected
     */
    protected $_primary = 'id';
    
    /**
     * Holds the table's name
     *
     * @var string
     * @access protected
     */
    protected $_name = DbTable::TABLE_AGENT_BALANCE;
    
    
    /*  Agent Allowed first load of selected product     */
    public function chkAllowFirstLoad($params)
    {
        if($params['agent_id'] == '' || $params['product_id'] == '' || $params['amount'] == '')
            throw new Exception ('Insufficient Data');
        
        /* check agent available balance */
        $availableAgentBalance = $this->chkAvailableAgentBalance($params['agent_id'], $params['amount']);
        if($availableAgentBalance)
        {
            /* check product per agent limit for first load */
            $allowFirstLoadAmt = $this->chkAgentProductFirstLoad($params);
            if($allowFirstLoadAmt)
            {
                /* check agent limits */
                $this->chkAllowAgentLoad($params);
            }
        }
        return true;
               
    }
    
    /*
     * on basis of product min first load
     */
    private function chkAgentProductFirstLoad($params)
    {
        $agentProdLimitDetails = $this->getAgentProductLimitDetails($params['agent_id'], $params['product_id']);
        /*echo "<br>agentProdLimitDetails: <pre>";
        print_r($agentProdLimitDetails);
        echo "</pre>";*/
        if($agentProdLimitDetails)
        {
            if($params['amount'] < $agentProdLimitDetails['limit_out_first_load'])
            {
                throw new Exception ("Amount less than Product Limit First Load for Agent. Min First Load Amount: ".Util::numberFormat($agentProdLimitDetails['limit_out_first_load']).". Amount tried: ".Util::numberFormat($params['amount']));
                return false;
            }
        }
        return true;
    }
   
    /*  Agent Allowed card reloads of selected product     */
    public function chkAllowReLoad($params)
    {
        if($params['agent_id']=='' || $params['product_id']=='' || $params['amount']=='')
            throw new Exception ('Insufficient Data');
        
         /* check agent available balance */
        $availableAgentBalance = $this->chkAvailableAgentBalance($params['agent_id'], $params['amount']);
        if($availableAgentBalance)
        {
            /* check product per agent limit for subsequent load */
            $allowReLoadAmt = $this->chkAgentProductReLoad($params);
            if($allowReLoadAmt)
            {
                /* check agent limits */
                $this->chkAllowAgentLoad($params);

            }
        }
        return true;
               
    }
   
    /*  Agent can assign selected product   
     * params: agent_id, product_id  */
    public function chkCanAssignProduct($params)
    {
        if($params['agent_id'] == '' || $params['product_id'] == '')
            throw new Exception ('Insufficient Data');
        
        /* check agent available balance */
        $availableAgentBalance = $this->getAgentBalance($params['agent_id']);
        if($availableAgentBalance <= 0)
        {
            throw new Exception('Insufficient Funds');
            return false;
        }
        
        /* check product per agent limit for first load */
        $allowFirstLoadAmt = $this->getAgentProductLimitDetails($params['agent_id'], $params['product_id']);
        if($allowFirstLoadAmt)
        {
            if($availableAgentBalance < $allowFirstLoadAmt['limit_out_first_load'])
            {
                throw new Exception ('You have Insufficient Balance in your Account for this Transaction.');
                return false;
            }
        }
        
        /* check agent limits */
        $agentLimitDetails = $this->getAgentLimitDetails($params['agent_id']);
        if($agentLimitDetails)
        {
            if($availableAgentBalance < $agentLimitDetails['limit_out_min_txn'])
            {
                throw new Exception ("You have Insufficient Balance in your Account for this Transaction.");
                exit;
            }
            /* agent limits contd. */
            $this->getAgentTxnStats($params['agent_id'], $agentLimitDetails);
        }
        
        return true;
        
        
               
    }
   
    private function getAgentTxnStats($agentId, $agentLimitDetails)
    {
        $amount = 0;
        $txnAgentModel = new TxnAgent();
        
        // DAILY LIMITS
        $curDate = date('Y-m-d'); 
        $row = $txnAgentModel->getTxnAgentDaily($agentId, $curDate);
        if($row)
        {
            $count = ($row['count'] > 0) ? $row['count'] : 0;
            $total = ($row['total'] > 0) ? $row['total'] : 0;
            $flglimit = $this->getAgentLoadLimitFlag($count, $total, $amount, $agentLimitDetails['cnt_out_max_txn_daily'], $agentLimitDetails['limit_out_max_daily'], "Daily");
        }
        // MONTHLY LIMITS
        $curMonth = date('m');
        $curYear = date('Y');
        $curMonthDays = Util::getMonthDays($curMonth, $curYear);
        $startDate = $curYear.'-'.$curMonth.'-01';
        $endDate = $curYear.'-'.$curMonth.'-'.$curMonthDays;   
        $row = $txnAgentModel->getTxnAgentDuration($agentId, $startDate, $endDate);
        if($row)
        {
            $count = ($row['count'] > 0) ? $row['count'] : 0;
            $total = ($row['total'] > 0) ? $row['total'] : 0;
            $flglimit = $this->getAgentLoadLimitFlag($count, $total, $amount, $agentLimitDetails['cnt_out_max_txn_monthly'], $agentLimitDetails['limit_out_max_monthly'], "Monthly");
        }
        // YEARLY LIMITS
        $startDate = date("Y-01-01");
        $endDate = date("Y-12-31");   
        $row = $txnAgentModel->getTxnAgentDuration($agentId, $startDate, $endDate);
        if($row)
        {
            $count = ($row['count'] > 0) ? $row['count'] : 0;
            $total = ($row['total'] > 0) ? $row['total'] : 0;
            $flglimit = $this->getAgentLoadLimitFlag($count, $total, $amount, $agentLimitDetails['cnt_out_max_txn_yearly'], $agentLimitDetails['limit_out_max_yearly'], "Yearly");
        }
        return true;
    }
 
    public function chkAgentMaxMinLoad($param , $returnValues = FALSE){
        
        $objAgBal = new AgentSetting();
        $agSettingData = $objAgBal->getAllSettings(array('section_id'=>$param['section_id']));
        
        $agSettingDataArr = $agSettingData->toArray();
        $throwException = false;
       
        foreach($agSettingDataArr as $key=>$val){
            
            if($val['type']==AGENT_SETTING_MIN_TYPE){
                $minAmount = $val['value'];
                if($param['amount']<$val['value']){
                    $throwException = true;
                    
                }                
            } else if($val['type']==AGENT_SETTING_MAX_TYPE){
                $maxAmount = $val['value'];
                if($param['amount'] > $val['value']){
                    $throwException = true;
                } 
            }
        }    
        if($returnValues){
             return $array = array('minValue' =>$minAmount,'maxValue' =>$maxAmount );
        }
        else{
        if($throwException){
            throw new Exception('Agent fund load must be between '.Util::numberFormat($minAmount).' and '.Util::numberFormat($maxAmount).', you loaded '.Util::numberFormat($param['amount']).'!');
            return false;
        } else { return true;}
        }
    }
    
    
}
