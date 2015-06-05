<?php
/**
 * Allow the admins to manage critical info, users, groups, permissions, etc.
 *
 * @category backoffice
 * @package backoffice_controllers
 * @copyright company
 */

class WalletsController extends App_Operation_Controller
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

        $this->title = 'Wallet Listing';
        $walletModel = new GlobalPurse();
        $this->view->paginator = $walletModel->findAll($this->_getPage());
    }
    
    
     /**
     * Edits an existing privilege
     *
     * @access public
     * @return void
     */
    public function editAction(){

        $this->title = 'Edit Wallet';
        $id = $this->_getParam('id');
        $form = new EditWalletForm();
        $walletModel = new GlobalPurse();
        $row = $walletModel->findById($id);
        
        $user = Zend_Auth::getInstance()->getIdentity();
        $row = Util::toArray($row);
        $row['by_ops_id'] = $user->id;
        $insertlog = new Log();
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
               
                $data = array(
                'global_purse_id' => $id,
                'name' => $row['name'],
                'description' => $row['description'],
                'max_balance' => $row['max_balance'],
                'allow_remit' => $row['allow_remit'],
                'allow_mvc' => $row['allow_mvc'],
                'load_validity_day' => $row['load_validity_day'],
                'load_validity_hr' => $row['load_validity_hr'],
                'load_validity_min' => $row['load_validity_min'],
                'load_min' => $row['load_min'],
                'load_max' => $row['load_max'],
                'load_max_cnt_daily' => $row['load_max_cnt_daily'],
                'load_max_val_daily' => $row['load_max_val_daily'],
                'load_max_cnt_monthly' => $row['load_max_cnt_monthly'],
                'load_max_val_monthly' => $row['load_max_val_monthly'],
                'load_max_cnt_yearly' => $row['load_max_cnt_yearly'],
                'load_max_val_yearly' => $row['load_max_val_yearly'],
                'txn_restriction_type' => strtolower($row['txn_restriction_type']),
                'txn_upload_list' => $row['txn_upload_list'],
                'txn_min' => $row['txn_min'],
                'txn_max' => $row['txn_max'],
                'txn_max_cnt_daily' => $row['txn_max_cnt_daily'],
                'txn_max_val_daily' => $row['txn_max_val_daily'],
                'txn_max_cnt_monthly' => $row['txn_max_cnt_monthly'],
                'txn_max_val_monthly' => $row['txn_max_val_monthly'],
                'txn_max_cnt_yearly' => $row['txn_max_cnt_yearly'],
                'txn_max_val_yearly' => $row['txn_max_val_yearly'],
                'datetime_start' => $row['datetime_start'],
                'datetime_end' => new Zend_Db_Expr('NOW()'),
                'date_updated' => new Zend_Db_Expr('NOW()'),
                'by_ops_id' => $row['by_ops_id'],
                );
                
                $insertlog->insertlog($data,DbTable::TABLE_LOG_GLOBAL_PURSE_MASTER);
                
                $updateArr = $form->getValues();
                $updateArray = array(
                'description' => $updateArr['description'],
                'max_balance' => $updateArr['max_balance'],
                'load_validity_day' => $updateArr['load_validity_day'],
                'load_validity_hr' => $updateArr['load_validity_hr'],
                'load_validity_min' => $updateArr['load_validity_min'],
                'load_min' => $updateArr['load_min'],
                'load_max' => $updateArr['load_max'],
                'load_max_cnt_daily' => $updateArr['load_max_cnt_daily'],
                'load_max_val_daily' => $updateArr['load_max_val_daily'],
                'load_max_cnt_monthly' => $updateArr['load_max_cnt_monthly'],
                'load_max_val_monthly' => $updateArr['load_max_val_monthly'],
                'load_max_cnt_yearly' => $updateArr['load_max_cnt_yearly'],
                'load_max_val_yearly' => $updateArr['load_max_val_yearly'],
                'txn_restriction_type' => strtolower($updateArr['txn_restriction_type']),
                'txn_upload_list' => strtolower($updateArr['txn_upload_list']),
                'txn_min' => $updateArr['txn_min'],
                'txn_max' => $updateArr['txn_max'],
                'txn_max_cnt_daily' => $updateArr['txn_max_cnt_daily'],
                'txn_max_val_daily' => $updateArr['txn_max_val_daily'],
                'txn_max_cnt_monthly' => $updateArr['txn_max_cnt_monthly'],
                'txn_max_val_monthly' => $updateArr['txn_max_val_monthly'],
                'txn_max_cnt_yearly' => $updateArr['txn_max_cnt_yearly'],
                'txn_max_val_yearly' => $updateArr['txn_max_val_yearly'],
                'datetime_start' => new Zend_Db_Expr('NOW()'),
                'date_created' => new Zend_Db_Expr('NOW()'),
                'date_updated' => new Zend_Db_Expr('NOW()'),
                'by_ops_id' => $user->id,
                );
                $walletModel->update($updateArray, "id = $id");
                $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'The Wallet details were successfully updated',
                    )
                );
                
                
                
                $this->_redirect($this->formatURL('/wallets/index/'));
            }
        }else{
            $id = $this->_getParam('id');
            
            if (!is_numeric($id)) {
                $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'The provided wallet id is invalid',
                    )
                );
                
                $this->_redirect($this->formatURL('/wallets/index/'));
            }
            
            
            
            if (empty($row)) {
                $this->_helper->FlashMessenger(
                    array(
                        'msg-warning' => 'The requested Wallet could not be found',
                    )
                );
                
                $this->_redirect($this->formatURL('/wallets/index/'));
            }
            
            $form->populate($row);
            $this->view->item = $row;
        }
        
        $this->view->form = $form;
    }
     public function viewAction(){

        $this->title = 'Wallet Details';
        $walletModel = new GlobalPurse();
        $id = $this->_getParam('id');
        $this->view->item = $walletModel->findById($id);
        $this->view->backlink = Zend_Controller_Front::getInstance()->getBaseUrl() . '/wallets/index';
        
    }
    
      public function bindmccAction(){
        $this->title = 'Bind wallet to MCC';
        $walletModel = new GlobalPurse();
        $walletId = $this->_getParam('id');
        $formData = $this->_request->getPost();
                
          if ($this->getRequest()->isPost()) {
                    try {
                        $walletModel->bindWalletToMCC($formData['reqid'],$walletId);
                        $this->_helper->FlashMessenger(
                                array(
                                    'msg-success' => 'Allowed MCCs updated'
                                )
                        );
                        $this->_redirect($this->formatURL('/wallets/index/'));
                    } catch (Exception $e) {
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                        $this->_helper->FlashMessenger(
                                array(
                                    'msg-error' => $e->getMessage(),
                                )
                        );
                    }
               
        }
        $mccBinding = $walletModel->getWalletMCCArray($walletId);
        $this->view->wallet = $walletModel->findById($walletId);
        $this->view->mccBinding = $mccBinding;
        $this->view->paginator = $walletModel->mcclist();
        $this->view->backlink = Zend_Controller_Front::getInstance()->getBaseUrl() . '/wallets/index';
        
    }
    
     public function viewbindmccAction(){

        $this->title = 'Allowed MCC Details';
        $walletModel = new GlobalPurse();
        $id = $this->_getParam('id');
        $this->view->wallet = $walletModel->findById($id);
        $this->view->paginator = $walletModel->getWalletMCCList($id);
        $this->view->backlink = Zend_Controller_Front::getInstance()->getBaseUrl() . '/wallets/view?id='.$id;
        
    }
    
}