<?php
/**
 * Validator
 * This will be responsible for handling balance Validation for agent and cardholder
 * 
 * @package Core
 * @copyright Transerv
 * @author Vikram Singh <Vikram@transerv.co.in>
 */
class Validator_CardholderBalance extends Validator
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
    
    
    public $_msg;
    
 
    public function validateCardholderBalance($id, $load = '0.0', $type='cr')
    {
    
        try {
            
            if(!$this->validateMaximumLoad($id, $load, $type)) {
                //Log The Error
                throw new Exception("Total amount is exceeding allowed limit");
            }
            
            if(!$this->validateMinimumLoad($id, $load, $type)) {
                //Log The Error                
                throw new Exception("Loading amount is below permissible limit");                
            }
            
            return true;

        } catch (Exception $e) {
            $this->setMessage($e->getMessage());
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            return false;

        }
        
    }
    
    private function validateMaximumLoad($id, $load='0.0', $type='cr')
    {
        $agentBalanceModel = new AgentBalance();
        $currentBalance =  $agentBalanceModel->getAgentActiveBalance($id);
        if($type == CR){
          $totalAfterLoad = Validator_AgentBalance::sumAmount($currentBalance, $load);
          $maximumLimit = Validator_AgentBalance::getMaximumAllowedLimit($id);
          if( $totalAfterLoad > $maximumLimit ) {
              //Log
              return false;
          }
         } elseif($type == DR) {
             //throw new Exception ("Validation Maximum Load: Type" . DR . " Not defined.");
         } else {
             throw new Exception ("Validation Maximum Load: Type " . $type ." not found.");
         }
        
         return true;
    }
    
    
    private function validateMinimumLoad($id, $unload='0.0', $type='dr')
    {
        
        $agentBalanceModel = new AgentBalance();
        $currentBalance =  $agentBalanceModel->getAgentActiveBalance($id);
        //exit('<br />' . $currentBalance . ' -:- '. $unload . ' -:- ' . $type . '<br />');
        if($type == DR){
          $totalAfterUnload = Validator_AgentBalance::deductAmount($currentBalance, $unload);
          
          //$maximumLimit = Validator_AgentBalance::getMaximumAllowedLimit($id);
          if( $totalAfterUnload < 0 ) {
              //Log
             return false;
          }
         } elseif($type == CR) {
             //throw new Exception ("Validation Maximum Load: Type" . DR . " Not defined.");
         } else {
             throw new Exception ("Validation Minimum Load: Type " . $type ." not found.");
         }

        return true;
    }
    
    
    public function setMessage($msg)
    {
        $this->_msg = $msg;
    }
    

    public function getMessage()
    {
        return $this->_msg;
    }
    

}