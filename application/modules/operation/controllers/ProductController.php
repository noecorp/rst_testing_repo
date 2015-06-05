<?php

/**
 * Allow the admins to manage products
 *
 * @category backoffice
 * @package backoffice_controllers
 * @copyright company
 */
class ProductController extends App_Operation_Controller {

    /**
     * Overrides Zend_Controller_Action::init()
     *
     * @access public
     * @return void
     */
    public function init() {
        // init the parent
        parent::init();
    }

    public function indexAction() {
        //echo "<pre>";print_r($session);
        $this->title = 'Products';
        $bid = 0;
        $bankName = '';
        if ($this->_getParam('bid') > 0) {
            $bid = $this->_getParam('bid');
            $bankModel = new Banks();
            $bankArr = $bankModel->findById($bid);
            $bankName = $bankArr['name'];
            $this->view->bankName = $bankName;
        }

        $productModel = new Products();

        $bindProducts = $productModel->getBindProducts();

        $this->view->paginator = $productModel->findAllProducts($this->_getPage(), NULL, FALSE, $bid);
        $this->view->bid = $bid;

        $this->view->bindProducts = $bindProducts;
    }


    /**
     * Edits an existing privilege
     *
     * @access public
     * @return void
     */
    public function editAction() {
        $this->title = 'Edit Product';
        $id = $this->_getParam('id');
        $this->view->bid = $this->_getParam('bid');

        $insertlog = new Log();
        $bankid = $this->_getParam('urlbid');

        $urlbid = isset($bankid) ? $bankid : 0;

        $form = new ProductForm();
        $productModel = new Products();
        $user = Zend_Auth::getInstance()->getIdentity();
        $row = $productModel->findById($id);
        $form->getElement('bid')->setValue($row['bank_id']);
        $form->getElement('urlBid')->setValue($urlbid);

        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {

                $res['product_id'] = $row['id'];
                $res['by_ops_id'] = $user->id;
                $res['ip'] = $row['ip'];
                $res['name'] = $row['name'];
                $res['bank_id'] = $row['bank_id'];
                $res['description'] = $row['description'];
                $res['currency'] = $row['currency'];
                $res['ecs_product_code'] = $row['ecs_product_code'];
                $res['program_type'] = $row['program_type'];
                $res['status'] = $row['status'];
                $insertlog->insertlog($res, DbTable::TABLE_LOG_PRODUCTS);
                $formdata = $form->getValues();

                $formdata['by_ops_id'] = $user->id;
                $formdata['ip'] = $productModel->formatIpAddress(Util::getIP());
                $productModel->save($formdata);

                $this->_helper->FlashMessenger(
                        array(
                            'msg-success' => 'The product was successfully updated',
                        )
                );

                //Regenerate Flag and Flippers
                App_FlagFlippers_Manager::save();
                if ($urlbid > 0) {

                    $this->_redirect($this->formatURL('/product/index?bid=' . $row['bank_id']));
                } else {

                    $this->_redirect($this->formatURL('/product/index/'));
                }
            }
        } else {
            $id = $this->_getParam('id');

            if (!is_numeric($id)) {
                $this->_helper->FlashMessenger(
                        array(
                            'msg-error' => 'The provided product_id is invalid',
                        )
                );

                $this->_redirect($this->formatURL('/product/index/'));
            }



            if (empty($row)) {
                $this->_helper->FlashMessenger(
                        array(
                            'msg-warning' => 'The requested product could not be found',
                        )
                );

                $this->_redirect($this->formatURL('/product/index/'));
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
    public function limitAction() {

        $pid = ($this->_getParam('pid') > 0) ? $this->_getParam('pid') : 0;
        $plid = ($this->_getParam('plid') > 0) ? $this->_getParam('plid') : 0;
        if ($pid == 0 && $plid == 0) {
            $this->_redirect($this->formatURL('/product/index/'));
        }

        if ($plid > 0) {
            $productModel = new Productlimit();
            $row = $productModel->findByPlId($plid);
        } else {
            $productModel = new Products();
            $row = $productModel->findById($pid);
        }
        //echo "<pre>";print_r($row);exit;
        switch ($row['program_type']) {
            case PROGRAM_TYPE_REMIT:
                $this->_redirect($this->formatURL('/remit_productlimit/index?pid=' . $pid . '&plid=' . $plid));
                break;

            case PROGRAM_TYPE_MVC:
            default:
                $this->_redirect($this->formatURL('/mvc_productlimit/index?pid=' . $pid . '&plid=' . $plid));
                break;
        }
    }

    /**
     * Allows the user to view Product Details
     * @access public
     * @return void
     */
    public function viewAction() {
        $this->title = 'Product Details';
        $product = new Products();
        $pid = $this->_getParam('pid');
        if (!is_numeric($pid)) {
            $this->_helper->FlashMessenger(
                    array(
                        'error' => 'The product id you provided is invalid',
                    )
            );

            $this->_redirect($this->formatURL('/product/index/'));
        }

        $bindProduct = $product->findById($pid);
        $this->view->item = $bindProduct->toArray();

        //Select Purces for Product
        $this->view->productPurses = array();
        if (!empty($this->view->item['id'])) {
            $masterPurse = new MasterPurse();
            $this->view->productPurses = $masterPurse->getPurseDetailsbyProductId($this->view->item['id']);
        }
        
         //Select Customer Limit for Product
        $this->view->customerLimit = array();
        if (!empty($this->view->item['id'])) {
            $productCustLimitModel = new ProductCustomerLimits();
            $this->view->customerLimit = $productCustLimitModel->getLimitDetailsbyProductId($this->view->item['id']);
       
        }
    }

    
    /**
     * Edits an existing Purse details
     *
     * @access public
     * @return void
     */
    public function editpurseAction() {
        $this->title = 'Edit Purse';
        $id = $this->_getParam('id');
        $m = new App\Messaging\System\Operation();
        $purseModel = new MasterPurse();
        $productModel = new Products();
        

        $form = new PurseDetailsForm();
       
        $user = Zend_Auth::getInstance()->getIdentity();
        $row = $purseModel->findById($id);
        $prodDetails= $productModel->findById($row->product_id);
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {

    
                $formdata = $form->getValues();
                $dataArr = array();
//                $dataArr['name'] = $formdata['name'];
                $dataArr['description'] = $formdata['description'];
                $dataArr['max_balance'] = $formdata['max_balance'];
                $dataArr['load_channel'] = $formdata['load_channel'];
                $dataArr['load_validity_day'] = $formdata['load_validity_day'];
                $dataArr['load_validity_hr'] = $formdata['load_validity_hr'];
                $dataArr['load_validity_min'] = $formdata['load_validity_min'];
                $dataArr['load_min'] = $formdata['load_min'];
                $dataArr['load_max'] = $formdata['load_max'];
                $dataArr['load_max_cnt_daily'] = $formdata['load_max_cnt_daily'];
                $dataArr['load_max_val_daily'] = $formdata['load_max_val_daily'];
                $dataArr['load_max_cnt_monthly'] = $formdata['load_max_cnt_monthly'];
                $dataArr['load_max_val_monthly'] = $formdata['load_max_val_monthly'];
                $dataArr['load_max_cnt_yearly'] = $formdata['load_max_cnt_yearly'];
                $dataArr['load_max_val_yearly'] = $formdata['load_max_val_yearly'];
                $dataArr['txn_restriction_type'] = $formdata['txn_restriction_type'];
                $dataArr['txn_upload_list'] = $formdata['txn_upload_list'];
                $dataArr['txn_min'] = $formdata['txn_min'];
                $dataArr['txn_max'] = $formdata['txn_max'];
                $dataArr['txn_max_cnt_daily'] = $formdata['txn_max_cnt_daily'];
                $dataArr['txn_max_val_daily'] = $formdata['txn_max_val_daily'];
                $dataArr['txn_max_cnt_monthly'] = $formdata['txn_max_cnt_monthly'];
                $dataArr['txn_max_val_monthly'] = $formdata['txn_max_val_monthly'];
                $dataArr['txn_max_cnt_yearly'] = $formdata['txn_max_cnt_yearly'];
                $dataArr['txn_max_val_yearly'] = $formdata['txn_max_val_yearly'];
                $dataArr['date_start'] = new Zend_Db_Expr('NOW()');
                $dataArr['date_updated'] = new Zend_Db_Expr('NOW()') ;
                $dataArr['by_ops_id'] = $user->id;
               try{
                
                $purseModel->update($dataArr,"id = $id");
                   
                $logArr = array();
                $logArr['purse_master_id'] = $id;
                $logArr['name'] = $row->name;
                $logArr['description'] = $row->description;
                $logArr['max_balance'] = $row->max_balance;
                $logArr['load_channel'] = $row->load_channel;
                $logArr['load_validity_day'] = $row->load_validity_day;
                $logArr['load_validity_hr'] = $row->load_validity_hr;
                $logArr['load_validity_min'] = $row->load_validity_min;
                $logArr['load_min'] = $row->load_min;
                $logArr['load_max'] = $row->load_max;
                $logArr['load_max_cnt_daily'] = $row->load_max_cnt_daily;
                $logArr['load_max_val_daily'] = $row->load_max_val_daily;
                $logArr['load_max_cnt_monthly'] = $row->load_max_cnt_monthly;
                $logArr['load_max_val_monthly'] = $row->load_max_val_monthly;
                $logArr['load_max_cnt_yearly'] = $row->load_max_cnt_yearly;
                $logArr['load_max_val_yearly'] = $row->load_max_val_yearly;
                $logArr['txn_restriction_type'] = $row->txn_restriction_type;
                $logArr['txn_upload_list'] = $row->txn_upload_list;
                $logArr['txn_min'] = $row->txn_min;
                $logArr['txn_max'] = $row->txn_max;
                $logArr['txn_max_cnt_daily'] = $row->txn_max_cnt_daily;
                $logArr['txn_max_val_daily'] = $row->txn_max_val_daily;
                $logArr['txn_max_cnt_monthly'] = $row->txn_max_cnt_monthly;
                $logArr['txn_max_val_monthly'] = $row->txn_max_val_monthly;
                $logArr['txn_max_cnt_yearly'] = $row->txn_max_cnt_yearly;
                $logArr['txn_max_val_yearly'] = $row->txn_max_val_yearly;
                $logArr['date_start'] = $row->date_start;
                $logArr['date_end'] = new Zend_Db_Expr('NOW()');
                $logArr['date_updated'] = new Zend_Db_Expr('NOW()') ;
                $logArr['by_ops_id'] = $user->id;        
                $logArr['status'] = $row->status;    
                

                $purseModel->insertLog($logArr);
                
                $oldArr = array(
                'Name' => $row->name,
                'Description' => $row->description,
                'Max Balance' => $row->max_balance,
                'Load Channel' => $row->load_channel,
                'Load Validity Day' => $row->load_validity_day,
                'Load Validity Hour' => $row->load_validity_hr,
                'Load Validity Minute' => $row->load_validity_min,
                'Minimum Value per Load' => $row->load_min,
                'Maximum Value per Load' => $row->load_max,
                'Max no. of Loads per Day' => $row->load_max_cnt_daily,
                'Max Load Amount per Day' => $row->load_max_val_daily,
                'Max no. of Loads per Month' => $row->load_max_cnt_monthly,
                'Max Load Amount per Month' => $row->load_max_val_monthly,
                'Max no. of Loads per Year' => $row->load_max_cnt_yearly,
                'Max Load Amount per Year' => $row->load_max_val_yearly,
                'Restriction Type' => $row->txn_restriction_type,
                'Upload List' => $row->txn_upload_list,
                'Minimum Value of Txn' => $row->txn_min,
                'Maximum Value of Txn' => $row->txn_max,
                'Max no. of Txns per Day' => $row->txn_max_cnt_daily,
                'Max Txn Amount per Day' => $row->txn_max_val_daily,
                'Max no. of Txns per Month' => $row->txn_max_cnt_monthly,
                'Max Txn Amount per Month' => $row->txn_max_val_monthly,
                'Max no. of Txns per Year' => $row->txn_max_cnt_yearly,
                'Max Txn Amount per Year' => $row->txn_max_val_yearly,
                  );
                 
                 $newArr = array(
                'Name' => $formdata['name'],
                'Description' => $formdata['description'],
                'Max Balance' => $formdata['max_balance'],
                'Load Channel' => $formdata['load_channel'],
                'Load Validity Day' => $formdata['load_validity_day'],
                'Load Validity Hour' => $formdata['load_validity_hr'],
                'Load Validity Minute' => $formdata['load_validity_min'],
                'Minimum Value per Load' => $formdata['load_min'],
                'Maximum Value per Load' => $formdata['load_max'],
                'Max no. of Loads per Day' => $formdata['load_max_cnt_daily'],
                'Max Load Amount per Day' => $formdata['load_max_val_daily'],
                'Max no. of Loads per Month' => $formdata['load_max_cnt_monthly'],
                'Max Load Amount per Month' => $formdata['load_max_val_monthly'],
                'Max no. of Loads per Year' => $formdata['load_max_cnt_yearly'],
                'Max Load Amount per Year' => $formdata['load_max_val_yearly'],
                'Restriction Type' => $formdata['txn_restriction_type'],
                'Upload List' => $formdata['txn_upload_list'],
                'Minimum Value of Txn' => $formdata['txn_min'],
                'Maximum Value of Txn' => $formdata['txn_max'],
                'Max no. of Txns per Day' => $formdata['txn_max_cnt_daily'],
                'Max Txn Amount per Day' => $formdata['txn_max_val_daily'],
                'Max no. of Txns per Month' => $formdata['txn_max_cnt_monthly'],
                'Max Txn Amount per Month' => $formdata['txn_max_val_monthly'],
                'Max no. of Txns per Year' => $formdata['txn_max_cnt_yearly'],
                'Max Txn Amount per Year' => $formdata['txn_max_val_yearly'],
                             );
                        // SEND NOTIFICATION TO ADMIN USERS
                    
                  $mailData['email'] = ADMIN_EMAIL_IDS;
                  $mailData['product_name'] = $prodDetails['name'];
                  $mailData['limit_category'] = 'PURSE: '.$row['name'];
                  $mailData['param_name'] = 'NA';
                  $mailData['old_value'] = $oldArr;
                  $mailData['new_value'] = $newArr;
                  $m->limitUpdates($mailData); 
                $this->_helper->FlashMessenger(
                        array(
                            'msg-success' => 'The purse was successfully updated',
                        )
                );


                    $this->_redirect($this->formatURL('/product/view?pid='.$row->product_id));
                
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
                            'msg-error' => 'The provided purse_id is invalid',
                        )
                );

                $this->_redirect($this->formatURL('/product/view/'));
            }



            if (empty($row)) {
                $this->_helper->FlashMessenger(
                        array(
                            'msg-warning' => 'The requested purse could not be found',
                        )
                );

                $this->_redirect($this->formatURL('/product/view/'));
            }
            $dateToday = date('d-m-Y');
            $dateTmr = date('d-m-Y', strtotime("$dateToday, +1 day"));
        
//            $row->date_start = $dateTmr;
            $form->populate($row->toArray());
           
        }
        
         $this->view->item = $row;
        $this->view->form = $form;
    }
     
  
    
    
      /**
     * Allows the user to view Product purse Details
     * @access public
     * @return void
     */
    public function purseviewAction() {
        $this->title = 'Purse Details';
        $id = $this->_getParam('id');
        if (!is_numeric($id)) {
            $this->_helper->FlashMessenger(
                    array(
                        'error' => 'The purse id you provided is invalid',
                    )
            );

            $this->_redirect($this->formatURL('/product/index/'));
        }

        //Select Purces for Product
        $masterPurse = new MasterPurse();
        $this->view->item = $masterPurse->getPurseDetailsbyPurseId($id);
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
        $productCustLimitModel = new ProductCustomerLimits();
        $productModel = new Products();
       
        $datetime = new DateTime('tomorrow');
        $dateToday = date('d-m-Y');
        $dateTmr = date('d-m-Y', strtotime("$dateToday, +1 day"));
        
        $form = new CustomerLimitDetailForm();
        
        $user = Zend_Auth::getInstance()->getIdentity();
        $row = $productCustLimitModel->findById($id);
        $prodDetails = $productModel->findById($row->product_id);
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
                $productCustLimitModel->update($dataArr,"id = $id");
                   
                $logArr = array();
                $logArr['customer_limit_id'] = $id;
                $logArr['name'] = $row->name;
                $logArr['bank_id'] = $row->bank_id;
                $logArr['product_id'] = $row->product_id;
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
                $productCustLimitModel->insertLog($logArr);

                
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
                  $mailData['product_name'] = $prodDetails['name'];
                  $mailData['limit_category'] = 'CUSTOMER LIMIT: '.$row->name;
                  $mailData['param_name'] = 'NA';
                  $mailData['old_value'] = $oldArr;
                  $mailData['new_value'] = $newArr;
                  $m->limitUpdates($mailData); 
                
                $this->_helper->FlashMessenger(
                        array(
                            'msg-success' => 'The customer limit was successfully updated',
                        )
                );
                    $this->_redirect($this->formatURL('/product/view?pid='.$row->product_id));
                
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
                            'msg-error' => 'The provided purse_id is invalid',
                        )
                );

                $this->_redirect($this->formatURL('/product/view/'));
            }



            if (empty($row)) {
                $this->_helper->FlashMessenger(
                        array(
                            'msg-warning' => 'The requested purse could not be found',
                        )
                );

                $this->_redirect($this->formatURL('/product/view/'));
            }
            $dateToday = date('d-m-Y');
            $dateTmr = date('d-m-Y', strtotime("$dateToday, +1 day"));
        
//            $row->date_start = $dateTmr;
            $form->populate($row->toArray());
           
        }
        
         $this->view->item = $row;
        $this->view->form = $form;
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

            $this->_redirect($this->formatURL('/product/index/'));
        }

        //Select customer Limit for Product
        $productCustLimitModel = new ProductCustomerLimits();
        $this->view->item = $productCustLimitModel->getLimitDetailsbyPurseId($id);
    }
  
}