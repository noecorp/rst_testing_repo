<?php

class Mvc_Axis_CardholderFund extends Mvc_Axis
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
    protected $_name = DbTable::TABLE_CARDHOLDERS;
    
    
    protected $_txnCode;
    
    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    protected $_rowClass = 'App_Table_CardholderUser';
    
    
    public function updateReloadTxn($param){        
      if($param['agent_id']!='' || $param['product_id']!='' || $param['cardholder_id']!='' || $param['amount']!=''){
          throw new Exception('Insufficient data for updating transaction'); exit;
      }
          
      $objBaseTxn = new BaseTxn();
      
      try{
            //$resp = $objBaseTxn->updateReloadTxn($param);
      } catch (Exception $e ) {  App_Logger::log($e->getMessage(), Zend_Log::ERR);return $e->getMessage();exit; }
      
        return true;
    }      
    
    public function chkAllowReLoad($param){
        $objBTxn = new BaseTxn();
        try{
            $resp = $objBTxn->chkAllowReLoad($param);
        }catch (Exception $e ) {
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                return $e->getMessage();exit;
        }
        return true;
    }
      
    public function loadFromCustomerAPI($param) {
         
            $apisettingModel = new APISettings();
            $ecsResp = '';
            $m = new App\Messaging\MVC\Axis\Agent();
            $objCh = new Mvc_Axis_CardholderUser();
            $objChf = new Mvc_Axis_CardholderFund();
            $objAgBal = new AgentBalance();
            $returnVal = FALSE;
            $chInfo = $objCh->getCardholderDetailsByCRN($param['crn']);
            
            $this->_db->beginTransaction();
          if(empty($chInfo)) { 
              $this->setError('Cardholder not found');
              return FALSE;
          }

                    try{
                    
                        $isSufficientBal = $objAgBal->chkAllowReLoad(array('agent_id'=> $param['agent_id'], 'product_id'=> $param['product_id'], 'amount' => $param['amount']));
                        if($isSufficientBal) {
                           
                            // validating the agent n cardholder details
                        
                            $chkData = array('agent_id'=> $param['agent_id'], 'product_id'=> $param['product_id'], 
                                             'amount'=> $param['amount'], 'block_amount'=>true 
                                            );
                            
                            
                            $data = array(  'agent_id'=> $param['agent_id'], 
                                            'product_id'=> $param['product_id'],
                                            'cardholder_id'=> $chInfo->id,
                                            'amount'=> $param['amount'],
                                            'txn_type'=>TXNTYPE_CARD_RELOAD
                                         );
                            
                            $objCHBal = new CardholderBalance();
                            $iniResp = $objCHBal->initiateAgentToCardholder($data); // initiate agent to cardholder txn
                        
                  
                        if(isset($iniResp['flag']) && $iniResp['flag']){
                                    // sending details to ecs
                                  $this->setTxnCode($iniResp['txnCode']);  

                                        $paramECS = array(
                                            'amount'    => $param['amount'],
                                            'crn'       => $param['crn'], 
                                            'agentId'   => $param['agent_id'],
                                            'transactionId'=> $iniResp['txnCode'],
                                            'currencyCode' => $param['currency'],
                                            'countryCode'  => COUNTRY_IN_CODE                                            
                                        );
                                        
                                           $ecsApi = new App_Socket_ECS_Transaction();
                                           $ecsResp = $ecsApi->cardLoad($paramECS);
//                                           $ecsResp = TRUE;
                                    
                                    /******** deciding error message *******/
                                    // getting message for sms 
                                    $mobileEndChars = substr($chInfo->mobile_number, -4); 
                                    if($ecsResp){ 
                                   
                                        // $data
                                        $data['txn_code'] = $iniResp['txnCode'];
                                        $objCHBal->saveCardloads($data);
                                        
                                         $txnMsg = 'Cardholder loaded amount '.$param['amount'].' successfully';
                                         $ecsStatus = FLAG_SUCCESS;
                                         $returnVal = TRUE;
                                    } else {
                                        $objAgent = new AgentBalance();         
                                        $agentBalance = $objAgent->getAgentActiveBalance($param['agent_id']);
                                       
                                        
                                         $ecsStatus = FLAG_FAILURE;
                                         if($ecsApi->getError()!=''){
                                             $txnMsg = 'Cardholder fund load transaction failed with amount '.$param['amount'].' as '.$ecsApi->getError();
                                            }
                                         else {
                                             $txnMsg = 'Cardholder fund load transaction failed with amount '.$param['amount'];
                                         } 
                                       
                                    }
                                    /******** deciding error message over *******/
                                                                
                                    
                                    // completing the txn of fund relaod to CH
                                    $completeData = array(  'agent_id'=> $param['agent_id'],
                                                            'cardholder_id'=> $chInfo->id,
                                                            'amount'=> $param['amount'],
                                                            'txn_code'=> $iniResp['txnCode'],
                                                            'txn_status'=> $ecsStatus,
                                                            'remarks'=> $txnMsg,
                                                         );
                                    
                                    try{
                                        $compResp = $objCHBal->completeAgentToCardholder($completeData);
                                    }catch (Exception $e ) {
                                        App_Logger::log(serialize($e) , Zend_Log::ERR);
                                        $this->setError($e->getMessage());
                                        return FALSE;
                                    } 
                            }  
                            $this->_db->commit();
                            return $returnVal;
                           }
                           else{
                             return FALSE;  
                           }
                           
                    }catch (Exception $e ) {
                            $this->_db->rollBack();
                            App_Logger::log($e->getMessage(), Zend_Log::ERR);
                            return FALSE;
                    }  
                   
 
                   
               
    }
    
    private function setTxnCode($txnCode)
    {
        $this->_txnCode = $txnCode;
    }
    
    public function getTxnCode()
    {
        return $this->_txnCode;
    }
    
    
}