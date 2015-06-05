<?php
/**
 * Allow the admins to manage critical info, users, groups, permissions, etc.
 *
 * @category backoffice
 * @package backoffice_controllers
 * @copyright company
 */

class CorporatesController extends App_Operation_Controller
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
        $this->title = 'Corporates';
        $data = array();
        
        $corporateUserModel = new CorporateUser();
        
        $data['searchCriteria'] = $this->_getParam('searchCriteria');
        $data['keyword'] = $this->_getParam('keyword');
        $data['sub'] = $this->_getParam('sub');
        
        $form = new CorporateSearchForm(array('action' => $this->formatURL('/corporates/index'),
            'method' => 'POST',
        ));
        
        if ($data['sub'] != '')
        {
            $this->view->paginator = $corporateUserModel->getAllUsers($data, $this->_getPage());
            $form->populate($data);
        }
        else
        {
            $this->view->paginator = $corporateUserModel->getAllUsers($data, $this->_getParam('page'));
        }
        $this->view->form = $form;
    }
    
    
      /**
     * Allows the user to add another privilege in the application
     *
     * @access public
     * @return void
     */
    public function addAction(){
        $this->title = 'Add New Corporate';
        $insertlog = new Log();
        $form = new CorporateForm();
        $state = new CityList();
        $corporateModel = new Corporates();
        $user = Zend_Auth::getInstance()->getIdentity();
        $formData  = $this->_request->getPost();
        
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
                $stateName =  $state->getStateName($formData['state']);
                $row = $form->getValues();
                $row['name'] = $formData['name'];
                $row['ecs_corp_id'] = $formData['ecs_corp_id'];
                $row['ip'] = $corporateModel->formatIpAddress(Util::getIP());
                $row['state'] = $stateName;
                $row['city'] = $formData['city'];
                $row['pincode'] = $formData['pincode'];
                $row['address'] = $formData['address'];
                $row['contact_number'] = $formData['contact_number'];
                $row['contact_email'] = $formData['contact_email'];
                $row['by_ops_id'] = $user->id;
                $row['date_created'] =  new Zend_Db_Expr('NOW()');
                $checkCorporateName =  $corporateModel->checkCorporateName($row['name']);
                $checkCorporateECSid = $corporateModel->checkCorporateECSid($row['ecs_corp_id']);
                if($checkCorporateName){
                    if($checkCorporateECSid){
                $corporateId = $corporateModel->save($row);
                
                //Insert into Corporate Log table
                $data['corporate_id'] = $corporateId;
                $data['name'] = $formData['name'];
                $data['ecs_corp_id'] = $formData['ecs_corp_id'];
                $data['ip'] = $corporateModel->formatIpAddress(Util::getIP());
                $data['state'] = $stateName;
                $data['city'] = $formData['city'];
                $data['pincode'] = $formData['pincode'];
                $data['address'] = $formData['address'];
                $data['contact_number'] = $formData['contact_number'];
                $data['contact_email'] = $formData['contact_email'];
                $data['by_ops_id'] = $user->id;
                $data['date_created'] =  new Zend_Db_Expr('NOW()');
                $insertlog->insertlog($data,DbTable::TABLE_LOG_CORPORATE);
                $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'The Corporate was successfully added',
                    )
                );
                $this->_redirect($this->formatURL('/corporate/index/'));
                    }
                    else
                    {
                     $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'The Corporate ECS id already exists',
                    )
                );     
                    }
                }
                else
                {
                   $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'The Corporate name already exists',
                    )
                );  
                   
                }
              
            $res = $form->getValues();
            $res['pin'] = $res['pincode'];
            $res['city_name'] = $res['city'];
            $form->populate($res);  
            
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
    public function editAction(){

        $this->title = 'Edit Corporate';
        $id = $this->_getParam('id');
        $form = new CorporateForm();
        $state = new CityList();
        $corporateModel = new Corporates();
        $user = Zend_Auth::getInstance()->getIdentity();
        $row = $corporateModel->findById($id);
       
        $res['ip'] = $corporateModel->formatIpAddress(Util::getIP());
        $res['by_ops_id'] = $user->id;
      
        $insertlog = new Log();
        if($this->getRequest()->isPost()){
             
              
                $formData  = $this->_request->getPost();
                //Update
                $stateName =  $state->getStateName($formData['state']);
                $data['state'] = $stateName;
                $data['city'] = $formData['city'];
                $data['pincode'] = $formData['pincode'];
                $data['address'] = $formData['address'];
                $data['contact_number'] = $formData['contact_number'];
                $data['contact_email'] = $formData['contact_email'];
                $data['by_ops_id'] = $user->id;
                $corporateModel->updateCorporate($id , $data);
                
                $data['corporate_id'] = $id ;  
                $data['name'] = $formData['name']; 
                $data['ecs_corp_id'] = $formData['ecs_corp_id'];
                
                
                //Insert into Log
                $insertlog->insertlog($data,DbTable::TABLE_LOG_CORPORATE);
               
                
                
                
                $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'The Corporate details were successfully edited',
                    )
                );
                
                //Regenerate Flag and Flippers
                App_FlagFlippers_Manager::save();
                
                $this->_redirect($this->formatURL('/corporate/index/'));
            
        }else{
            
            if (!is_numeric($id)) {
                $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'The provided corporate_id is invalid',
                    )
                );
                
                $this->_redirect($this->formatURL('/corporate/index/'));
            }
            
            
            
            if (empty($row)) {
                $this->_helper->FlashMessenger(
                    array(
                        'msg-warning' => 'The requested Corporate could not be found',
                    )
                );
                
                $this->_redirect($this->formatURL('/corporate/index/'));
            }
            $res = $row->toArray();
            $res['pin'] = $res['pincode'];
            $res['city_name'] = $res['city'];
            $res['state'] = $state->getStateCode($res['state']); 
            $form->populate($res);
            $this->view->item = $row;
        }
        
        $this->view->form = $form;
    }
    
 public function viewAction() {
        $this->title = 'Corporate Details';
        $corpModel = new CorporateUser();
        $approveCorpModel = new Corporatelimit();
        $corplimitModel = new Corporatelimits();
        $id = $this->_getParam('id');
        $row = $corpModel->findDetailsById($id);
        $documents = $corpModel->corpDoclist($id);

        $this->view->document = $documents->toArray();
         
        $this->view->item = $row;
        $corpBalance = $corpModel->getCorpBalance($id);
        $this->view->balance = $corpBalance['amount'];
        $this->view->corporate_id = $id;
        $prodArr = $corpModel->getCorpproductDetailsAsArray($id);
        
        $arrNew = array();
        if (!empty($prodArr)) {
            foreach ($prodArr as $key => $val) {
                $arr = array();

                $arr['product_name'] = $val['product_name'];
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

        $limitArr = $corplimitModel->getCorplimitAsArray($id);

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
}