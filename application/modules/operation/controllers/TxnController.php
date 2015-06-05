<?php
/**
 * controller for txns.
 *
 * @category backoffice
 * @package backoffice_controllers
 * @copyright company
 */

class TxnController extends App_Operation_Controller
{
    /**
     * Overrides Zend_Controller_Action::init()
     *
     * @access public
     * @return void
     */
   public function init(){
        // init the parent
        parent::init();
        
        $this->session = new Zend_Session_Namespace("App.Operation.Controller");
    }
    public function indexAction(){
        
         try{
             
            $objAgent = new Agents();
            $objComm = new CommissionReport();
            $curdate = date("Y-m-d");
            $qurData['from'] = $curdate;
            $qurData['to'] = $curdate;
            $agentArr = $objAgent->getAll();
        $totalAgents = $objComm->saveCommission($qurData, $agentArr);
        //$totalRecs = count($agentArr);
        $msg= $totalAgents.' agents commissions have been added in table t_commission_report'; 
             
             exit;
             
        $txnModel = new BaseTxn();
      
        /*$params['agent_id'] = 93;
        $params['product_id'] = 1;
        $params['cardholder_id'] = 175;
        $params['amount'] = 300;
        //$params['txn_type'] = TXNTYPE_FIRST_LOAD;
        $params['txn_type'] = TXNTYPE_CARD_RELOAD;
        echo "<pre>";
        print_r($params);
        echo "</pre>";
        $res = $txnModel->AgentToCardholder($params);*/
         
        
       /* $params['ops_id'] = 1;
        $params['agent_id'] = 43;
        $params['amount'] = 200;
        $params['txn_type'] = TXNTYPE_AGENT_FUND_LOAD;
        //$params['agent_fund_request_id'] = 16; // optional
        echo "<pre>";
        print_r($params);
        echo "</pre>";
        $res = $txnModel->OpsToAgent($params);*/
             
        /*$params['agent_id'] = 93;
        $params['product_id'] = 1;
        echo "<pre>";
        print_r($params);
        echo "</pre>";
        $res = $txnModel->chkCanAssignProduct($params);*/
        
        
        $objAgent = new Agents();
        $objComm = new CommissionReport();
        $curdate = date("Y-m-d");
        $curdate = "2013-02-05";
        $qurData['from'] = $curdate;
        $qurData['to'] = $curdate;
        $agentArr = $objAgent->getAll();
        $totalAgents = $objComm->saveCommission($qurData, $agentArr);

        exit;


        $params['agent_id'] = 93;
        $params['product_id'] = 1;
        $params['cardholder_id'] = 175;
        $params['amount'] = 100;
        $params['txn_type'] = TXNTYPE_FIRST_LOAD;
        //$params['txn_type'] = TXNTYPE_CARD_RELOAD;
        echo "<pre>";
        print_r($params);
        echo "</pre>";
        $res = $txnModel->initiateAgentToCardholder($params);
        
        echo "<br>Result: <pre>";
        print_r($res);
        echo "</pre>";
        
        /* Prints something like this:
         * Array
        (
            [flag] => 1
            [txnCode] => Zend_Db_Expr Object
                (
                    [_expression:protected] => 28788
                )

        )
         */
        
        $params['agent_id'] = 93;
        $params['cardholder_id'] = 175;
        $params['amount'] = 100;
        $params['txn_code'] = $res['txnCode'];
        //$params['txn_status'] = FLAG_SUCCESS;
        $params['txn_status'] = FLAG_FAILURE;
        $params['remarks'] = 'test comments';
        echo "<pre>";
        print_r($params);
        echo "</pre>";
        $res = $txnModel->completeAgentToCardholder($params);
        
//        $objAgentBal = new AgentBalance();
//        $resp = $objAgentBal->updateAgentClosingBalance();
//        echo $resp.' agents balance have been added in table t_agent_closing_balance';
        }
         catch (Exception $e ) {    
                   App_Logger::log($e->getMessage(), Zend_Log::ERR);
                   echo $e->getMessage();
                    
                }  
        
        exit;
    }
    
    
    
}