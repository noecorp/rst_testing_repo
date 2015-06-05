<?php

/**
 * Allow the admins to manage critical info, users, groups, permissions, etc.
 *
 * @category backoffice
 * @package backoffice_controllers
 * @copyright company
 */
class AgentsummaryController extends App_Operation_Controller {

    /**
     * Overrides Zend_Controller_Action::init()
     *
     * @access public
     * @return void
     */
    public function init() {
        // init the parent
        parent::init();

        $this->session = new Zend_Session_Namespace("App.Operation.Controller");
    }

    public function indexAction() {
        $this->title = 'Agents';
        // Unset the agent_id so that a new Agent can be added or edited
        unset($this->session->agent_id);
        unset($this->session->step_one);
        unset($this->session->step_two);
        unset($this->session->step_three);
        unset($this->session->step_four);
        //$this->session->agent_id='';
       
        $data['searchCriteria'] = $this->_getParam('searchCriteria');
        $data['keyword'] = $this->_getParam('keyword');
        $data['sub'] = $this->_getParam('sub');

        $agentModel = new Agents();
        $agentUserModel = new AgentUser();
        $form = new AgentSearchForm(array('action' => $this->formatURL('/agentsummary/index'),
            'method' => 'POST',
        ));
        if ($data['sub'] != '') {

            $this->view->paginator = $agentModel->getDetails($data, $this->_getPage());
            $form->populate($data);
        }

        $this->view->backLink = 'searchCriteria=' . $data['searchCriteria'] . '&keyword=' . $data['keyword'] . '&sub=1';
        $this->view->controllerName = Zend_Registry::get('controllerName');
        $this->view->form = $form;
        $this->view->agentUser = $agentUserModel;
    }

    public function viewAction() {


        $this->title = 'Agent Details';
        $agentModel = new Agents();
        $approveagentModel = new Approveagent();
        $agentlimitModel = new Agentlimit();
        $id = $this->_getParam('id');
        $row = $agentModel->findById($id);
        $data['searchCriteria'] = $this->_getParam('searchCriteria');
        $data['keyword'] = $this->_getParam('keyword');
        $data['sub'] = $this->_getParam('sub');

        $documents = $agentModel->agentDoclist($id);

        $this->view->document = $documents->toArray();
        if (!empty($data['searchCriteria']) && !empty($data['keyword'])) {
            $queryString = 'searchCriteria=' . $data['searchCriteria'] . '&keyword=' . $data['keyword'] . '&sub=1';
            $this->view->backlink = Zend_Controller_Front::getInstance()->getBaseUrl() . '/agentsummary/index?' . $queryString;
        }
        $this->view->item = $row;
        $agentBalance = $agentModel->getAgentBalance($id);
        $this->view->balance = $agentBalance['amount'];
        $this->view->agent_id = $id;
        $prodArr = $approveagentModel->getagentproductDetailsAsArray($id);
        $this->view->agentStatus = $row['status'];
        $arrNew = array();
        if (!empty($prodArr)) {
            foreach ($prodArr as $key => $val) {
                $arr = array();

                $arr['product_name'] = $val['product_name'];
                //$arr['product_limit_name'] = $val['product_limit_name'] ;
                $arr['product_limit_code'] = $val['product_limit_code'];
                $arr['commission_name'] = $val['commission_name'];
                $arr['fee_name'] = $val['fee_name'];
                $arr['date_start'] = Util::returnDateFormatted($val['date_start'], "Y-m-d", "d-m-Y", $separator = "-");
                $arr['date_end'] = Util::returnDateFormatted($val['date_end'], "Y-m-d", "d-m-Y", $separator = "-");
                $arrNew[] = $arr;
            }
            $this->view->productArr = $arrNew;
        }
        else
            $this->view->productArr = '';

        $limitArr = $agentlimitModel->getAgentlimitAsArray($id);

        if (!empty($limitArr)) {
            foreach ($limitArr as $key => $val) {
                $limit = array();
                $limit['bid'] = $val['bid'];
                $limit['name'] = $val['name'];
                $limit['date_start'] = Util::returnDateFormatted($val['date_start'], "Y-m-d", "d-m-Y", $separator = "-");
                $limit['date_end'] = Util::returnDateFormatted($val['date_end'], "Y-m-d", "d-m-Y", $separator = "-");
                $arrlimitNew[] = $limit;
            }


            $this->view->limitArr = $arrlimitNew;
            //echo '<pre>';print_r($arrlimitNew);exit;
        }
        else
            $this->view->limitArr = '';
    }

    public function agentdocsAction() {
        $this->title = 'Agent Documents List';
        $id = $this->_getParam('id');
        $agentModel = new Agents();
        $documents = $agentModel->agentDoclist($id);

        $this->view->data = $documents->toArray();
    }

    /**
     * Allows the user to add another privilege in the application
     *
     * @access public
     * @return void
     */
    public function addAction() {
        $this->title = 'Add New Agent';

        $form = new AgentfullForm();
        $agentModel = new Agents();
        $formData = $this->_request->getPost();
        //echo "<pre>";print_r($form);exit;
        if ($this->getRequest()->isPost()) {
            //echo "<pre>";print_r($this->getRequest()->getPost());
            //exit;
            if ($form->isValid($this->getRequest()->getPost())) {

                $dataagents = array('afn' => $formData['afn'],
                    'email' => $formData['email'],
                    'username' => $formData['username'],
                    'password' => $this->getPassword(),
                    'status' => 'inactive',
                    'activation_code' => $this->getActivationCode($formData),
                    'agent_code' => $this->getAgentCode(),
                    'principle_distributor_id' => '123',
                    'mobile1' => $formData['mobile1'],
                    'mobile2' => $formData['mobile2'],
                    'date_created' => date('Y-m-d')
                );
                $dataagentdetails = array('first_name' => $formData['first_name'],
                    'middle_name' => $formData['middle_name'],
                    'last_name' => $formData['last_name'],
                    'home' => $formData['home'],
                    'office' => $formData['office'],
                    'shop' => $formData['shop'],
                    'matric_school_name' => $formData['matric_school_name'],
                    'intermediate_school_name' => $formData['intermediate_school_name'],
                    'graduation_degree' => $formData['graduation_degree'],
                    'graduation_college' => $formData['graduation_college'],
                    'p_graduation_degree' => $formData['p_graduation_degree'],
                    'p_graduation_college' => $formData['p_graduation_college'],
                    'other_degree' => $formData['other_degree'], 'other_college' => $formData['other_college'],
                    'date_of_birth' => $formData['date_of_birth'],
                    'gender' => $formData['gender'],
                    'Identification_type' => $formData['Identification_type'],
                    'Identification_number' => $formData['Identification_number'],
                    'pan_number' => $formData['pan_number'],
                    'res_type' => $formData['res_type'],
                    'res_address1' => $formData['res_address1'],
                    'res_address2' => $formData['res_address2'],
                    'res_city' => $formData['res_city'],
                    'res_taluka' => $formData['res_taluka'], 'res_district' => $formData['res_district'], 'res_state' => $formData['res_state'],
                    'res_country' => $formData['res_country'], 'res_pincode' => $formData['res_pincode'],
                    'fund_account_type' => $formData['fund_account_type'],
                    'bank_name' => $formData['bank_name'],
                    'bank_account_number' => $formData['bank_account_number'],
                    'bank_id' => $formData['bank_id'],
                    'bank_location' => $formData['bank_location'], 'bank_city' => $formData['bank_city'], 'bank_ifsc_code' => $formData['bank_ifsc_code'],
                    'branch_id' => $formData['branch_id'], 'bank_area' => $formData['bank_area'],
                    'bank_branch_id' => $formData['bank_branch_id']);


                $message = $agentModel->savedetails($dataagents, $dataagentdetails);
                if ($message == 'email_mobile_dup') {
                    $this->_helper->FlashMessenger(
                            array(
                                'msg-success' => 'Phone no. or email already exists.',
                            )
                    );
//                    $this->_redirect('/agentsummary/add');
                    $this->_redirect($this->formatURL('/agentsummary/add/'));
                } else {
                    $this->_helper->FlashMessenger(
                            array(
                                'msg-success' => 'Agent successfully added.',
                            )
                    );
                }
                //Regenerate Flag and Flippers
                App_FlagFlippers_Manager::save();

//                $this->_redirect('/agentsummary/');
                $this->_redirect($this->formatURL('/agentsummary/index/'));
            }
        }

        $this->view->form = $form;
    }

    /**
     * Edits an existing privilege
     *
     * @access public
     * @return void
     */
    public function editAction() {
        $this->title = 'Edit Agent';

        $form = new AgentfullForm();
        $agentModel = new Agents();
        $formData = $this->_request->getPost();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {


                $dataagents = array('afn' => $formData['afn'],
                    'email' => $formData['email'],
                    'username' => $formData['username'],
                    'password' => $this->getPassword(),
                    'status' => 'inactive',
                    'activation_code' => $this->getActivationCode($formData),
                    'agent_code' => $this->getAgentCode(),
                    'principle_distributor_id' => '123',
                    'mobile1' => $formData['mobile1'],
                    'mobile2' => $formData['mobile2'],
                    'date_created' => date('Y-m-d')
                );
                $dataagentdetails = array('first_name' => $formData['first_name'],
                    'middle_name' => $formData['middle_name'],
                    'last_name' => $formData['last_name'],
                    'home' => $formData['home'],
                    'office' => $formData['office'],
                    'shop' => $formData['shop'],
                    'matric_school_name' => $formData['matric_school_name'],
                    'intermediate_school_name' => $formData['intermediate_school_name'],
                    'graduation_degree' => $formData['graduation_degree'],
                    'graduation_college' => $formData['graduation_college'],
                    'p_graduation_degree' => $formData['p_graduation_degree'],
                    'p_graduation_college' => $formData['p_graduation_college'],
                    'other_degree' => $formData['other_degree'], 'other_college' => $formData['other_college'],
                    'date_of_birth' => $formData['date_of_birth'],
                    'gender' => $formData['gender'],
                    'Identification_type' => $formData['Identification_type'],
                    'Identification_number' => $formData['Identification_number'],
                    'pan_number' => $formData['pan_number'],
                    'res_type' => $formData['res_type'],
                    'res_address1' => $formData['res_address1'],
                    'res_address2' => $formData['res_address2'],
                    'res_city' => $formData['res_city'],
                    'res_taluka' => $formData['res_taluka'], 'res_district' => $formData['res_district'], 'res_state' => $formData['res_state'],
                    'res_country' => $formData['res_country'], 'res_pincode' => $formData['res_pincode'],
                    'fund_account_type' => $formData['fund_account_type'],
                    'bank_name' => $formData['bank_name'],
                    'bank_account_number' => $formData['bank_account_number'],
                    'bank_id' => $formData['bank_id'],
                    'bank_location' => $formData['bank_location'], 'bank_city' => $formData['bank_city'], 'bank_ifsc_code' => $formData['bank_ifsc_code'],
                    'branch_id' => $formData['branch_id'], 'bank_area' => $formData['bank_area'],
                    'bank_branch_id' => $formData['bank_branch_id']);
                $id = $this->_getParam('id');

                $agentModel->updatedetails($dataagents, $dataagentdetails, $id);
                if ($agentmodel) {
                    $this->_helper->FlashMessenger(
                            array(
                                'msg-success' => 'The Agent was successfully edited.',
                            )
                    );
                } else {
                    $this->_helper->FlashMessenger(
                            array(
                                'msg-success' => 'The Agent details could not be edited.',
                            )
                    );
                }


                //Regenerate Flag and Flippers
                App_FlagFlippers_Manager::save();

//                $this->_redirect('/agentsummary/');
                $this->_redirect($this->formatURL('/agentsummary/'));
            }
        } else {
            $id = $this->_getParam('id');

            if (!is_numeric($id)) {
                $this->_helper->FlashMessenger(
                        array(
                            'msg-error' => 'The provided agent_id is invalid.',
                        )
                );

//                $this->_redirect('/agentsummary/');
                $this->_redirect($this->formatURL('/agentsummary/'));
            }

            $row = $agentModel->findById($id);

            if (empty($row)) {
                $this->_helper->FlashMessenger(
                        array(
                            'msg-warning' => 'The requested agent_id could not be found.',
                        )
                );

//                $this->_redirect('/agentsummary/');
                $this->_redirect($this->formatURL('/agentsummary/index'));
            }

            $form->populate($row->toArray());
            $this->view->item = $row;
        }

        $this->view->form = $form;
    }

    /**
     * Allows the user to delete an existing privilege. All the flippers related to
     * this privilege will be removed
     *
     * @access public
     * @return void
     */
    public function blockAction() {
        $this->title = 'Block Agent';

        $form = new AgentstatuschangeForm();
        $agentModel = new Agents();
        $user = Zend_Auth::getInstance()->getIdentity();
        $dataArr['searchCriteria'] = $this->_getParam('searchCriteria');
        $dataArr['keyword'] = $this->_getParam('keyword');
        $dataArr['sub'] = $this->_getParam('sub');
        $queryString = '?searchCriteria=' . $dataArr['searchCriteria'] . '&keyword=' . $dataArr['keyword'] . '&sub=1';


        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {


                //$formData  = $this->_request->getPost();
                $formData = $form->getValues();
                $data = array('agent_id' => $this->_getParam('id'), 'by_ops_id' => $user->id,
                    'status_old' => STATUS_UNBLOCKED, 'status_new' => STATUS_BLOCKED, 'remarks' => $formData['remarks']);

                $agentModel->blockByAgentId($this->_getParam('id'), $data);
                $this->_helper->FlashMessenger(
                        array(
                            'msg-success' => 'The Agent was successfully Blocked.',
                        )
                );

                //Regenerate Flag and Flippers
                //App_FlagFlippers_Manager::save();
//                $this->_redirect('/agentsummary/');
                $this->_redirect($this->formatURL('/agentsummary/index' . $queryString));
            }
        } else {

            $id = $this->_getParam('id');
            $row = $agentModel->findById($id);

            if (empty($row)) {
                $this->_helper->FlashMessenger(
                        array(
                            'msg-warning' => sprintf('We cannot find Agent with id %s', $id),
                        )
                );
//                $this->_redirect('/agentsummary/');
                $this->_redirect($this->formatURL('/agentsummary/index/' . $queryString));
            }

            $row['id'] = $id;
            $form->populate($row->toArray());
            $this->view->item = (object) $row;
        }

        $this->view->form = $form;
        $this->view->queryString = $this->formatURL('/agentsummary/index' . $queryString);
    }

    public function unblockAction() {
        $this->title = 'Unblock Agent';

        $form = new AgentstatuschangeForm();
        $agentModel = new Agents();
        $user = Zend_Auth::getInstance()->getIdentity();
        $dataArr['searchCriteria'] = $this->_getParam('searchCriteria');
        $dataArr['keyword'] = $this->_getParam('keyword');
        $dataArr['sub'] = $this->_getParam('sub');
        $queryString = '?searchCriteria=' . $dataArr['searchCriteria'] . '&keyword=' . $dataArr['keyword'] . '&sub=1';

        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                //$formData  = $this->_request->getPost();
                $formData = $form->getValues();
                $data = array('agent_id' => $this->_getParam('id'), 'by_ops_id' => $user->id,
                    'status_old' => STATUS_BLOCKED, 'status_new' => STATUS_UNBLOCKED, 'remarks' => $formData['remarks']);


                $agentModel->unblockByAgentId($this->_getParam('id'), $data);
                $this->_helper->FlashMessenger(
                        array(
                            'msg-success' => 'The Agent was successfully Unblocked.',
                        )
                );

                //Regenerate Flag and Flippers
                //App_FlagFlippers_Manager::save();
//                $this->_redirect('/agentsummary/');
                $this->_redirect($this->formatURL('/agentsummary/index' . $queryString));
            }
        } else {
            $id = $this->_getParam('id');
            $row = $agentModel->findById($id);

            if (empty($row)) {
                $this->_helper->FlashMessenger(
                        array(
                            'msg-warning' => sprintf('We cannot find Agent with id %s', $id),
                        )
                );
//                $this->_redirect('/agentsummary/');
                $this->_redirect($this->formatURL('/agentsummary/index' . $queryString));
            }

            $row['id'] = $id;
            $form->populate($row->toArray());
            $this->view->item = (object) $row;
        }

        $this->view->form = $form;
        $this->view->queryString = $this->formatURL('/agentsummary/index' . $queryString);
    }

    public function unlockAction() {
        $this->title = 'Unlock Agent';

        $form = new AgentstatuschangeForm();
        $agentModel = new Agents();
        $user = Zend_Auth::getInstance()->getIdentity();
        $dataArr['searchCriteria'] = $this->_getParam('searchCriteria');
        $dataArr['keyword'] = $this->_getParam('keyword');
        $dataArr['sub'] = $this->_getParam('sub');
        $queryString = '?searchCriteria=' . $dataArr['searchCriteria'] . '&keyword=' . $dataArr['keyword'] . '&sub=1';

        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                //$formData  = $this->_request->getPost();
                $formData = $form->getValues();
                $data = array('agent_id' => $this->_getParam('id'), 'by_ops_id' => $user->id,
                    'status_old' => STATUS_LOCKED, 'status_new' => STATUS_UNBLOCKED, 'remarks' => $formData['remarks']
                );


                $agentModel->unlockByAgentId($this->_getParam('id'), $data);
                $this->_helper->FlashMessenger(
                        array(
                            'msg-success' => 'The Agent was successfully Unlocked.',
                        )
                );

                //Regenerate Flag and Flippers
                //App_FlagFlippers_Manager::save();
//                $this->_redirect('/agentsummary/');
                $this->_redirect($this->formatURL('/agentsummary/index' . $queryString));
            }
        } else {
            $id = $this->_getParam('id');
            $row = $agentModel->findById($id);

            if (empty($row)) {
                $this->_helper->FlashMessenger(
                        array(
                            'msg-warning' => sprintf('We cannot find Agent with id %s', $id),
                        )
                );
//                $this->_redirect('/agentsummary/');
                $this->_redirect($this->formatURL('/agentsummary/index' . $queryString));
            }

            $row['id'] = $id;
            $form->populate($row->toArray());
            $this->view->item = (object) $row;
        }

        $this->view->form = $form;
        $this->view->queryString = $this->formatURL('/agentsummary/index' . $queryString);
    }

    private function getAgentCode() {
        return rand(1, 32767);
    }

    private function getActivationCode($data) {
        return base64_encode($data['afn'] . '-' . $data['email']);
    }

    private function sendMobActivationCode() {
        // sending actication code on mobile codding here 
    }

    private function getPassword() {
        return substr(md5(rand(0, 1000000)), 0, 10);
    }
   public function closeaccountAction(){
       
        $this->title = 'Close Agent Account';
        $id = $this->_getParam('id');
        $form = new AgentCloseAccountForm();
        $objAgent = new AgentBalance(); 
        $user = Zend_Auth::getInstance()->getIdentity();
        $dataArr['searchCriteria'] = $this->_getParam('searchCriteria');
        $dataArr['keyword'] = $this->_getParam('keyword');
        $dataArr['sub'] = $this->_getParam('sub');
        $queryString = '?searchCriteria=' . $dataArr['searchCriteria'] . '&keyword=' . $dataArr['keyword'] . '&sub=1';

        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $formData = $form->getValues();
              
                $this->_redirect($this->formatURL('/agentsummary/index' . $queryString));
            }
        } else {
            
            $row = $objAgent->getBalance($id);
            if (empty($row)) {
                $this->_helper->FlashMessenger(
                        array(
                            'msg-warning' => sprintf('We cannot find Agent with id %s', $id),
                        )
                );
//                $this->_redirect('/agentsummary/');
                $this->_redirect($this->formatURL('/agentsummary/index' . $queryString));
            }

            $form->populate(Util::toArray($row));
            $this->view->item = (object) $row;
        }

        $this->view->form = $form;
        $this->view->queryString = $this->formatURL('/agentsummary/index' . $queryString);
   }
   
    public function remappingAction(){
        $this->title = 'Agent Remapping';
        
        $agentsModel = new AgentUser();        
        $approveagentModel = new Approveagent();
        $agentproductModel = new BindAgentProductCommission();
        
        $id = $this->_getParam('id');
        $res = false;
        
        $agentInfo = $agentsModel->findById($id);
        $form = new AgentRemappingForm($agentInfo); 
        $userType = $agentsModel->getAgentType($agentInfo['id']);
        
        if( $userType == SUPER_AGENT ) {
            $this->_helper->FlashMessenger(
                array(
                'msg-error' => 'Agent can not be remapped',
                )
            );
            $this->_redirect($this->formatURL('/agentsummary/index'));
        } elseif( $userType == DISTRIBUTOR_AGENT ) {
            $form->removeElement('distributor_agent');
        }
        
        $agentProduct = $agentproductModel->findProductById($id);

        $searchArr = array('product_id'=>$agentProduct['product_id'],
                            'user_type'=>DISTRIBUTOR_AGENT);
        $superdistDD = $agentsModel->getDistributorList($searchArr);
        $form->getElement('super_agent')->setmultioptions($superdistDD);
            
        
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
                $formData  = $this->_request->getPost();

                try{
                    
                    $params = array(
                        'super_agent' => $formData['super_agent'],
                        'distributor_agent' => $formData['distributor_agent'],
                        'agent_id' => $id,
                        'product_id' => $agentProduct['product_id'],
                        'user_type' => $userType
                    );
                    
                    $res = $agentsModel->mapAgent($params);
                    
                    if($res === TRUE) {
                        $this->_helper->FlashMessenger(
                            array(
                                'msg-success' => 'Agent Remapped successfully.',
                            )
                        );
                        $this->_redirect($this->formatURL('/agentsummary/index/'));
                    }
                } catch(Exception $e) {
                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    $msg = $e->getMessage();
                    $this->_helper->FlashMessenger(
                        array(
                            'msg-error' => $msg,
                        )
                    );
                }                             
            }
        } else {
            $id = $this->_getParam('id');
            $row = $approveagentModel->findAgent($id);
            
            if (empty($row)) {
                $this->_helper->FlashMessenger(
                    array(
                        'msg-warning' => sprintf('We cannot find Agent with id %s', $id),
                    )
                );
                $this->_redirect($this->formatURL('/agentsummary/index/'));
            }
            
            $form->populate($row);
            $this->view->item = (object)$row;
        }        
        $row = $approveagentModel->findAgent($id);
        $info = $agentsModel->getAgentCodeName($row['user_type'],$id);
        
        if(!empty($info)) {
            $row['dist_info'] = $info['dist_name'].'('.$info['dist_code'].')';
            $row['super_info'] = $info['sup_dist_name'].'('.$info['sup_dist_code'].')';
        }
     
        $this->view->item = (object)$row;
        $this->view->form = $form;
        $this->view->agentUser = $agentsModel;
        $this->view->productid = $agentProduct['product_id'];
   }
}