<?php
/**
 * Allow the admins to manage critical info, users, groups, permissions, etc.
 *
 * @category backoffice
 * @package backoffice_controllers
 * @copyright company
 */

class BankController extends App_Operation_Controller
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
    }
    
    
    public function indexAction(){

        //echo "<pre>";print_r($session);
        $this->title = 'Banks';
        $bankModel = new Banks();
        $this->view->paginator = $bankModel->findAll($this->_getPage());
    }
    
    
     /**
     * Edits an existing privilege
     *
     * @access public
     * @return void
     */
    public function editAction(){

        $this->title = 'Edit Bank';
        $id = $this->_getParam('id');
        $form = new BankForm();
        $bankModel = new Banks();
        $row = $bankModel->findById($id);
        $user = Zend_Auth::getInstance()->getIdentity();
        $row['ip'] = $bankModel->formatIpAddress(Util::getIP());
        $row['by_ops_id'] = $user->id;
        $insertlog = new Log();
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
                $data = array('bank_id'=>$id,'name'=>$row['name'],
                    'ifsc_code'=>$row['ifsc_code'],'city'=>$row['city'],
                    'branch_name'=>$row['branch_name'],'address'=>$row['address'],
                    'status'=> $row['status'] ,'ip'=>$row['ip'],
               'by_ops_id'=> $row['by_ops_id']                
                );
                $insertlog->insertlog($data,DbTable::TABLE_LOG_BANK);
                //$bankModel->updateBank($data);
                //print_r($form->getValues());exit;
                $row = $form->getValues();
                
                $bankModel->save($row);
                $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'The Bank details were successfully edited',
                    )
                );
                
                //Regenerate Flag and Flippers
                App_FlagFlippers_Manager::save();
                
                $this->_redirect($this->formatURL('/bank/index/'));
            }
        }else{
            $id = $this->_getParam('id');
            
            if (!is_numeric($id)) {
                $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'The provided bank_id is invalid',
                    )
                );
                
                $this->_redirect($this->formatURL('/bank/index/'));
            }
            
            
            
            if (empty($row)) {
                $this->_helper->FlashMessenger(
                    array(
                        'msg-warning' => 'The requested Bank could not be found',
                    )
                );
                
                $this->_redirect($this->formatURL('/bank/index/'));
            }
            
            $form->populate($row->toArray());
            $form->getElement('ifsc')->setValue($row['ifsc_code']);
            $this->view->item = $row;
        }
        
        $this->view->form = $form;
    }
    
    /**
     * Allows the user to view Bank Details
     * @access public
     * @return void
     */
    public function viewAction() {
        $this->title = 'Bank Details';
       // $product = new Products();
        $bank  = new Banks();
        $bid = $this->_getParam('bid');
       
        if (!is_numeric($bid)) {
            $this->_helper->FlashMessenger(
                    array(
                        'error' => 'The bank id you provided is invalid',
                    )
            );

            $this->_redirect($this->formatURL('/bank/index/'));
        }

        $bindBank = $bank->getBankbyID($bid);
        $this->view->item = $bindBank;
       
       
        
         //Select Customer Limit for Product
        $this->view->customerLimit = array();
        if (!empty($this->view->item['id'])) {
            $BankCustLimitModel = new BankCustomerLimits();
            $this->view->customerLimit = $BankCustLimitModel->getLimitDetailsbyBankId($this->view->item['id']);
       
        }
    }
    
     /**
     * Allows the user to view Customer Limit Details
     * @access public
     * @return void
     */
    public function viewcustomerlimitAction() {
        $this->title = 'Customer Limit Details';
        $id = $this->_getParam('id');
        if (!is_numeric($id)) {
            $this->_helper->FlashMessenger(
                    array(
                        'error' => 'The customer limit id you provided is invalid',
                    )
            );

            $this->_redirect($this->formatURL('/bank/index/'));
        }

        //Select customer Limit for Product
        $bankCustLimitModel = new BankCustomerLimits();
        $this->view->item = $bankCustLimitModel->getLimitDetailsbyPurseId($id);
    }
    
      /**
     * Edits an existing customer limit details
     *
     * @access public
     * @return void
     */
    public function editcustomerlimitAction() {
        $this->title = 'Edit Customer Limit';
        $id = $this->_getParam('id');
        $m = new App\Messaging\System\Operation();
        $bankCustLimitModel = new BankCustomerLimits();
      //  $productModel = new Products();
        $bankModel  = new Banks();
        $datetime = new DateTime('tomorrow');
        $dateToday = date('d-m-Y');
        $dateTmr = date('d-m-Y', strtotime("$dateToday, +1 day"));
        
        $form = new CustomerLimitDetailForm();
        
        $user = Zend_Auth::getInstance()->getIdentity();
        $row = $bankCustLimitModel->findById($id);
        $bankDetails = $bankModel->findById($row->bank_id);
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {

    
                $formdata = $form->getValues();
                $dataArr = array();
                $dataArr['max_balance'] = $formdata['max_balance'];
                $dataArr['load_min'] = $formdata['load_min'];
                $dataArr['load_max'] = $formdata['load_max'];
                $dataArr['load_max_val_daily'] = $formdata['load_max_val_daily'];
                $dataArr['load_max_val_monthly'] = $formdata['load_max_val_monthly'];
                $dataArr['load_max_val_yearly'] = $formdata['load_max_val_yearly'];
                $dataArr['txn_min'] = $formdata['txn_min'];
                $dataArr['txn_max'] = $formdata['txn_max'];
                $dataArr['txn_max_val_daily'] = $formdata['txn_max_val_daily'];
                $dataArr['txn_max_val_monthly'] = $formdata['txn_max_val_monthly'];
                $dataArr['txn_max_val_yearly'] = $formdata['txn_max_val_yearly'];
                $dataArr['date_start'] = new Zend_Db_Expr('NOW()') ;;
                $dataArr['date_updated'] = new Zend_Db_Expr('NOW()') ;
                $dataArr['by_ops_id'] = $user->id;
               
               try{
                $bankCustLimitModel->update($dataArr,"id = $id");
                   
                $logArr = array();
                $logArr['customer_limit_id'] = $id;
                $logArr['name'] = $row->name;
                $logArr['bank_id'] = $row->bank_id;
                $logArr['code'] = $row->code;
                $logArr['description'] = $row->description;
                $logArr['max_balance'] = $row->max_balance;
                $logArr['load_min'] = $row->load_min;
                $logArr['load_max'] = $row->load_max;
                $logArr['load_max_val_daily'] = $row->load_max_val_daily;
                $logArr['load_max_val_monthly'] = $row->load_max_val_monthly;
                $logArr['load_max_val_yearly'] = $row->load_max_val_yearly;
                $logArr['txn_min'] = $row->txn_min;
                $logArr['txn_max'] = $row->txn_max;
                $logArr['txn_max_val_daily'] = $row->txn_max_val_daily;
                $logArr['txn_max_val_monthly'] = $row->txn_max_val_monthly;
                $logArr['txn_max_val_yearly'] = $row->txn_max_val_yearly;
                $logArr['date_start'] = $row->date_start;
                $logArr['date_end'] = new Zend_Db_Expr('NOW()') ;
                $logArr['date_created'] = $row->date_created;
                $logArr['date_updated'] = new Zend_Db_Expr('NOW()') ;
                $logArr['by_ops_id'] = $user->id;        
                $logArr['status'] = $row->status;    
                $bankCustLimitModel->insertLog($logArr);

                
                 $newArr = array(
                   'Name'=> $row->name ,
                   'Max Balance'=> $formdata['max_balance'] ,
                   'Minimum Value per Load'=> $formdata['load_min'] ,
                   'Maximum Value per Load'=> $formdata['load_max'] ,
                   'Max Load Amount per Day'=> $formdata['load_max_val_daily'] ,
                   'Max Load Amount per Month'=> $formdata['load_max_val_monthly'] ,
                   'Max Load Amount per Year'=> $formdata['load_max_val_yearly'] ,
                   'Minimum Value of Txn'=> $formdata['txn_min'] ,
                   'Maximum Value of Txn'=> $formdata['txn_max'] ,
                   'Max Txn Amount per Day'=> $formdata['txn_max_val_daily'] ,
                   'Max Txn Amount per Month'=> $formdata['txn_max_val_monthly'] ,
                   'Max Txn Amount per Year'=> $formdata['txn_max_val_yearly'] ,      
                        );
                 
                 $oldArr = array(
                   'Name'=> $row->name ,
                   'Max Balance'=> $row->max_balance ,
                   'Minimum Value per Load'=> $row->load_min ,
                   'Maximum Value per Load'=> $row->load_max ,
                   'Max Load Amount per Day'=> $row->load_max_val_daily ,
                   'Max Load Amount per Month'=> $row->load_max_val_monthly ,
                   'Max Load Amount per Year'=> $row->load_max_val_yearly ,
                   'Minimum Value of Txn'=> $row->txn_min ,
                   'Maximum Value of Txn'=> $row->txn_max ,
                   'Max Txn Amount per Day'=> $row->txn_max_val_daily ,
                   'Max Txn Amount per Month'=> $row->txn_max_val_monthly ,
                   'Max Txn Amount per Year'=> $row->txn_max_val_yearly ,
                  );
                        // SEND NOTIFICATION TO ADMIN USERS
                  $mailData['email'] = ADMIN_EMAIL_IDS;
                  $mailData['bank_name'] = $bankDetails['name'];
                  $mailData['limit_category'] = 'CUSTOMER LIMIT: '.$row->name;
                  $mailData['param_name'] = 'NA';
                  $mailData['old_value'] = $oldArr;
                  $mailData['new_value'] = $newArr;
                  $m->bankcustlimitUpdates($mailData); 
                
                $this->_helper->FlashMessenger(
                        array(
                            'msg-success' => 'The customer limit was successfully updated',
                        )
                );
                    $this->_redirect($this->formatURL('/bank/view?bid='.$row->bank_id));
                
               }
               catch(Exception $e){
                     App_Logger::log($e->getMessage(), Zend_Log::ERR);
                     echo $e;
                }
            }
            
        } else {
            $id = $this->_getParam('id');

            if (!is_numeric($id)) {
                $this->_helper->FlashMessenger(
                        array(
                            'msg-error' => 'The provided purse id is invalid',
                        )
                );

                $this->_redirect($this->formatURL('/bank/view/'));
            }



            if (empty($row)) {
                $this->_helper->FlashMessenger(
                        array(
                            'msg-warning' => 'The requested purse could not be found',
                        )
                );

                $this->_redirect($this->formatURL('/bank/view/'));
            }
            $dateToday = date('d-m-Y');
            $dateTmr = date('d-m-Y', strtotime("$dateToday, +1 day"));
        
//            $row->date_start = $dateTmr;
            $form->populate($row->toArray());
           
        }
        
         $this->view->item = $row;
        $this->view->form = $form;
    }
}