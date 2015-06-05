<?php

class CardholderBalance extends BaseUser
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
    protected $_name = DbTable::TABLE_TXN_CARDHOLDER;
    
    private $_msg;
    
    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    //protected $_rowClass = 'App_Table_CardholderUser';
       
    
    /*public function loadCardholderBalance($param){

        try
        {
                $agentBalanceValidator = new Validator_AgentBalance();
               if($agentBalanceValidator->isSufficientAgentBalance(array('agent_id'=>$param['agent_id'], 'amount'=>$param['amount'])))
               {
                  
                    //print 'Can Load money';exit;
                   if( $this->loadBalance($param)){
                         $datas = array(
                                            'agent_id' => $param['agent_id'],
                                            'cardholder_id' => $param['cardholder_id'],
                                            'amount' => $param['amount'],
                                            'mode' => 'cr',
                                            'trans_type' => 'CDRG',
                                            'date_created' => date('Y-m-d H:i:s'),
                                        );    
                   
                        $this->insertCardholderTransaction($datas); // adding agent transactions
                   }                   
                  
               } else {
                   throw new Exception ('Insufficient Agent Balance');                  
               }
               
              // exit("END");
               return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());exit;
            return false;
        }
    }
    
    
    
    
       
    private function loadBalance($data)
    {
        
        if($data['agent_id']=='' || $data['cardholder_id']=='' || $data['amount']=='')
            throw new Exception ('Insufficient Details');
       
        $objAgentBal = new AgentBalance();
        
        $resp = $objAgentBal->updateAgentBalance($data);
        if(!$resp){
            throw new Exception('Insufficient Agent Balance');
        }
        
       // inserting the agent transaction
        if($resp) {          
                        
            $data = array(
                            'agent_id' => $data['agent_id'],                            
                            'amount' => $data['amount'],
                            'operation_id' => 0,
                            'mode' => 'dr',
                            'trans_type' => 'CDRG',
                            'date_created' => date('Y-m-d H:i:s')
                         );
            
            $objAgentBal->insertAgentTransaction($data);      
        }      
        
        return true;
    }
    
    
     private function insertCardholderTransaction($param)
     {          
        if($param['agent_id']=='' || $param['cardholder_id']=='' || $param['amount']=='' || $param['mode']=='')
            return false;        
       
        //Will Do Insert
        $resp = $this->_db->insert('t_cardholder_transactions', $param);
     }
    
     public function validateAgentFee($param) {
                
      if($param['agent_id']=='' || $param['product_id']==''){
             throw new Exception('Insufficient Agent Data');
        } 
       
            $objFee = new Agentfee();         
            $feeInfo = $objFee->getAgentFeeDetails($param); 
           
            if(empty($feeInfo)){
                throw new Exception('Fee not allocated to agent.');
                return false;
            }
                                      
            if($feeInfo['limit_first_load']>$param['amount']){
                throw new Exception('Atleast '.$feeInfo['limit_first_load'].' amount must be loaded');
                return false;
            }
            
            if($feeInfo['load_limit_max']<$param['amount']){
                throw new Exception('You cannot load more than '.$feeInfo['load_limit_max'].' amount.');
                return false;
            }
            return true;
           
     }  
     

     
*/
     
    /* public function agentToCardholder($param){
        
         if($param['agent_id']<1 || $param['product_id']<1 || $param['cardholder_id']<1 || $param['amount']<1 || $param['txn_type']==''){
             throw new Exception('Insuffient data found.'); return false;
         }
         
         try{
             $objBaseTxn = new BaseTxn();
             $resp = $objBaseTxn->agentToCardholder($param);             
             $compResp = $objBaseTxn->completeAgentToCardholder($param);  
                
             // update cardholder status is as active
             if($compResp){                    
                    $chObj->updateCardholder(array('cardholder_id'=>$param['cardholder_id'], 'status'=>CARDHOLDER_ACTIVE_STATUS));
             }
             
             return $compResp;             
            }catch (Exception $e) {
                    throw new Exception($e->getMessage());exit;
                    return false;
            }
            
            
              if($resp==1){                  
                    $chObj = new Mvc_Axis_CardholderUser();               
                    $chInfo = $chObj->getCardHolderInfo($param['cardholder_id']);

                     

                    $smsEmailData = array('amount' => $param['amount'],
                                          'mobile1' => $chInfo['mobile_country_code'].$chInfo['mobile_number'],
                                          'email' => $chInfo['email'],
                                          'cardholder_name' => $chInfo['first_name'].' '.$chInfo['middle_name'].' '.$chInfo['last_name'],
                                          'smsMessage' => 'Congratulations you have been registered with Transerv and your account is loaded with amount: '.$param['amount'],
                                          'mailSubject' => 'Registration with Transerv',                             
                                         ); 

                     $alert = new Alerts(); // sending balance info to agent 
                     $resp = $alert->sendCardholderBalance($smsEmailData, 'agent');
              }
              
              return true;
         
     }
     */
     
     
     
     public function initiateAgentToCardholder($param){
         if($param['agent_id']<1 || $param['product_id']<1 || $param['cardholder_id']<1 || $param['amount']<1 || $param['txn_type']==''){
             App_Logger::log('Insuffient data found' , Zend_Log::ERR);
             throw new Exception('Insuffient data found.'); return false;
         }
         
         try{
                $objBaseTxn = new BaseTxn();
                return $objBaseTxn->initiateAgentToCardholder($param);             
            } catch (Exception $e) {
                    App_Logger::log(serialize($e) , Zend_Log::ERR);
                    throw new Exception($e->getMessage());
                    return false;
            }
         
     }
     
     
     public function completeAgentToCardholder($param){
         if($param['agent_id'] == '' || $param['cardholder_id'] == '' || $param['amount'] == '' || $param['txn_code'] == '' || $param['txn_status'] == ''){
             throw new Exception('Insuffient data found.'); return false;
         }
         
         try{
                $objBaseTxn = new BaseTxn();
                $completeResp = $objBaseTxn->completeAgentToCardholder($param);             
               
                // update cardholder status is as active
                if($completeResp){ 
                       $chObj = new Mvc_Axis_CardholderUser();
                       $chObj->updateCardholder(array('cardholder_id'=>$param['cardholder_id'], 'status'=>CARDHOLDER_ACTIVE_STATUS));
                }
                return $completeResp;
             
            } catch (Exception $e) {
                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    throw new Exception($e->getMessage());exit;
                    return false;
            }
     }
     
     public function saveCardloads($param)
     {
         
         $data['agent_id'] = $param['agent_id'];
         $data['cardholder_id'] = $param['cardholder_id'];
         $data['product_id'] = $param['product_id'];
         $data['amount'] = $param['amount'];
         $data['txn_type'] = $param['txn_type'];
         $data['txn_code'] = $param['txn_code'];
         $data['status'] = FLAG_SUCCESS;
         $data['date_created'] = new Zend_Db_Expr('NOW()');
         $this->_db->insert(DbTable::TABLE_CARDLOADS, $data);
     }
     
      /*public function sendCardholderBalance($param){
         
            $chObj = new Mvc_Axis_CardholderUser();               
            $chInfo = $chObj->getCardHolderInfo($param['cardholder_id']);

            $smsEmailData = array('amount' => $param['amount'],
                                  'mobile1' => $chInfo['mobile_country_code'].$chInfo['mobile_number'],
                                  'email' => $chInfo['email'],
                                  'cardholder_name' => $chInfo['first_name'].' '.$chInfo['middle_name'].' '.$chInfo['last_name'],
                                  'smsMessage' => 'Congratulations you have been registered with Transerv and your account is loaded with amount: '.$param['amount'],
                                  'mailSubject' => 'Registration with Transerv',                             
                                 ); 

             $alert = new Alerts(); // sending balance info to agent 
             $resp = $alert->sendCardholderBalance($smsEmailData, 'agent');
     }*/
}