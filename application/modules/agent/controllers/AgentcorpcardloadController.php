<?php

/**
 * Default entry point in the application
 *
 * @package frontend_controllers
 * @copyright company
 */
class AgentcorpcardloadController extends App_Agent_Controller {

    /**
     * Overrides Zend_Controller_Action::init()
     *
     * @access public
     * @return void
     */
    public function init() {
        // init the parent
        parent::init();
        // initiating the session
        $this->session = new Zend_Session_Namespace("App.Agent.Controller");
    }

    public function indexAction() {
        $this->title = 'My Funding';
        $this->_redirect($this->formatURL('/agentcorpcardload/load/'));
    }

    public function loadAction() {
        $session = new Zend_Session_Namespace('App.Agent.Controller');
        $session->ch_fund_load_auth = '';
        $this->title = 'Cardload';
        $this->view->heading = 'Cardload';
        $user = Zend_Auth::getInstance()->getIdentity(); //get Agent details from session 
        
        //Get The product Id for Only smp 
        $productIds = $user->product_ids;
        $prodConstArr = Util::getArrayBykey($productIds, 'product_const');  
        if(in_array(PRODUCT_CONST_RAT_SMP,$prodConstArr)){ 
            $key = array_search(PRODUCT_CONST_RAT_SMP,$prodConstArr);
            $product_id = $productIds[$key]['product_id']; 
        } else {
            $this->_helper->FlashMessenger(array('msg-error' => 'Product not assigned to the agent.',));
            $this->_redirect($this->formatURL('/profile/index'));
        }
        
        $form = new Mvc_Axis_FundReloadMobileForm(array(
            'action' => $this->formatURL('/agentcorpcardload/load'),
            'method' => 'POST',
            'name' => 'frmFund',
            'id' => 'frmFund'
        ));
        $formData = $this->_request->getPost();
        $btnMob = isset($formData['btn_mob']) ? $formData['btn_mob'] : '';
        if ($btnMob) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $frmData = array(
                    'mobile_number' => $formData['mobile_number'],
                    'product_id' => $product_id
                );
                $chObj = new Corp_Ratnakar_Cardholders();
                $chInfo = $chObj->checkCardholder($frmData);
                if (empty($chInfo)) {
                    $this->_helper->FlashMessenger(array('msg-error' => 'Mobile number does not exist',));
                } else {
                    $this->_redirect($this->formatURL('/agentcorpcardload/doload?mob=' . $chInfo['mobile']));
                    exit;
                }
            }
        }
        $this->view->form = $form;
    }

    public function doloadAction() {
        $this->title = 'Fund Load Details';
        $this->view->heading = 'Fund Load Details';
        $session = new Zend_Session_Namespace('App.Agent.Controller');
        $user = Zend_Auth::getInstance()->getIdentity();
        $agentId = $user->id;
        $objAgBal = new AgentBalance();
        $m = new App\Messaging\Corp\Ratnakar\Agent();
        $chObj = new Corp_Ratnakar_Cardholders();
        //Get The product Id for Only smp
        $productIds = $user->product_ids;
        $prodConstArr = Util::getArrayBykey($productIds, 'product_const'); 
        if(in_array(PRODUCT_CONST_RAT_SMP,$prodConstArr)){
            $key = array_search(PRODUCT_CONST_RAT_SMP,$prodConstArr);
            $product_id = $productIds[$key]['product_id']; 
        } else {
            $this->_helper->FlashMessenger(array('msg-error' => 'Product not assigned to the agent.'));
            $this->_redirect($this->formatURL('/profile/index'));
        }

        $request = $this->_getAllParams();
        $mobile = $this->_getParam('mob');
        $formData = $this->_request->getPost();
        $btnAuth = isset($formData['send_auth_code']) ? $formData['send_auth_code'] : 0;
        $submit = isset($formData['is_submit']) ? $formData['is_submit'] : '';

        $form = new Corp_Ratnakar_FundReloadForm(array(
            'action' => '',
            'method' => 'POST',
            'name' => 'frmFund',
            'id' => 'frmFund'
        ));

        if ($mobile == '') {
            $this->_helper->FlashMessenger(array('msg-error' => 'Please complete mobile detail step',));
            $this->_redirect($this->formatURL('/agentcorpcardload/load'));
            exit;
        } else {
            $chInfo = $chObj->checkCardholder(array(
                'mobile_number' => $mobile,
                'product_id' => $product_id
            ));
            if (!empty($chInfo)) {
                $chid = $chInfo['id'];
                $ch_rat_customer_id = $chInfo['rat_customer_id'];
                $this->view->chInfo = $chInfo;
                $prodArray[$chInfo['product_id']] = $chInfo['product_name'];
                $form->getElement("product_id")->setMultiOptions($prodArray);
            } else {
                $this->_helper->FlashMessenger(array('msg-error' => 'Mobile number does not exist',));
                $this->_redirect($this->formatURL('/agentcorpcardload/load'));
                exit;
            }
        }
//        if (!$submit) {
//             if ($formData['amount'] == '') {
//                $this->_helper->FlashMessenger(array('msg-error' => 'Enter load amount'));
//           } elseif($formData['amount'] < 1 || !ctype_digit($formData['amount'])) { 
//		$this->_helper->FlashMessenger(array('msg-error' => 'Invalid load amount'));
//           }else {
//                try {
//                    //Validate Agent
//                    $isSufficientBal = $objAgBal->chkAllowReLoad(array(
//                        'agent_id' => $user->id,
//                        'product_id' => $formData['product_id'],
//                        'amount' => $formData['amount']
//                    ));
//                    //Validate Customer 
//                    $productWallet = App_DI_Definition_BankProduct::getInstance(BANK_RATNAKAR_SMP);
//                    $genwalletCode = $productWallet->purse->code->genwallet;
//
//                    $purs_details = $chObj->getRatCardholderPursesDetail(
//                            array(
//                                'customer_id' => $chInfo['id'],
//                                'rat_customer_id' => $chInfo['rat_customer_id'],
//                                'wallet_code' => $genwalletCode,
//                                'product_id' => $formData['product_id'],
//                    )); 
//                    $isCustmer = array(
//                        'customer_master_id' => $chInfo['customer_master_id'],
//                        'purse_master_id' => $purs_details['purse_master_id'],
//                        'customer_purse_id' => $purs_details['customer_purse_id'],
//                        'amount' => $formData['amount'],
//                        'agent_id' => $user->id,
//                        'product_id' => $formData['product_id'],
//                        'bank_id' => $purs_details['bank_id'],
//                        'manageType' => AGENT_MANAGE_TYPE,
//                    );
//                    $baseTxn = new BaseTxn();
//                    $flgValidate = $baseTxn->chkAllowRatCardLoadLimit($isCustmer); 
//                    if ($isSufficientBal && $flgValidate) {
//                        $userData = array(
//                            'mobile' => $mobile,
//                            'amount' => $formData['amount'],
//                            'cardholder_name' => $chInfo['name'],
//                            'account_name' => SMP_SHMART_ACCOUNT,
//                            'currency' => CURRENCY_INR
//                        );
////                        if ($session->ch_fund_load_auth == '') {
////                            $resp = $m->cardLoadAgentAuth($userData);
////                        } else {
////                            $resp = $m->cardLoadAgentAuth($userData, $resend = TRUE);
////                        }
////                        $this->_helper->FlashMessenger(array('msg-success' => 'Authorization code has been sent on cardholder mobile number',));
////                        $form->getElement("send_auth_code")->setValue("0");
//                    }
//                } catch (Exception $e) {
//                    $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage(),));
//                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
//                }
//            }
//        }
        if ($submit) {
            if ($form->isValid($this->getRequest()->getPost())) {
                if ($formData['amount'] == '') {
                    $this->_helper->FlashMessenger(array('msg-error' => 'Enter load amount'));
               } elseif($formData['amount'] < 1 || !ctype_digit($formData['amount'])) { 
                    $this->_helper->FlashMessenger(array('msg-error' => 'Invalid load amount'));
               } else {
                    try {
                        $loadArr = array(
                            'ch_id' => $chid,
                            'ch_mobile' => $mobile,
                            'product_id' => $product_id,
                            'agent_id' => $user->id,
                            'bank_product_const' => BANK_RATNAKAR_SMP,
                            'amount' => Util::convertIntoPaisa($formData['amount']),
                            'channel' => CHANNEL_AGENT
                        );
                        //$chload = $chObj->byAgentCardload($loadArr);
                        $chload = $chObj->cardloadByAgent($loadArr);
                        
                        if ($chload == FALSE) {
                            throw('Card fund load transaction failed.');
                        } else {
                            $custPurseModel = new Corp_Ratnakar_CustomerPurse();
                            $custPurse = $custPurseModel->getCustBalance($ch_rat_customer_id);
                            // Get balance
                            $balVal = $custPurse['sum'];
                            $userData = array(
                                'mobile' => $mobile,
                                'amount' => $formData['amount'],
                                'cardholder_name' => $chInfo['name'],
                                'account_name' => SMP_SHMART_ACCOUNT,
                                'currency' => CURRENCY_RUPEES,
                                'call_center_num' => AGENT_AUTH_CALL_CENTRE_NUMBER,
                                'current_balance' => $balVal
                            );
                            $resp = $m->cardLoadAgentMsg($userData);
                            $msg = CURRENCY_RUPEES . ' ' . $formData['amount']
                                    . ' successfully credited in ' . $chInfo['name'] . " 's " . SMP_SHMART_ACCOUNT;
                            $this->_helper->FlashMessenger(array('msg-success' => $msg));
                            $this->_redirect($this->formatURL('/agentcorpcardload/load'));
                            exit;
                        }
                    } catch (Exception $e) {
                        App_Logger::log(serialize($e), Zend_Log::ERR);
                        $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage(),));
                        $this->view->formData = $formData;
                    }
                }
            }
        }
        $this->view->form = $form;
        $form->populate($formData);
    }
}
