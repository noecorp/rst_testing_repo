<?php
/**
 * ECS that manages the ECS releated stuff for defining
 * the ECS method in the application
 *
 * @package Core
 * @copyright transerv
 */

class ECS extends App_Model
{

    /**
     * Returns an array with all MVC Types
     * 
     * 
     * @access public static
     * @return array
     */
    public function assignCRN($cardHolderId = 0){
        if(!is_numeric($cardHolderId) || !$cardHolderId > 0) {
            throw new Exception("Invalid Cardholder id");
        }
        $banks = new Banks();
        $product = new Products();
        $cardHolder = new Mvc_Axis_CardholderUser();
        //Is Valid CardHolder Id
        $row = $cardHolder->findById($cardHolderId);
      
        if (empty($row)) {
            throw new Exception("Cardholder not found!!");
        }
        
        try {
            $this->_db->beginTransaction();
            //Start Transaction
            //Get CRN
            
            $cardholderDetails = $cardHolder->getCardholderProducts($cardHolderId);
            //echo '<pre>';print_r($cardholderDetails);

            $productArr = $product->findById($cardholderDetails['product_id']);
            $bankArr = $banks->findById($productArr['bank_id']);

            $crn = new Unicode();
            $crn->_PROGRAM_TYPE = $productArr['program_type'];//PROGRAM_TYPE_MVC / PROGRAM_TYPE_REMIT;
            $crn->_PRODUCT_UNICODE = $productArr['unicode'] ;
            $crn->_BANK_UNICODE = $bankArr['unicode'];
            

            //echo "<pre>";print_r($crn);exit;

            if($crn->generateUnicode()) {
            
            $crnArr =Util::toArray($crn->getUnicodeInfo());
             //Assign CRN
            $updateArr = array(
                'crn' => $crnArr['crn'],
                'unicode' => $crnArr['unicode']
            );
            //echo '<pre>';print_r($updateArr);exit;
            $cardHolder->update($updateArr, "id = $cardHolderId");

            $this->_db->update(DbTable::TABLE_CARDHOLDER_DETAILS, $updateArr, 'cardholder_id="'.$cardHolderId.'" AND status="active"'); 

            //Block/Set Used CRN            
            $crn->setUsedStatus();
            
            } 
             //End Transaction
               $this->_db->commit();
               //return true;
            } catch (Exception $e) {

            //print $e->getMessage();exit('hhhhh');
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_db->rollBack();
            
           }
        
    }
    
    public function getNewCRN()
    {
        //return array('crn' => '2222220000000010');
        $select = $this->_db->select()
                ->from("t_ecs_crn")
                ->where("status=?","free")
                ->limit(1);
        $rs = $this->_db->fetchRow($select);
        
        if(empty($rs)) {
            throw new Exception("No CRN Registered with System");
            //Send Alert to Sysadmin
        }
        return $rs;
        
        
    }
    
    
    public function blockCRN($crn)
    {
        $data = array(
            'status' => 'block'
        );
        $this->_db->update("t_ecs_crn", $data," crn = '".$crn."'");
        
    }
    
    
    public function getECSCount(){
         $select = $this->_db->select()
                    ->from(DbTable::TABLE_ECS_CRN,array('count(*) as free_crns'))
                    ->where("status=?",STATUS_FREE)
                    ->limit(1);
        $rs = $this->_db->fetchRow($select);
        return $rs['free_crns'];
    }
    
    public function sendLowCRNAlert(){
      $crnCount  = $this->getECSCount();
      
      $countRequired = App_DI_Container::get('ConfigObject')->cron->crn->count_required;
     
      if($crnCount<$countRequired){
         $m = new App\Messaging\MVC\Axis\Operation();
          $param['current_crn_count']=$crnCount;
          $param['crn_count_required']=$countRequired;  
          
          try{  
              $m->lowCrnAlert($param);              
          } catch(Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            throw new Exception($e->getMessage());
            return false;
        }

      }
      
      return $crnCount;
      
    }
    
    
    /**
     * Returns an array with all MVC Types
     * 
     * @access public static
     * @return array
     */
    public function addCRN($crns) {
        if (empty($crns)) {
            throw new Exception("CRN not found to add");
        }

        try {
            //$this->_db->beginTransaction();
            //Start Transaction
            $crnData = array(
                'crn'           => $crns['crn'],
                'status'        => STATUS_FREE,
                'relation'      => $crns['relation'],
                'product'       => $crns['product'],
                'promotion'     => $crns['promotion'],
                'branch'        => $crns['branch'],
                'statement_plan'=> $crns['statement_plan'],
                'transaction_plan'=> $crns['transaction_plan'],
                'embossed_line3'=> $crns['embossed_line3'],
                'embossed_line4'=> $crns['embossed_line4'],
                'other'         => $crns['extra']
            );
            $this->insert($crnData);
            // End Transaction
            //$this->_db->commit();
        } catch (Exception $e) {
             throw new Exception($e->getMessage());
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            //$this->_db->rollBack();
        }
        return true;
    }
    
  /*
   * Assign Rat Corp CRN

   */
      public function assignRatCorpCRN($cardHolderId = 0){
          
        if(!is_numeric($cardHolderId) || !$cardHolderId > 0) {
            throw new Exception("Invalid Cardholder id");
        }
        $banks = new Banks();
        $product = new Products();
        $cardHolder = new Corp_Ratnakar_Cardholders();
        //Is Valid CardHolder Id
        $row = $cardHolder->findById($cardHolderId);
        //echo $cardHolderId.'**';exit;
        //echo '<per>';print_r($row->toArray());exit;
        if (empty($row)) {
            throw new Exception("Cardholder not found!!");
        }
        
        try {
            $this->_db->beginTransaction();
            //Start Transaction
            //Get CRN
            
            //$cardholderDetails = $cardHolder->getRatCardholderProductsAndBank($cardHolderId);
            $cardholderDetails = $cardHolder->getRatCardholderInfoProductsAndBank($cardHolderId);

            $crn = new Unicode();
            $crn->_PROGRAM_TYPE = $cardholderDetails['program_type'];//PROGRAM_TYPE_CORP;
            $crn->_PRODUCT_UNICODE = $cardholderDetails['unicode'] ;
            $crn->_BANK_UNICODE = $cardholderDetails['bank_unicode'] ;
           
             

            if($crn->generateUnicode()) {
            $crnArr =Util::toArray($crn->getUnicodeInfo());
             //Assign CRN
            $updateArr = array(
                'crn' => $crnArr['crn'],
                'unicode' => $crnArr['unicode']
            );
            
            $this->_db->update(DbTable::TABLE_RAT_CORP_CARDHOLDER, $updateArr, 'id="'.$cardHolderId.'"'); 

            //Block/Set Used CRN            
            $crn->setUsedStatus();
                //exit('CRN Updated Successfully');
            } else {
                //exit('Unable to Generate CRN');
            }
                //exit('END');
             //End Transaction
               $this->_db->commit();
               //return true;
            } catch (Exception $e) {
               // echo $e->getMessage();exit('END');
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_db->rollBack();
            
           }
        
    }
    
    
      public function assignMediassistCRN($cardHolderId = 0){
          
        if(!is_numeric($cardHolderId) || !$cardHolderId > 0) {
            throw new Exception("Invalid Cardholder id");
        }
        $banks = new Banks();
        $product = new Products();
        $cardHolder = new Corp_Ratnakar_Cardholders();
        //Is Valid CardHolder Id
        $row = $cardHolder->findById($cardHolderId);
        $crnMaster = new CRNMaster();
        if (empty($row)) {
            throw new Exception("Cardholder not found!!");
        }
        
        try {
            $this->_db->beginTransaction();
            //Start Transaction
            //Get CRN
            
            //$cardholderDetails = $cardHolder->getRatCardholderProductsAndBank($cardHolderId);
            $cardholderDetails = $cardHolder->getRatCardholderInfoProductsAndBank($cardHolderId);
            
     //echo '<per>';print_r($cardholderDetails);exit;
            $crn = new Unicode();
            $crn->_PROGRAM_TYPE = $cardholderDetails['program_type'];//PROGRAM_TYPE_CORP;
            $crn->_PRODUCT_UNICODE = $cardholderDetails['unicode'] ;
            $crn->_BANK_UNICODE = $cardholderDetails['bank_unicode'] ;
           
             

            if($crn->generateUnicode()) {
                
            $crnArr =Util::toArray($crn->getUnicodeInfo());
            //echo "<pre>";print_r();
             //Assign CRN
            $updateArr = array(
                'crn' => $crnArr['crn'],
                'unicode' => $crnArr['unicode']
            );
            //echo "<pre>";print_r($crnArr);exit;
            $this->_db->update(DbTable::TABLE_RAT_CORP_CARDHOLDER, $updateArr, 'id="'.$cardHolderId.'"'); 

            //Block/Set Used CRN            
            $crn->setUsedStatus();
                //exit('CRN Updated Successfully');
            }
                //exit('END');
             //End Transaction
               $this->_db->commit();
               //return true;
            } catch (Exception $e) {
               // echo $e->getMessage();exit('END');
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_db->rollBack();
            
           }
        
    }
   
}