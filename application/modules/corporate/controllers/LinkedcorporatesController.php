<?php
/**
 * Allows user to see reports
 *
 * @category backoffice
 * @package backoffice_controllers
 * @copyright company
 */

class LinkedcorporatesController extends App_Corporate_Controller
{
    /**
     * Overrides Zend_Controller_Action::init()
     *  
     * @access public
     * @return void
     */
   
    public function init()
    {
        
        // init the parent
        parent::init();
        $user = Zend_Auth::getInstance()->getIdentity();
         if(!isset($user->id)) {
            $this->_redirect($this->formatURL('/profile/login'));
            exit;
         }
        
       //$this->_addCommand(new App_Command_SendEmail());
        
    }
       
    /**
     * Controller's entry point
     *
     * @access public
     * @return void
     */
    public function supercorporateAction(){
        $agentModel = new CorporateUser();
        $user = Zend_Auth::getInstance()->getIdentity();
        
        $userType = $user->user_type;
        if($userType == HEAD_CORPORATE){
            $title = 'Regional';
        }
        else
        {
             $title = 'Local';
        }
        $this->title = $title.' Listing'; 
        $this->view->title = $this->title;
        $this->view->userType = $userType;
    }       
 

     public function subcorporatelistingAction(){
        $corporateModel = new CorporateUser();
        $user = Zend_Auth::getInstance()->getIdentity();
        $userType = $user->user_type;
        if($userType == HEAD_CORPORATE){
            $title = 'Regional';
        }
        else
        {
             $title = 'Local';
        }
        $this->title = $title.' Listing';              
      
        $page = $this->_getParam('page');
        $objCorporate = new Corporates();
        
        $objectRelation = new ObjectRelations();
        if($corporateModel->isHead($user->id)) {
            $typeId = $objectRelation->getRelationTypeId(HEAD_TO_REGIONAL);
        } elseif(isset($user->id) && $corporateModel->isRegional($user->id)) {
            $typeId = $objectRelation->getRelationTypeId(REGIONAL_TO_LOCAL);                    
        }        
        $paginator = $objCorporate->getSubCorporateList($user->id,$typeId, $page);
        $this->view->paginator=$paginator;
        $this->view->title = $this->title;
        $this->view->userType = $title;
     }
     
     
     public function corporateinfoAction(){
        $agentModel = new CorporateUser();
        $user = Zend_Auth::getInstance()->getIdentity();
        $userType = $user->user_type;
        if($userType == HEAD_CORPORATE){
            $title = 'Regional';
        }
        else
        {
             $title = 'Local';
        }
        $this->title = $title.' Listing';                 
        //$user = Zend_Auth::getInstance()->getIdentity();
        $id = $this->_getParam('id');
        
        $page = $this->_getParam('page');
        $objAgent = new Corporates();
        $objProducts = new Products();
        $productInfo = $objProducts->getCorporateProductsInfo($id);
        $agentInfo = $objAgent->findById($id);

        //Pending Error Handling if agent not found
        $this->view->agentId = $id;
        $this->view->productInfo = $productInfo;
        $this->view->agentInfo = $agentInfo;
        $this->view->paginator= $objProducts->paginateByArray($productInfo, $page, NULL);
        //echo '<pre>';print_r($this->view->paginator);exit;
     }
     
     
     
     public function assignproductAction(){

         
        $this->title = 'Assign Product';
        $this->view->title = 'Assign Product';
        $id = $this->_getParam('corporateid');
        
        $user = Zend_Auth::getInstance()->getIdentity();        
        
        $objProducts = new Products();
        $form = new AssignProductForm();
        $productInfo = $objProducts->getCorporateProductsInfo($user->id,$id);            
        if(!empty($productInfo)) 
        {
        $productArr = $this->filterProductArrayForForm($productInfo);
        $form->getElement('product_id')->setMultiOptions($productArr);
           
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
              
                $formData  = $this->_request->getPost();
                // Agent product limit and commission plan assignment
                $res = $this->assignCorporateProduct($formData['product_id'], $user->id,$id);
                // No Need to Assign Limit, Limit is assigned during registration
                //$limit = $this->assignAgentLimit($user->id,$id);
                //echo '<pre>';print_r($formData);exit;
                
                if ($res){
                    $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'New product assigned to agent',
                    )
                );
                    $this->_redirect($this->formatURL('/linkedcorporates/subcorporatelisting'));
                }
                else
                {
                    $this->_helper->FlashMessenger(
                        array(
                        'msg-error' => 'Could not be assigned',
                        )
                    );
                }
                
            } // valid
        
            $row = $this->_request->getPost();
            $this->view->item = $row;
            $form->populate($row);
            $form->getElement('product')->setValue($row['product_id']);
            $form->getElement('limit')->setValue($row['product_limit_id']); 
        
        }
       
        $this->view->form = $form;
        } else {
                    $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'There is no product to assign at this time',
                    )
                );
        }
      }
      
      
        
     private function assignCorporateProduct($product_id, $superAgentId, $subAgentId)
     {
         //$user = Zend_Auth::getInstance()->getIdentity();
         $agentproductModel = new BindCorporateProductCommission();
         $chkAgentProduct = $agentproductModel->chkDuplicateCorporateProduct($subAgentId, $product_id);
                if($chkAgentProduct)
                {
                    $productInfo = $agentproductModel->getCorporateProduct($superAgentId,$product_id);                    
                    $dateStart = new Zend_Db_Expr('CURDATE()');
                    $data = array('corporate_id'=>$subAgentId, 
                           'product_id'=>$product_id,
                           'product_limit_id'=>$productInfo['product_limit_id'],
                           'plan_commission_id'=>$productInfo['plan_commission_id'],
                           'plan_fee_id'=>$productInfo['plan_fee_id'],
                           'by_ops_id' => '0',
                           'by_corporate_id' => $superAgentId,
                           'date_start' => $dateStart
                          ); 
                    $res = $agentproductModel->corporateProduct($data); 
                    if($res > 0)
                    {
                      return $res;
                    }
            
                }
                else
                {
                    return FALSE;
                } 
     }
      
    private function filterProductArrayForForm($productInfo) {
        $productArr = array('' =>'Select Product');
        if (!empty($productInfo)) {
            foreach ($productInfo as $product) {
                $productArr[$product['product_id']] = $product['product_name'];
            }
        }
        return $productArr;
    }
    
    
     /**
     * fundtrfr
     * Agent to Agent Fund Transfer
     * @access public
     * @return void
     */    
    public function fundtrfrAction() {
        $this->title = 'Corporate Fund Transfer';
        $user = Zend_Auth::getInstance()->getIdentity();
        //echo "<pre>";print_r($user);
        $id = $this->_getParam('id');
        $page = $this->_getParam('page');
        $agentFundTrfrModel = new CorporateFundTransfer();
        
        $form = new CorporateFundTransferForm();
        $this->validateTxnCorporate($id);
             
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $formData = $this->_request->getPost();
                $trfrAmount = $formData['amount'];
                try {
                    $validator = new Validator_LimitValidator();
                    $res = $validator->chkAvailableCorporateBalance($user->id, $trfrAmount);
                } catch (App_Exception $e) {
                    $res = false;
                    $msg = $e->getMessage();
                } catch (Exception $e) {
                    $res = false;
                    $msg = $e->getMessage();
                }
                if ($res) {
                    //Redirect to Confirm URL
                    $this->_redirect($this->formatURL('/linkedcorporates/fundtrfrconfirm/id/' . $id . '/trframount/' . $trfrAmount));
                } else {
                    $this->_helper->FlashMessenger(
                            array(
                                //'msg-error' => $agentBalance . ' is less then ' . $trfrAmount,
                                //'msg-error' => 'Your balance is less then ' . $trfrAmount,
                                'msg-error' => $msg,
                            )
                    );
                }
            } // valid

            $row = $this->_request->getPost();
            $this->view->item = $row;
            $form->populate($row);
        }

        $this->view->corporateId = $id;
        $this->view->form = $form;
        $this->view->paginator = $agentFundTrfrModel->getCorporateFunding($user->id, $id, $page);
    }

    
     /**
     * fundtrfrconfirm
     * Agent to Agent Fund Transfer Confirmation Action
     * @access public
     * @return void
     */        
    public function fundtrfrconfirmAction() {
        $this->title = 'Agent Fund Transfer Confirm';
        $user = Zend_Auth::getInstance()->getIdentity();
        $id = $this->_getParam('id');
        $trfrAmount = $this->_getParam('trframount');

        $form = new CorporateFundTransferConfirmForm();
        $this->validateTxnCorporate($id);
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                try {
                    $validator = new Validator_LimitValidator();
                    $res = $validator->chkAvailableCorporateBalance($user->id, $trfrAmount);
                } catch (App_Exception $e) {
                    $res = false;
                    $msg = $e->getMessage();
                } catch (Exception $e) {
                    $res = false;
                    $msg = $e->getMessage();
                }
                if ($res) {
                    try {
                        $baseTxnModel = new BaseTxn();
                        $baseTxnModel->corporateToCorporate(array(
                            'corporate_id' => $user->id,
                            'txn_corporate_id' => $id,
                            'amount' => $trfrAmount,
                            'txn_type' => TXNTYPE_CORPORATE_TOCORPORATE_FUND_TRANSFER,
                        ));
                    } catch (App_Exception $e) {
                        $res = false;
                        $msg = $e->getMessage();
                    } catch (Exception $e) {
                        $res = false;
                        $msg = $e->getMessage();
                    }
                }
        if($user->user_type == HEAD_CORPORATE){
            $userType = 'regional';
        }
        else{
            $userType = 'local';
        }
                if ($res) {
                    $this->_helper->FlashMessenger(
                            array(
                                'msg-success' => 'Fund Transferred to '.$userType.' Successfully.',
                            )
                    );

                    //Redirect to Confirm URL
                    $this->_redirect($this->formatURL('/linkedcorporates/subcorporatelisting'));
                } else {
                    $this->_helper->FlashMessenger(
                            array(
                                //'msg-error' => $agentBalance . ' is less then ' . $trfrAmount,
                                //'msg-error' => 'Your balance is less then ' . $trfrAmount,
                                'msg-error' => $msg,
                            )
                    );
                }
            } // valid
            $row = $this->_request->getPost();
            $form->populate($row);
        }
        //Pending Error Handling if agent not found
        $this->view->corporateId = $id;
        $this->view->form = $form;

        $corporate = new CorporateBalance();
        //Get Logged in Agent Balance
        $balance = $corporate->getCorporateBalance($user->id);
        $myBalance = $balance - $trfrAmount;

        $txnAgentbalanceInfo = $corporate->getCorporateBalance($id);
        $txnAgentBalance = $txnAgentbalanceInfo + $trfrAmount;
        $corporateObj = new Corporates();
      
        $corporateInfo = $corporateObj->findById($id);
        //echo "<pre>"; print_r($corporateInfo); exit;

        $items = new stdClass();
        $items->name = $corporateInfo['first_name'] . ' ' . $corporateInfo['last_name'];
        $items->txn_amount = Util::numberFormat($trfrAmount);
        $items->my_balance = Util::numberFormat($myBalance);
        $items->txn_corporate_balance = Util::numberFormat($txnAgentBalance);
        $this->view->items = $items;
    }

     /**
     * retrievefund
     * Agent to Agent Fund Reversal Action
     * @access public
     * @return void
     */            
    public function retrievefundAction() {
        $this->title = 'Agent Fund Reversal';
        $user = Zend_Auth::getInstance()->getIdentity();
        $id = $this->_getParam('id');
        $page = $this->_getParam('page');
        $agentFundTrfrModel = new CorporateFundTransfer();
        $this->validateTxnCorporate($id);
        $form = new CorporateFundTransferForm();
        $form->getElement("submit")->setLabel('Retrieve Fund');
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {

                $formData = $this->_request->getPost();
                $trfrAmount = $formData['amount'];
                $agent = new CorporateBalance();
                $agentBalance = $agent->getCorporateBalance($id);
                //$agentBalance = $balanceInfo['amount'];
                if ($agentBalance >= $trfrAmount) {
                    //Redirect to Confirm URL
                    $this->_redirect($this->formatURL('/linkedcorporates/retrievetrfrconfirm/id/' . $id . '/trframount/' . $trfrAmount));
                } else {
                    $this->_helper->FlashMessenger(
                            array(
                                //'msg-error' => $agentBalance . ' is less then ' . $trfrAmount,
                                'msg-error' => 'Linked Corporate balance is less then ' . $trfrAmount,
                            )
                    );
                }
            } // valid

            $row = $this->_request->getPost();
            $this->view->item = $row;
            $form->populate($row);
        }

        //Pending Error Handling if agent not found
        $this->view->corporateId = $id;
        $this->view->form = $form;
        $this->view->paginator = $agentFundTrfrModel->getCorporateFunding($user->id, $id, $page);
    }

     /**
     * retrievetrfrconfirm
     * Agent to Agent Fund Reversal confirm Action
     * @access public
     * @return void
     */                
    public function retrievetrfrconfirmAction() {
        $this->title = 'Corporate Fund Reversal Confirm';
        $user = Zend_Auth::getInstance()->getIdentity();
        $id = $this->_getParam('id');
        $trfrAmount = $this->_getParam('trframount');
        $this->validateTxnCorporate($id);
        $form = new CorporateFundTransferConfirmForm();
        $form->getElement("submit")->setLabel("Yes, Reverse Fund");
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                try {
                    $validator = new Validator_LimitValidator();
                    $res = $validator->chkAvailableCorporateBalance($id, $trfrAmount);
                } catch (App_Exception $e) {
                    $res = false;
                    $msg = $e->getMessage();
                } catch (Exception $e) {
                    $res = false;
                    $msg = $e->getMessage();
                }
                if ($res) {
                    //echo 'Transfering Fund';exit;
                    try {
                        $baseTxnModel = new BaseTxn();
                        $baseTxnModel->corporateToCorporate(array(
                            'txn_corporate_id' => $id,
                            'corporate_id' => $user->id,
                            'amount' => $trfrAmount,
                            'txn_type' => TXNTYPE_CORPORATE_TOCORPORATE_FUND_REVERSAL,
                        ));
                    } catch (App_Exception $e) {
                        $res = false;
                        $msg = $e->getMessage();
                    } catch (Exception $e) {
                        $res = false;
                        $msg = $e->getMessage();
                    }
                }
                if ($res) {
                    $this->_helper->FlashMessenger(
                            array(
                                'msg-success' => 'Fund Reverse from Corpoate Successfully.',
                            )
                    );

                    //Redirect to Confirm URL
                    $this->_redirect($this->formatURL('/linkedcorporates/subcorporatelisting'));
                } else {
                    $this->_helper->FlashMessenger(
                            array(
                                //'msg-error' => $agentBalance . ' is less then ' . $trfrAmount,
                                //'msg-error' => 'Your balance is less then ' . $trfrAmount,
                                'msg-error' => $msg,
                            )
                    );
                }
            } // valid

            $row = $this->_request->getPost();
            $form->populate($row);
        }

        //Pending Error Handling if agent not found
        $this->view->corporateId = $id;
        $this->view->form = $form;

        $agent = new CorporateBalance();
        $balanceInfo = $agent->getCorporateBalance($user->id);
        $myBalance = $balanceInfo + $trfrAmount;
        $txnAgentbalanceInfo = $agent->getCorporateBalance($id);
        $txnAgentBalance = $txnAgentbalanceInfo - $trfrAmount;
        
        $coporate = new Corporates();
        $agentInfo = $coporate->findById($id);


        $items = new stdClass();
        $items->name = $agentInfo['first_name'] . ' ' . $agentInfo['last_name'];
        $items->txn_amount = Util::numberFormat($trfrAmount);
        $items->my_balance = Util::numberFormat($myBalance);
        $items->txn_agent_balance = Util::numberFormat($txnAgentBalance);
        $this->view->items = $items;
    }

    /**
     * validateTxnAgent
     * Validates Super and sub agent redirect otherwise
     * @param type $agentId
     */
    private function validateTxnCorporate($corporateId) {
        $corporateVerify = FALSE;
        $id = (int) $corporateId;

        if($id == $corporateId) {
            $corporateUserModel = new CorporateUser();
            $object = new ObjectRelations();
            $user = Zend_Auth::getInstance()->getIdentity();  
            $uType = $corporateUserModel->getCorporateType($corporateId);
            $label = $corporateUserModel->getObjectRelationshipLabel($uType);
            $corporateVerify = $object->checkRelation($corporateId, $user->id, $label);

        }
        if(!$corporateVerify) {
            $this->_helper->FlashMessenger(
                array(
                    'msg-error' => 'Corporate not found!!',
                )
            );
            $this->_redirect($this->formatURL('/linkedcorporates/subcorporatelisting'));
        }


    }    
    
    public function performancesummaryAction() {

        $user = Zend_Auth::getInstance()->getIdentity();
        $page = $this->_getParam('page');
        $objAgent = new Agents();
        $agentModel = new AgentUser();
        $objectRelation = new ObjectRelations();
        $this->title = 'Performance Summary Report';

        $form = new LinkedAgentsrPerformance(array('action' => $this->formatURL('/linkedagents/performancesummary'),
            'method' => 'POST',
        ));

        $request = $this->_getAllParams();
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['sub'] = $this->_getParam('sub');
        $qurStr['agent'] = $this->_getParam('agent');
        $qurStr['to_date'] = $this->_getParam('to_date');
        $qurStr['from_date'] = $this->_getParam('from_date');
        $page = $this->_getParam('page');
                
        $options = array("0" => "All");
        $agents = array();
        if ($agentModel->isSuperAgent($user->id)) {
            $supertypeId = $objectRelation->getRelationTypeId(SUPER_TO_DISTRIBUTOR);
            $distributors = $objAgent->getSubAgentList($user->id, $supertypeId, $page);
            foreach ($distributors as $item) {
                $userName = ucfirst($item->first_name) . ' ' . ucfirst($item->last_name);
                $options[$item->id] = $userName;
            }
        } elseif (isset($user->id) && $agentModel->isDistributorAgent($user->id)) {
            $distypeId = $objectRelation->getRelationTypeId(DISTRIBUTOR_TO_AGENT);
            $agentsData = $objAgent->getSubAgentList($user->id, $distypeId, $page);
            foreach ($agentsData as $item) {
                $userName = ucfirst($item->first_name) . ' ' . ucfirst($item->last_name);
                $options[$item->id] = $userName;
            }
        }

        $form->getElement("agent")->setMultiOptions($options);
        if ($qurStr['agent']) {
            $form->agent->setValue($qurStr['agent']);
        }

        if ($qurStr['sub'] != '') {
            if ($form->isValid($qurStr)) {
                if ($qurStr['dur'] != '') {
                    $durationArr = Util::getDurationDates($qurStr['dur']);
                    $qurData['from'] = $durationArr['from'];
                    $qurData['to'] = $durationArr['to'];
                    $fromDate = explode(' ', Util::returnDateFormatted($durationArr['from'], "d-m-Y", "Y-m-d", "-"));
                    $toDate = explode(' ', Util::returnDateFormatted($durationArr['to'], "d-m-Y", "Y-m-d", "-"));
                    $this->view->from = $fromDate[0];
                    $this->view->to = $toDate[0];
                    $this->view->title = 'Performance Summary Report ' . $fromDate[0];
                    $this->view->title .= ' to ' . $toDate[0];
                    $durationDates = Util::getDurationAllDates($qurStr['dur']);
                } elseif ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                    $durationDates = Util::getDurationRangeAllDates($qurData);
                    $this->view->title = 'Performance Summary Report ' . Util::returnDateFormatted($qurData['from'], "Y-m-d", "d-m-Y", "-");
                    $this->view->title .= ' to ' . Util::returnDateFormatted($qurData['to'], "Y-m-d", "d-m-Y", "-");
                    $this->view->from = $qurData['from'];
                    $this->view->to = $qurData['to'];
                }


                $objReports = new Reports();
                $reportData = array();
                if ($agentModel->isSuperAgent($user->id)) {
                    $supertypeId = $objectRelation->getRelationTypeId(SUPER_TO_DISTRIBUTOR);
                    $distributors = $objAgent->getSubAgentList($user->id, $supertypeId, $page);
                    foreach ($distributors as $distributor) {
                        if ($qurStr['agent']) {
                            if ($qurStr['agent'] != $distributor->id) {
                                continue;
                            }
                        }
                        $distypeId = $objectRelation->getRelationTypeId(DISTRIBUTOR_TO_AGENT);
                        $agents = $objAgent->getSubAgentList($distributor->id, $distypeId, $page);
                        $distributorName = ucfirst($distributor->first_name) . ' ' . ucfirst($distributor->last_name);
                        foreach ($agents as $item) {
                            $userName = ucfirst($item->first_name) . ' ' . ucfirst($item->last_name);
                            $rptData = $objReports->getRemittersAndAmount($durationDates, $item->id, $userName, $item->agent_code, false, false);
                            foreach ($rptData as $data) {
                                $data['name'] = $distributorName;
                                $data['code'] = $distributor->agent_code;
                                $reportData[] = $data;
                            }
                        }
                    }
                    $paginator = $objReports->paginateByArray($reportData, $page, $paginate = NULL);
                    $this->view->paginator = $paginator;
                } elseif (isset($user->id) && $agentModel->isDistributorAgent($user->id)) {
                    $distypeId = $objectRelation->getRelationTypeId(DISTRIBUTOR_TO_AGENT);
                    $agents = $objAgent->getSubAgentList($user->id, $distypeId, $page);
                    foreach ($agents as $item) {
                        if ($qurStr['agent']) {
                            if ($qurStr['agent'] != $item->id) {
                                continue;
                            }
                        }
                        $userName = ucfirst($item->first_name) . ' ' . ucfirst($item->last_name);
                        $rptData = $objReports->getRemittersAndAmount($durationDates, $item->id, $userName, $item->agent_code, false, false);
                        foreach ($rptData as $data) {
                            $reportData[] = $data;
                        }
                    }
                    $paginator = $objReports->paginateByArray($reportData, $page, $paginate = NULL);
                }
                $this->view->paginator = $paginator;
            }
        }
        $this->view->form = $form;
        $this->view->formData = $qurStr;
    }

    public function exportperformancesummaryAction() {

        $user = Zend_Auth::getInstance()->getIdentity();
        $page = $this->_getParam('page');
        $objAgent = new Agents();
        $agentModel = new AgentUser();
        $objectRelation = new ObjectRelations();
        $this->title = 'Performance Summary Report';

        $form = new LinkedAgentsrPerformance(array('action' => $this->formatURL('/linkedagents/performancesummary'),
            'method' => 'POST',
        ));

        $request = $this->_getAllParams();
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['sub'] = $this->_getParam('sub');
        $qurStr['agent'] = $this->_getParam('agent');
        $qurStr['to_date'] = $this->_getParam('to_date');
        $qurStr['from_date'] = $this->_getParam('from_date');
        $page = $this->_getParam('page');

        if ($qurStr['dur'] != '') {
            $durationArr = Util::getDurationDates($qurStr['dur']);
            $qurData['from'] = $durationArr['from'];
            $qurData['to'] = $durationArr['to'];
            $fromDate = explode(' ', Util::returnDateFormatted($durationArr['from'], "d-m-Y", "Y-m-d", "-"));
            $toDate = explode(' ', Util::returnDateFormatted($durationArr['to'], "d-m-Y", "Y-m-d", "-"));
            $this->view->from = $fromDate[0];
            $this->view->to = $toDate[0];
            $durationDates = Util::getDurationAllDates($qurStr['dur']);
        } else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
            $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
            $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
            $durationDates = Util::getDurationRangeAllDates($qurData);
            $this->view->title = 'Performance Summary Report ' . Util::returnDateFormatted($qurData['from'], "Y-m-d", "d-m-Y", "-");
            $this->view->title .= ' to ' . Util::returnDateFormatted($qurData['to'], "Y-m-d", "d-m-Y", "-");
            $this->view->from = $qurData['from'];
            $this->view->to = $qurData['to'];
        }

        $objReports = new Reports();
        $reportData = array();
        if ($agentModel->isSuperAgent($user->id)) {
            $supertypeId = $objectRelation->getRelationTypeId(SUPER_TO_DISTRIBUTOR);
            $distypeId = $objectRelation->getRelationTypeId(DISTRIBUTOR_TO_AGENT);
            $distributors = $objAgent->getSubAgentList($user->id, $supertypeId, $page);
            foreach ($distributors as $distributor) {
                if ($qurStr['agent']) {
                    if ($qurStr['agent'] != $distributor->id) {
                        continue;
                    }
                }
                $agents = $objAgent->getSubAgentList($distributor->id, $distypeId, $page);
                foreach ($agents as $item) {
                    $userName = ucfirst($item->first_name) . ' ' . ucfirst($item->last_name);
                    $rptData = $objReports->getRemittersAndAmount($durationDates, $item->id, $userName, $item->agent_code, false, false);
                    $csvData = array();
                    foreach ($rptData as $data) {
                        $csvData['date'] = $data['date'];
                        $csvData['name'] = $data['name'];
                        $csvData['code'] = $data['code'];
                        $csvData['remitter_count'] = $data['remitter_count'];
                        $csvData['remitter_txn_amt'] = Util::numberFormat($data['remitter_txn_amt']);
                        $csvData['remitter_rfnd_amt'] = Util::numberFormat($data['remitter_rfnd_amt']);
                        $reportData[] = $csvData;
                    }
                }
            }
            $paginator = $objReports->paginateByArray($reportData, $page, $paginate = NULL);
        } elseif (isset($user->id) && $agentModel->isDistributorAgent($user->id)) {
            $distypeId = $objectRelation->getRelationTypeId(DISTRIBUTOR_TO_AGENT);
            $agents = $objAgent->getSubAgentList($user->id, $distypeId, $page);
            foreach ($agents as $item) {
                if ($qurStr['agent']) {
                    if ($qurStr['agent'] != $item->id) {
                        continue;
                    }
                }
                $userName = ucfirst($item->first_name) . ' ' . ucfirst($item->last_name);
                $rptData = $objReports->getRemittersAndAmount($durationDates, $item->id, $userName, $item->agent_code, false, false);
                $csvData = array();
                foreach ($rptData as $data) {
                    $csvData['date'] = $data['date'];
                    $csvData['name'] = $data['name'];
                    $csvData['code'] = $data['code'];
                    $csvData['remitter_count'] = $data['remitter_count'];
                    $csvData['remitter_txn_amt'] = Util::numberFormat($data['remitter_txn_amt']);
                    $csvData['remitter_rfnd_amt'] = Util::numberFormat($data['remitter_rfnd_amt']);
                    $reportData[] = $csvData;
                }
            }
        }
        $columns = array(
            'Transaction Date',
            'Agent Name',
            'Code',
            'Remitter Registration',
            'Remittance Transaction ( Without fees)',
            'Refund (Without fees)',
        );

        $objCSV = new CSV();
        try {
            $resp = $objCSV->export($reportData, $columns, 'performance_summary_report');
            exit;
        } catch (Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage(),));
            $this->_redirect($this->formatURL('/linkedagents/performancesummary?from_date=' . $params['from'] . '&to_date=' . $params['to']));
        }
    }

    public function subagnentsbalanceAction() {
        $this->title = 'Balance Report';
        $user = Zend_Auth::getInstance()->getIdentity();
        //$form = new LinkedAgentsrBalanceRports(array('action' => $this->formatURL('/linkedagents/subagnentsbalance'),
        //    'method' => 'POST',
       // ));

        $request = $this->_getAllParams();
        $qurStr['agent'] = $this->_getParam('agent');
        $page = $this->_getParam('page');
        $objAgent = new Agents();
        $agentModel = new AgentUser();
        $objReports = new Reports();
        $objectRelation = new ObjectRelations();

        if ($agentModel->isSuperAgent($user->id)) {
            $typeId = $objectRelation->getRelationTypeId(SUPER_TO_DISTRIBUTOR);
        } elseif (isset($user->id) && $agentModel->isDistributorAgent($user->id)) {
            $typeId = $objectRelation->getRelationTypeId(DISTRIBUTOR_TO_AGENT);
        }

        $paginator = $objAgent->getSubAgentList($user->id, $typeId, $page);
        $options = array("" => "All");
        $reportData = array();

        foreach ($paginator as $item) {
            $userName = ucfirst($item->first_name) . ' ' . ucfirst($item->last_name);
            $options[$item->id] = $userName;
        }
        $data = array();
        foreach ($paginator as $item) {
            if ($qurStr['agent']) {
                if ($qurStr['agent'] != $item->id) {
                    continue;
                }
            }
            $data['name'] = ucfirst($item->first_name) . ' ' . ucfirst($item->last_name);
            $data['agent_code'] = $item->agent_code;
            $data['amount'] = Util::numberFormat($item->amount);
            $reportData[] = $data;
        }
       // $form->getElement("agent")->setMultiOptions($options);
       // if ($qurStr['agent']) {
        //    $form->agent->setValue($qurStr['agent']);
        //}
        $paginator = $objReports->paginateByArray($reportData, $page, $paginate = NULL);
        $this->view->paginator = $paginator;
        //$this->view->form = $form;
        $this->view->formData = $qurStr;
    }

    public function exportsubagnentsbalanceAction() {
        $this->title = 'Balance Report';
        $user = Zend_Auth::getInstance()->getIdentity();
        $page = $this->_getParam('page');
        $objAgent = new Agents();
        $agentModel = new AgentUser();
        $objectRelation = new ObjectRelations();
        $qurStr['agent'] = $this->_getParam('agent');

        if ($agentModel->isSuperAgent($user->id)) {
            $typeId = $objectRelation->getRelationTypeId(SUPER_TO_DISTRIBUTOR);
        } elseif (isset($user->id) && $agentModel->isDistributorAgent($user->id)) {
            $typeId = $objectRelation->getRelationTypeId(DISTRIBUTOR_TO_AGENT);
        }

        $paginator = $objAgent->getSubAgentList($user->id, $typeId, $page);
        $columns = array(
            'Agent Name',
            'Code',
            'Balance',
        );
        $data = array();
        $reportData = array();
        foreach ($paginator as $item) :
            if ($qurStr['agent']) {
                if ($qurStr['agent'] != $item->id) {
                    continue;
                }
            }
            $data['name'] = ucfirst($item->first_name) . ' ' . ucfirst($item->last_name);
            $data['code'] = $item->agent_code;
            $data['amt'] = Util::numberFormat($item->amount);
            $reportData[] = $data;
        endforeach;
        $objCSV = new CSV();
        try {
            $resp = $objCSV->export($reportData, $columns, 'balance_report');
            exit;
        } catch (Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage(),));
            $this->_redirect($this->formatURL('/linkedagents/subagnentsbalance?from_date=' . $params['from'] . '&to_date=' . $params['to']));
        }
    }

   
    
    public function assignproductbcAction(){

         
        $this->title = 'Assign Product';
        $this->view->title = 'Assign Product';
        $id = $this->_getParam('agentid');
        
        $user = Zend_Auth::getInstance()->getIdentity();        
        
        $objProducts = new Products();
        $form = new AssignProductForm();
        $productInfo = $objProducts->getAgentProductsInfo($user->id,$id);            
        if(!empty($productInfo)) 
        {
        $productArr = $this->filterProductArrayForForm($productInfo);
        $form->getElement('product_id')->setMultiOptions($productArr);
           
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
              
                $formData  = $this->_request->getPost();
                // Agent product limit and commission plan assignment
                $res = $this->assignCorporateProduct($formData['product_id'], $user->id,$id);
                // No Need to Assign Limit, Limit is assigned during registration
                //$limit = $this->assignAgentLimit($user->id,$id);
                //echo '<pre>';print_r($formData);exit;
                
                if ($res){
                    $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'New product assigned to partner',
                    )
                );
                    $this->_redirect($this->formatURL('/reports/bclisting'));
                }
                else
                {
                    $this->_helper->FlashMessenger(
                        array(
                        'msg-error' => 'Could not be assigned',
                        )
                    );
                }
                
            } // valid
        
            $row = $this->_request->getPost();
            $this->view->item = $row;
            $form->populate($row);
            $form->getElement('product')->setValue($row['product_id']);
            $form->getElement('limit')->setValue($row['product_limit_id']); 
        
        }
       
        $this->view->form = $form;
        } else {
                    $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'There is no product to assign at this time',
                    )
                );
        }
      }

}