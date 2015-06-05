<?php

class Mvc_Reports extends Mvc
{

   public function getAgentLoadReload($param,  $page = 1, $paginate = NULL){ 
       $param['programType'] = self::PROGRAM_TYPE;
        $objCardLoads = new CardLoads();
        return $objCardLoads->getAgentLoadReload($param,  $page);
   }
   
   
    public function getAgentWiseLoads($param,  $page = 1, $paginate = NULL){
        $param['programType'] = self::PROGRAM_TYPE;
        $objCardLoads = new CardLoads();
        return $objCardLoads->getAgentWiseLoads($param, $page,  $paginate);
   }
   
   public function getCardholderActivations($param,  $page = 1, $paginate = NULL){ 
        $param['programType'] = self::PROGRAM_TYPE;
        $objCardLoads = new CardLoads();
        return $objCardLoads->getCardholderActivations($param, $page,  $paginate);
   }
   
   /* exportAgentLoadReload function will find data for agent load reload report. 
    * it will accept param array with query filters e.g.. duration of report
    */
    public function exportAgentLoadReload($param){ 
        $param['programType'] = self::PROGRAM_TYPE;
        $objCardLoads = new CardLoads();
        $data =  $objCardLoads->exportAgentLoadReload($param);
        $retData = array();
        
        if(!empty($data))
        {    
            foreach($data as $key=>$data){
                    $retData[$key]['txn_date'] = $data['txn_date'];
                    $retData[$key]['sup_dist_code'] = $data['sup_dist_code'];
                    $retData[$key]['sup_dist_name'] = $data['sup_dist_name'];
                    $retData[$key]['dist_code'] = $data['dist_code'];
                    $retData[$key]['dist_name'] = $data['dist_name'];
                    $retData[$key]['agent_code'] = $data['agent_code'];
                    $retData[$key]['agent_name'] = $data['agent_name'];
                    $retData[$key]['estab_city'] = $data['estab_city'];
                    $retData[$key]['estab_pincode'] = $data['estab_pincode'];
                    $retData[$key]['txn_type']   = $data['txn_type'];
                    $retData[$key]['amount'] = $data['amount'];
                    $retData[$key]['crn'] = Util::maskCard($data['crn'],4);
                    $retData[$key]['mobile_number'] = $data['mobile_number'];
                    $retData[$key]['txn_code'] = $data['txn_code'];
          }
        }
        return $retData;
   }
   
   /* exportAgentWiseLoads function will find data for agent wise load report. 
    * it will accept param array with query filters e.g.. duration and agent id
    */
    public function exportAgentWiseLoads($param){ 
        $param['programType'] = self::PROGRAM_TYPE;
        $objCardLoads = new CardLoads();
        $data = $objCardLoads->exportAgentWiseLoads($param);
        $objAgent = new Agents();
        $agentInfo = $objAgent->findById($param['agent_id']);
        $retData = array();
        
        if(!empty($data))
        {
                                  
            foreach($data as $key=>$data){
                    $retData[$key]['date_created']  = $data['date_created'];
                    $retData[$key]['name']          = $agentInfo['name'];
                    $retData[$key]['agent_code']    = $agentInfo['agent_code'];
                    $retData[$key]['estab_city']    = $agentInfo['estab_city'];
                    $retData[$key]['estab_pincode'] = $agentInfo['estab_pincode'];
                    $retData[$key]['txn_type']      = $data['txn_type']; 
                    $retData[$key]['amount']        = $data['amount'];
                    $retData[$key]['crn']           = Util::maskCard($data['crn'],4);
                    $retData[$key]['mobile_number'] = $data['mobile_number'];
                    $retData[$key]['txn_code']      = $data['txn_code']; 
                   
          }
        }
        
        return $retData;
   }
   
   /* exportAgentWiseLoadsFromAgent function will find data for agent wise load report. 
    * it will accept param array with query filters e.g.. duration and agent id
    */
    public function exportAgentWiseLoadsFromAgent($param){ 
        $param['programType'] = self::PROGRAM_TYPE;
        $objCardLoads = new CardLoads();
        $data = $objCardLoads->exportAgentWiseLoads($param);
        $objAgent = new Agents();
        $agentInfo = $objAgent->findById($param['agent_id']);
        $retData = array();
        
        if(!empty($data))
        {
                                  
            foreach($data as $key=>$data){
                    $retData[$key]['date_created']  = $data['date_created'];
                    $retData[$key]['name']          = $agentInfo['name'];
                    $retData[$key]['agent_code']    = $agentInfo['agent_code'];
                    $retData[$key]['txn_type']      = $data['txn_type']; 
                    $retData[$key]['amount']        = $data['amount'];
                    $retData[$key]['cardholder_name'] = $data['cardholder_name'];
                    $retData[$key]['mobile_number'] = $data['mobile_number'];
                    $retData[$key]['ecs_product_code'] = $data['ecs_product_code'];
                    $retData[$key]['txn_code']      = $data['txn_code']; 
                   
          }
        }
        
        return $retData;
   }
   
   
   /* exportCardholderActivations function will find data for Card holder Activations report. 
    * it will accept param array with query filters e.g.. duration
    */
    public function exportCardholderActivations($param){
        $param['programType'] = self::PROGRAM_TYPE;
        $objCardLoads = new CardLoads();
        $data = $objCardLoads->exportCardholderActivations($param);
                                   
        $retData = array();
        
        if(!empty($data))
        {
            foreach($data as $key=>$data){
                    $retData[$key]['date_created'] = $data['date_created'];
                    $retData[$key]['cardholder_name'] = $data['cardholder_name'];
                    $retData[$key]['crn'] = Util::maskCard($data['crn'],4);
                    $retData[$key]['mobile_number']   = $data['mobile_number'];
                    $retData[$key]['agent_code'] = $data['agent_code'];
                    $retData[$key]['agent_name'] = $data['agent_name'];
                    $retData[$key]['agent_city'] = $data['agent_city'];                   
                    $retData[$key]['bank_name'] = $data['bank_name'];
                    //$retData[$key]['address_line1'] = $data['address_line1'];
                    //
                    //$retData[$key]['agent_pincode'] = $data['agent_pincode'];
          }
        }
        
        return $retData;
   }
   
   
   
   
}