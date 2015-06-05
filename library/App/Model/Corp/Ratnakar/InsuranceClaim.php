<?php
/**
 * Model that manages the products
 *
 * @package Operation_Models
 * @copyright transerv
 */

class Corp_Ratnakar_InsuranceClaim extends Corp_Ratnakar
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
    protected $_name = DbTable::TABLE_RAT_CORP_INSURANCE_CLAIM;
    
    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    protected $_rowClass = 'App_Table_Rat_Corp_Insurance_Claim';
    
    protected $_txnCode;
     
    /*
     * initiates Medi Assist CardLoad txns
     * params:
     * amount
     * hospital_id_code      
     * terminal_id_code      
     * hospital_mcc      
     * medi_assist_id      
     */
    public function initiateCardLoad($params){
        
        $cardholdersModel = new Corp_Ratnakar_Cardholders();
        $insuranceClaimModel = new Corp_Ratnakar_InsuranceClaim();
        $agentsModel = new Agents();
        $purseModel = new MasterPurse();
        $customerPurseModel = new Corp_Ratnakar_CustomerPurse();
        $cardholderdetail = $cardholdersModel->searchByMediAssistId($params['medi_assist_id']);
        
        // get master purse id and get purse code from product.ini
        $product = App_DI_Definition_BankProduct::getInstance(BANK_RATNAKAR_CORP);
        $purseCode = $product->purse->code->corporateins; 
        $purseDetails = $purseModel->getPurseIdByPurseCode($purseCode); 
      
        $custPurseArr = array('rat_customer_id'=>  $cardholderdetail['id'],
           'purse_master_id' => $purseDetails['id']);

        $custPursedetails = $customerPurseModel->getCustPurseDetails($custPurseArr);
       
        $data['product_id'] = $cardholderdetail['product_id'];
        $data['customer_id'] = $cardholderdetail['customer_master_id'];
        $data['cardholder_id'] = $cardholderdetail['id'];
        $data['customer_purse_id'] = $custPursedetails['id'];
        $data['amount'] = $params['amount'];
        $data['hospital_id_code'] = $params['hospital_id_code'];
//        $data['txn_type'] = TXNTYPE_RAT_CORP_LOAD;
        $data['by_agent_id'] = MEDIASSIST_AGENT_ID;
        $data['ip'] = ''; //$this->formatIpAddress(Util::getIP());
        $data['status'] = STATUS_INCOMPLETE;
        $data['medi_assist_id'] = $params['medi_assist_id'];
        $data['terminal_id_code'] = $params['terminal_id_code'];
        $data['hospital_mcc'] = $params['hospital_mcc'];
        $data['date_created'] = new Zend_Db_Expr('NOW()');
        
        try{
            
        $insuranceClaimId = $this->save($data);
        $txnParams = array(
                        'agent_id' => MEDIASSIST_AGENT_ID,
                        'amount' => $params['amount'],
                        'insurance_claim_id' => $insuranceClaimId, 
                        'customer_master_id' =>  $cardholderdetail['customer_master_id'],
                        'product_id' => $cardholderdetail['product_id'],
                        'purse_master_id' =>  $purseDetails['id'],
                        'customer_purse_id' =>  $custPursedetails['id']
                );
        
        $baseTxn = new BaseTxn();
        //$txnCode = $baseTxn->initiateRatMediAssistCardLoad($txnParams);
        $txnCode = $baseTxn->initiateMediAssistCardLoad($txnParams);
        
        $update = array();
        $update['txn_code'] = $txnCode;
        $update['status'] = STATUS_PENDING;
        $this->setLastTxnCode($txnCode);
       $updateClaim = $this->updateCustomerClaims($update, $insuranceClaimId);
       return $updateClaim;
        }
        catch(App_Exception $e){
                                 App_Logger::log($e->getMessage(), Zend_Log::ERR);
                                 throw new Exception($e->getMessage());

                    } catch(Exception $e){
                                  App_Logger::log($e->getMessage(), Zend_Log::ERR);
                                  throw new Exception($e->getMessage());
                   }
                   
    }
   
    /*
     * getCustomerClaims will return the medi assist customer insurance claim
     */
    public function getCustomerClaims($param){
        $status = isset($param['status'])?$param['status']:'';
        $limit = isset($param['limit'])?$param['limit']:'';
        $crnkey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`rcc`.`card_number`,'".$crnkey."') as card_number");
        $crn = new Zend_Db_Expr("AES_DECRYPT(`rcc`.`crn`,'".$crnkey."') as crn");
        $select =  $this->_db->select() ; 
        $select->from(DbTable::TABLE_RAT_CORP_INSURANCE_CLAIM.' as rcic',array('rcic.id', 'rcic.cardholder_id', 'rcic.amount', 'rcic.txn_code', 'rcic.by_agent_id', 'rcic.date_created', 'rcic.date_created', 'rcic.by_agent_id'));
        $select->joinLeft(DbTable::TABLE_RAT_CORP_CARDHOLDER." as rcc", "rcic.cardholder_id=rcc.id", array($crn, $card_number));
        if($status!='')
           $select->where('rcic.status = ?', $status);
        if($limit!='')
           $select->limit($limit); 
        return $this->_db->fetchAll($select);
    }
   
    
    /*
     * getCustomerClaims will return the medi assist customer insurance claim
     */
    public function getCardLoad(){
        
        $custClaims = $this->getCustomerClaims(array('limit'=>MEDIASSIST_CUSTOMER_LOAD_LIMIT, 'status'=>STATUS_PENDING));
        
        $count = count($custClaims);
        $retResp = array('loaded'=> 0, 'not_loaded'=> 0, 'exception'=>array());
        if($count>0){            
           $ecsApi = new App_Socket_ECS_Corp_Transaction();
         
                foreach($custClaims as $key=>$val){  
                    
                    $cardLoadData = array(
                                           'amount'=>$val['amount'],
                                           'crn'=>$val['crn'],
                                           'agentId'=>$val['by_agent_id'],
                                           'transactionId'=> $val['txn_code'],
                                           'currencyCode' => CURRENCY_INR_CODE,
                                           'countryCode'  => COUNTRY_IN_CODE
                                         );
                    
                  try{
                      
                        $apiResp =  $ecsApi->cardLoad($cardLoadData);
                        $baseParams = array(
                             'insurance_claim_id' =>  $val['id'],
                             'txn_code' => $val['txn_code'],
                             'amount' => $val['amount'],
                             'customer_purse_id' => $val['customer_purse_id']);
                        if($apiResp){
                           $this->baseTxnECSCardLoad($baseParams, STATUS_SUCCESS);
         
                           
                           $retResp['loaded'] = $retResp['loaded'] + 1;
                           $updInsuranceClaim = $this->updateCustomerClaims(array('status'=>STATUS_LOADED), $val['id']);
                        }
                        else{
                           $retResp['not_loaded'] = $retResp['not_loaded'] + 1;
                           $this->baseTxnECSCardLoad($baseParams, STATUS_FAILURE);
                            $updInsuranceClaim = $this->updateCustomerClaims(array('status'=>STATUS_FAILED), $val['id']);
                        }
                        
                    } catch(App_Exception $e){
                                  $retResp['not_loaded'] = $retResp['not_loaded'] + 1;
                                  $errorMsg = $e->getMessage();
                                  $countException = count($retResp['exception']);
                                  $retResp['exception'][$countException] = 'Exception of CRN '.$val['crn'].' with txn id '.$val['txn_code'].' is '. $errorMsg;
                    } catch(Exception $e){
                                  $retResp['not_loaded'] = $retResp['not_loaded'] + 1;
                                  $errorMsg = $e->getMessage();
                                  $countException = count($retResp['exception']);
                                  $retResp['exception'][$countException] = 'Exception of CRN '.$val['crn'].' with txn id '.$val['txn_code'].' is '. $errorMsg;
                   }
                   

                }
           
        }
        
        return $retResp;
    }
    
    
    
    /*
     * updateCustomerClaims will update
     */
    public function updateCustomerClaims($data, $id){
        if(!empty($data) && $id>0){
           $this->_db->update(DbTable::TABLE_RAT_CORP_INSURANCE_CLAIM, $data, 'id="'.$id.'"');
           return true;
        }
        return false;
    }
   
    
    /*
     * validateECSInsuranceClaim will validate the insurance claim txn , inputs coming from ECS API
     * As Params:- card number, amount, hospital code id, terminal code id
     */
//    public function validateECSInsuranceClaim($cardNumber, $amount, $hid = 0, $tid = 0){
    //Removing HID As its not part of transction, Add MCC
    public function validateECSInsuranceClaim($cardNumber, $amount, $mcc = '', $tid = 0, $rrNo){
        if($cardNumber != '' && $amount != ''){
            
              // verify card number existence
              $cardNumberInfo = $this->getInsuranceClaimInfo(array('crn'=>$cardNumber));
              if(count($cardNumberInfo)==0)
                  return INSURANCE_CLAIM_CARDNUMBER_NOT_MATCH;
              
              $id = $cardNumberInfo['id']; //insurance claim id
              
              // verify amount existence
              if($amount!='')
                 $amountInfo = $this->getInsuranceClaimInfo(array('amount'=>$amount));
                 if(count($amountInfo)==0)
                    return INSURANCE_CLAIM_AMOUNT_NOT_MATCH;
              
              // verify hid existence
              /*   
              if($hid != 0)
                 $hidInfo = $this->getInsuranceClaimInfo(array('hospital_id_code'=>$hid));
                 if(count($hidInfo)==0)
                    return INSURANCE_CLAIM_HID_NOT_MATCH;
              */
              // verify tid existence
              if($tid != 0)
                 $tidInfo = $this->getInsuranceClaimInfo(array('hospital_id_code'=>$hid, 'terminal_id_code'=>$tid));
                 if(count($tidInfo)==0){
                    return INSURANCE_CLAIM_TID_NOT_MATCH;
                 }
                 else{
                $baseTxn = new BaseTxn();
                  // get master purse id and get purse code from product.ini
                $product = App_DI_Definition_BankProduct::getInstance(BANK_RATNAKAR_CORP);
                $purseCode = $product->purse->code->corporateins; 
                $purseDetails = $purseModel->getPurseIdByPurseCode($purseCode); 
                $params = array(
                'insurance_claim_id' => $id,
                'customer_master_id' => $cardNumberInfo['customer_master_id'],
                'product_id' =>  $cardNumberInfo['product_id'],
                'amount' => $amount , 
                'purse_master_id' => $purseDetails['id'],
                'customer_purse_id' => $cardNumberInfo['customer_purse_id'] );
                
                  $response = $baseTxn->initiateRatMediAssistAuthRequest($params);
                    if($response){
                         $updStatus = $this->updateCustomerClaims(array('status'=>STATUS_BLOCKED, 'rr_no'=>$rrNo), $id);
                         return STATUS_SUCCESS;
                    }
                 }
                 
              
        } else {
            throw new Exception('Card number or amount missing');
        }
    }
    
    
    
    /*
     * getCustomerClaims will return the medi assist customer insurance claim
     */
    public function getInsuranceClaimInfo($param){
        $cardNumber = isset($param['crn'])?$param['crn']:'';
        $amount = isset($param['amount'])?$param['amount']:'';
        $hospitalIdCode = isset($param['hospital_id_code'])?$param['hospital_id_code']:'';
        $terminalIdCode = isset($param['terminal_id_code'])?$param['terminal_id_code']:'';
        $rrNo = isset($param['rr_no'])?$param['rr_no']:'';
        $hospitalMcc = isset($param['hospital_mcc'])?$param['hospital_mcc']:'';
        $joinWhere = "rcic.cardholder_id = rcc.id AND rcc.crn='".$cardNumber."'"; 
        
        
        $select =  $this->_db->select() ; 
        $select->from(DbTable::TABLE_RAT_CORP_INSURANCE_CLAIM.' as rcic',array('rcic.id', 'rcic.amount', 'rcic.hospital_id_code', 'rcic.terminal_id_code', 'rcic.cardholder_id','rcic.customer_purse_id','customer_purse_id'));
        $select->join(DbTable::TABLE_RAT_CORP_CARDHOLDER." as rcc", $joinWhere, array('afn','customer_master_id','product_id'));
        if($hospitalIdCode > 0)                                                                                                                         
           $select->where('rcic.hospital_id_code = ?', $hospitalIdCode);
        if($terminalIdCode > 0)
           $select->where('rcic.terminal_id_code = ?', $terminalIdCode);
        if($amount > 0)
           $select->where('rcic.amount = ?', $amount);
        if($rrNo !='')
           $select->where('rcic.rr_no= ?', $rrNo);
        if($hospitalMcc !='')
           $select->where('rcic.hospital_mcc= ?', $hospitalMcc);
        return $this->_db->fetchRow($select);
       
    }
    
    /*
     * clearInsuranceClaims will revert back the credited claimed amount from cardholder acnt, if amount not used within time duration 
     */
    public function clearInsuranceClaims(){
        
        $custClaims = $this->getCustomerClaims(array('status'=>STATUS_LOADED));
        $count = count($custClaims);
        $retResp = array('cutoff'=> 0, 'not_cutoff'=> 0, 'exception'=>array());
        
        if($count>0){            
           $ecsApi = new App_Socket_ECS_Corp_Transaction();
           $objBaseTxn = new BaseTxn();
         
                foreach($custClaims as $key=>$val){
                    
                    // check for time duration 
                    $dateCreate = $val['date_created'];
                    $cuDate = date('Y-m-d H:i:s');
                    $timeDiffrence = Util::dateDiff($dateCreate, $cuDate); // difference in secs
                    $timeDiffrence = $timeDiffrence / 60; // difference in mins
                    
                    if($timeDiffrence > INSURANCE_CLAIM_AMOUNT_ALLOWED_TIME) {

                      try{

                            $apiResp =  $ecsApi->reversalMACardLoad($val['txn_code'], $val['card_number'], $val['amount'], $val['date_transaction']);
                            if($apiResp){
                                $paramsBaseTxn = array(
                                                        'insurance_claim_id' => $val['id'],
                                                        'txn_code' => $val['txn_code'],
                                                        'amount' => $val['amount'],
                                                        'agent_id' => $val['by_agent_id']
                                                      );
                                
                                $respBaseTxn = $objBaseTxn->cutoffRatMediAssistCardLoad($paramsBaseTxn);
                                
                                if($respBaseTxn){
                                    $retResp['cutoff'] = $retResp['cutoff'] + 1;
                                    $updInsuranceClaim = $this->updateCustomerClaims(array('status'=>STATUS_CUTOFF), $val['id']);
                                }
                            }
                            
                           if(!$apiResp || !$respBaseTxn){
                               $retResp['not_cutoff'] = $retResp['not_cutoff'] + 1;
                            }

                        } catch(App_Exception $e){
                                      $retResp['not_cutoff'] = $retResp['not_cutoff'] + 1;
                                      $errorMsg = $e->getMessage();
                                      $countException = count($retResp['exception']);
                                      $retResp['exception'][$countException] = 'Exception of cardholder id '.$val['cardholder_id'].' with txn id '.$val['txn_code'].' is '. $errorMsg;
                        } catch(Exception $e){
                                      $retResp['not_cutoff'] = $retResp['not_cutoff'] + 1;
                                      $errorMsg = $e->getMessage();
                                      $countException = count($retResp['exception']);
                                      $retResp['exception'][$countException] = 'Exception of cardholder id '.$val['cardholder_id'].' with txn id '.$val['txn_code'].' is '. $errorMsg;
                       }
                     }
                }
           
        }
        
        return $retResp;
    }
    
    
    
    /*
     * completeECSInsuranceClaim will update customer insurance claim to complete
     * as params :- card number, amount, hospital mcc, terminal id, rr no
     */
    public function completeECSInsuranceClaim($cardNumber, $amount, $mcc = '', $tid = 0, $rrNo = ''){
        if($cardNumber!='' && $amount>0 && $rrNo!=''){
            $baseTxn = new BaseTxn();
            $params = array(
                             'crn'=>$cardNumber,
                             'amount'=>$amount,
                             'rr_no'=>$rrNo,
                             'hospital_mcc'=>$mcc,
                             'terminal_id'=>$tid,
                           );
            
            $claimInfo = $this->getInsuranceClaimInfo($params);
           
            if(count($claimInfo)==0)
               return COMPLETE_ECS_CLAIM_NOT_EXISTED;     
           
           $where = 'rr_no="'.$rrNo.'" AND amount="'.$amount.'" AND status="'.STATUS_BLOCKED.'"';
           $data = array('status'=>STATUS_COMPLETED);
           
           $baseParams = array();
           $baseParams['insurance_claim_id'] = $claimInfo['id'];
           $baseParams['amount'] = $amount;
           $baseParams['customer_purse_id'] = $claimInfo['customer_purse_id'];
          
           $baseTxn->successRatMediAssistAuthAdvice($baseParams);
           $updResp = $this->_db->update(DbTable::TABLE_RAT_CORP_INSURANCE_CLAIM, $data, $where);
           if($updResp==1)
              return STATUS_SUCCESS;   
           else 
              return STATUS_FAILED;   
        } else 
            return COMPLETE_ECS_CLAIM_INSUFFICIENT_DATA;
    }
    
    
    /*
     * revertECSInsuranceClaim will update customer insurance claim to loaded status
     * as params :- card number, amount, hospital mcc, terminal id, rr no
     */
    public function revertECSInsuranceClaim($cardNumber, $amount, $mcc = '', $tid = 0, $rrNo = ''){
        if($cardNumber!='' && $amount>0 && $rrNo!=''){
            $baseTxn = new BaseTxn();
            $params = array(
                             'crn'=>$cardNumber,
                             'amount'=>$amount,
                             'rr_no'=>$rrNo,
                             'hospital_mcc'=>$mcc,
                             'terminal_id'=>$tid,
                           );
            
            $claimInfo = $this->getInsuranceClaimInfo($params);
            if(count($claimInfo)==0)
               return COMPLETE_ECS_CLAIM_NOT_EXISTED;     
           
           $where = 'rr_no="'.$rrNo.'" AND amount="'.$amount.'" AND status="'.STATUS_BLOCKED.'"';
           
           $data = array('status'=>STATUS_LOADED);
           
           $baseParams = array();
           $baseParams['insurance_claim_id'] = $claimInfo['id'];
           $baseParams['amount'] = $amount;
           $baseTxn->failureRatMediAssistAuthAdvice($baseParams);
           $updResp = $this->_db->update(DbTable::TABLE_RAT_CORP_INSURANCE_CLAIM, $data, $where);
           if($updResp==1)
              return STATUS_SUCCESS;   
           else 
              return STATUS_FAILED;   
        } else 
            return COMPLETE_ECS_CLAIM_INSUFFICIENT_DATA;
    }
    
    
     /*
     * baseTxnECSCardLoad will call the base function on the basis of the status passed
     */
    public function baseTxnECSCardLoad($params, $status){
       $baseTxn = new BaseTxn();
       if($status == STATUS_SUCCESS){
           $baseTxn->successRatMediAssistCardLoad($params);
       }
       else if($status == STATUS_FAILURE){
           unset($params['customer_purse_id']);
           $baseTxn->failureRatMediAssistCardLoad($params); 
       }
       }
    
    
    public function getLastTxnCode() {
        return $this->_txnCode;
    }
    
    private function setLastTxnCode($txnCode) {
        $this->_txnCode = $txnCode;
    }
}