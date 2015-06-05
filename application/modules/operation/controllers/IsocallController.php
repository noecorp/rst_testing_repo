 <?php
/**
 * Allow the admins to manage critical info, users, groups, permissions, etc.
 *
 * @category backoffice
 * @package backoffice_controllers
 * @copyright company
 */

class IsocallController extends App_Operation_Controller
{
    /**
     * Overrides Zend_Controller_Action::init()
     *
     * @access public
     * @return void
     */
    
    private $session;
    
    public function init(){
        // init the parent
        parent::init();
        
        $this->session = new Zend_Session_Namespace("App.Operation.Controller");
    }
    
    /**
     * Controller's entry point
     *
     * @access public
     * @return void
     */
    public function indexAction(){
        
    }
    
    /**
     * Theme example page
     *
     * @access publi
     * @return void
     */
    public function cardloadAction(){
        
        $ecsApi = new App_Socket_ECS_Transaction();
        //$ecsApi->generateCardLoadReversalISO($transISO, $param);
        try {
            
           $crn = $this->_getParam('crn', '');
           if($crn !='') {
                $param['amount'] = $this->_getParam('amount');
                $this->view->amount = $this->_getParam('amount');
                $param['stan'] = rand(1111,9999);
                //$param['agentId'] = $this->_getParam('agent_id');
                $this->view->agentId = $this->_getParam('agent_id');
                $this->view->transactionId = $this->_getParam('transaction_id');
                $param['crn'] = $crn;
                $this->view->crn = $crn;

                $param['transactionId']  = $this->_getParam('transaction_id');

                //$agentId = isset($param['agentId']) ? $param['agentId'] : substr($param['agentId'],-8);                
 
                $resp =  $ecsApi->cardLoad($param);
                $this->view->iso = $ecsApi->getRequestISO();
               // print $this->view->iso;exit;
                if(!$resp) {
                    $this->view->Error = $ecsApi->getError();
                } else {
                    $this->view->response = $ecsApi->getResponse();
                }
                
                
                //exit;
            
          
               
           }
        } catch (Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            print $e->getMessage();
        }
        //exit;
    }
    /**
     * Theme example page
     *
     * @access publi
     * @return void
     */
    public function cardloadreversalAction(){
         //$this->_helper->layout->disableLayout();
         
        //$this->_helper->viewRenderer->setNoRender(true);
        $ecsApi = new App_Socket_ECS_Transaction();
        //$ecsApi->generateCardLoadReversalISO($transISO, $param);
        try {
            
           $fullISO = $this->_getParam('fulliso', '');
           if($fullISO !='') {
                $this->view->fulliso = $this->_getParam('fulliso');
                $param['iso'] = $fullISO;
                

                
    //print 'Response : ' . $this->response . '<br />';
    $iso8583 = new App_ISO_ISO8583();
    $iso8583->addISOwithHeader($fullISO); 
    $dataArray = $iso8583->getData();
    //$amount = intval($dataArray['4']) /100;
    $amount = intval($dataArray['4']);
    $param = array(
        'crn'   => $dataArray['2'],
        'amount' => $amount,
        'transactionTime'   => date('mdHis')
    );
    //print '**' . $ecsApi->generateCardLoadReversalISO($fullISO, $param) . '**';
    $resp = $ecsApi->cardLoadReversalTransaction($fullISO, $param);
    
    if(!$resp) {
        $this->view->Error = $ecsApi->getError();
    } else {
        $this->view->response = $ecsApi->getResponse();
    }
                
                
                //exit;
            
          
               
           }
        } catch (Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            print $e->getMessage();
            //print_r($e);exit;
        }
        //print 'here';exit;
        //exit;
    }
       
    
}