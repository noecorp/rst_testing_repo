<?php

class Mvc_Ratnakar_CardholderFund extends Mvc_Axis
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
         
        $objCh = new Mvc_Ratnakar_CardholderUser();
        $returnVal = FALSE;
        $chInfo = $objCh->getCardHolderInfoApproved($param['cardholder_id'], '', '', $param['product_id']);
        $baseTxn = new BaseTxn();
        
        $this->_db->beginTransaction();
        if(empty($chInfo)) { 
            $this->setError('Cardholder not found');
            return FALSE;
        }

        try{

            $isSufficientBal = $baseTxn->chkAllowReLoad(array('agent_id'=> $param['agent_id'], 'product_id' => $param['product_id'], 'amount' => $param['amount']));
            
            if($isSufficientBal) {

                // validating the agent n cardholder details
                $data = array('agent_id'=> $param['agent_id'], 
                            'product_id'=> $param['product_id'],
                            'bank_id' => $chInfo['bank_id'],
                            'cardholder_id'=> $chInfo['id'],
                            'amount'=> $param['amount'],
                            'customer_master_id'=>$chInfo['customer_master_id'],
                            'customer_purse_id' => $chInfo['customer_purse_id'],
                            'txn_type'=>TXNTYPE_CARD_RELOAD,
                            'txn_status' => FLAG_PENDING,
                            'remarks' =>''
                         );

            $flag = $baseTxn->chkAllowRatMvcCardLoad(array('load_request_id'=> '1','customer_master_id'=>$chInfo['customer_master_id'],'purse_master_id'=>$chInfo['purse_master_id'],'customer_purse_id'=>$chInfo['customer_purse_id'],'bank_id'=>$chInfo['bank_id'],'amount'=>$param['amount']));
            
            if($flag){
                $iniResp = $baseTxn->initiateAgentToRblMvcCardholder($data); // initiate agent to cardholder txn
                if(isset($iniResp['flag']) && $iniResp['flag']){
                    $data['txn_code'] = $iniResp['txnCode'];
                    $data['bank_id'] = $chInfo['bank_id'];
                    $data['mode'] = TXN_MODE_CR;
                    $data['txn_no'] = $param['txn_no'];
                    $data['narration'] = $param['narration'];
                    $data['load_channel'] = BY_API;
                    $objRatCardload = new Corp_Ratnakar_Cardload();
                    $objRatCardload->doRblMvcCardLoad($data);
                    
                    // sending details to ecs
                    $this->setTxnCode($iniResp['txnCode']);  

                    $paramECS = array(
                        'amount'    => $param['amount'],
                        'crn'       => $chInfo['crn'], 
                        'agentId'   => $param['agent_id'],
                        'transactionId'=> $iniResp['txnCode'],
                        'currencyCode' => $param['currency'],
                        'countryCode'  => COUNTRY_IN_CODE                                            
                    );

                    $ecsApi = new App_Socket_ECS_Transaction();
                    //$ecsResp = $ecsApi->cardLoad($paramECS);

                    $debug_enable = App_DI_Container::get('ConfigObject')->system->enable_debug;
                    
                    /******** deciding error message *******/
                    if($debug_enable == TRUE){
                        // $data
                        $data['txn_code'] = $iniResp['txnCode'];
                        $txnMsg = 'Cardholder loaded amount '.$param['amount'].' successfully';
                        $ecsStatus = FLAG_SUCCESS;
                        //$txn_load_id = $ecsApi->getISOTxnId();
                        $returnVal = TRUE;
                    } else {
                        $ecsStatus = FLAG_FAILURE;
                        if($ecsApi->getError()!=''){
                            $txnMsg = 'Cardholder fund load transaction failed with amount '.$param['amount'].' as '.$ecsApi->getError();
                        } else {
                             $txnMsg = 'Cardholder fund load transaction failed with amount '.$param['amount'];
                        } 
                        $txn_load_id = 0;
                    }
                    /******** deciding error message over *******/

                    // completing the txn of fund relaod to CH
                    $completeData = array(  'agent_id'=> $param['agent_id'],
                                            'cardholder_id'=> $chInfo['id'],
                                            'amount'=> $param['amount'],
                                            'txn_code'=> $iniResp['txnCode'],
                                            'txn_load_id'=>$txn_load_id,
                                            'txn_status'=> $ecsStatus,
                                            'remarks'=> $txnMsg,
                                            'customer_master_id' => $chInfo['customer_master_id'],
                                            'customer_purse_id' => $chInfo['customer_purse_id'],
                                         );

                    try{
                        $baseTxn->completeTxnAgentToRblCardholder($completeData);
                    }catch (Exception $e ) {
                        App_Logger::log(serialize($e) , Zend_Log::ERR);
                        $this->setError($e->getMessage());
                        throw new Exception($e->getMessage());
                    }
                }
            }
                $this->_db->commit();
                return $returnVal;
            } else {
                return FALSE;  
            }
        } catch (App_Api_Exception $e ) {
            $this->setError($e->getMessage());
            $this->_db->rollBack();            
            throw new Exception($e->getMessage());
        } catch (Exception $e ) {
            $this->setError($e->getMessage());
            $this->_db->rollBack();            
            throw new Exception($e->getMessage());
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