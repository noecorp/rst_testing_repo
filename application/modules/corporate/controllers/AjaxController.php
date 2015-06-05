<?php
/**
 * Default entry point in the application
 *
 * @package frontend_controllers
 * @copyright company
 */

class AjaxController extends App_Corporate_Controller
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
        
        //$this->_addCommand(new App_Command_SendEmail());
        $this->_helper->layout()->setLayout('ajax');
        //$this->_helper->layout()->setViewRender('ajax');
    }
    
    public function mobiledupAction()
    {
        
        $mobile = $this->_getParam("q",0);
        $tablename = $this->_getParam("tablename",'');
        
        if(strlen($mobile) == Mobile::$length) {
             
            //Checking Validation
            $mobileModel = new Mobile();
            
            try {
                
                print $mobileModel->checkDuplicate($mobile, $tablename);
                
            } catch (Exception $e ) {
                //var_dump($e);exit;
               // print $e->getMessage();
                print 'Mobile number exists';
                
            }
            
        } else {
            print 'Invalid Mobile Number';
        }
        
        exit;
        
    }
    
    
    public function getCityAction()
    {
        
        $stateCode = $this->_getParam("q",0);
        //$arrCity =  Util::getCity($state);
        $citylist = new CityList();
        $arrCity = $citylist->getCityByStateCode($stateCode);
        $strReturn = '<option value="">Select City</option>';
        foreach ($arrCity as $city)
        {
            $strReturn .= '<option value="'.$city.'">'.$city.'</option>';
        }
        print $strReturn;

        
        exit;
        
    }
    
    public function getPincodeAction()
    {
        
        $cityName = $this->_getParam("q",0);
        //$cityCode = $this->_getParam("q",0);
        //$arrCity =  Util::getCity($state);
        $citylist = new CityList();
        $cityCode = $citylist->getCityCode($cityName);
        $arrCity = $citylist->getPincodeList($cityCode);
        $strReturn = '<option value="">Select Pincode</option>';
        foreach ($arrCity as $pincode)
        {
            $strReturn .= '<option value="'.$pincode.'">'.$pincode.'</option>';
        }
        print $strReturn;

        
        exit;
        
    }
    
    
     
    public function emaildupAction()
    {        
        $email = $this->_getParam("q",'');
        $tablename = $this->_getParam("tablename",'');
        
        if(strlen($email) < Email::$minLength || strlen($email) > Email::$maxLength) {
             
             print 'Invalid Email Address';           
             exit;
            
        } else {
                
                //Checking Validation
                $emailModel = new Email();

                try {

                    print $emailModel->checkDuplicate($email, $tablename);
                    exit;
                } catch (Exception $e ) {
                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    print $e->getMessage();
                    exit;
                }
        }       
    }
    
    
    public function sendDownloadLinkAction()
    {
        
        
        
        $mob = $this->_getParam("mob",'');
        if($mob== '') {
            print 'Mobile number not provided';
            exit;
        }
      
           try {
               //Handling missing country code
               if(strpos($mob, '+') === false) {
                 $mob = '+'.trim($mob);
               }
               //exit($mob);
        $mvc = new App_Api_MVC_Transactions();
        $flg = $mvc->sendDownloadLink($mob);
        } catch (Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            //print $e->getMessage();exit;
        }
        //echo "<pre>";print_r($flg);
        if($flg) {
            echo "<br />Download link sent successfully<br /><br />";
        } else {
            print 'ERROR:' .$mvc->getError() . '<br />';
            echo "<br />Download link could not be sent<br />";

        }

            //echo $ecs->getError();

        
        $this->_helper->viewRenderer->setNoRender(true);
        //$this->view->render(false);
        //$viewRenderer = Zend_Controller_Action_HelperBroker::getExistingHelper('viewRenderer');
        //$viewRenderer->setNeverRender(true);
        
        exit;
        
        //$this->_helper->layout()->disableLayout();

    }
    
    
     public function arndupAction(){
        
        $arn = $this->_getParam("q",0);
        $tableName = $this->_getParam("tablename",'');         
             
        //Checking Validation
        $objValid = new Validator();
            
            try {                
                print $objValid->checkARNDuplicate(array('tablename'=>$tableName, 'arn'=>$arn));                
                exit;
                
            } catch (Exception $e ) {
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                print $e->getMessage();
                exit;
            }
      }
   public function resendAuthcodeAction(){
        $user = Zend_Auth::getInstance()->getIdentity();
        $userModel = new AgentUser();
        $dataArr = $userModel->findDetails($user->agent_code,DbTable::TABLE_AGENTS);
        print_r($dataArr);
        exit;
        $alert = new Alerts();
        $alert->sendAuthCode($dataArr,'agent');
        echo 'Mail Sent';
        exit;

    }
    
     public function getIfscAction(){
        $bank = $this->_getParam("q",0);
        $ifsc = $this->_getParam("i",0);  
      //$arrCity =  Util::getCity($state);
      	if(empty($bank)){
      		echo $strReturn = '<option value="">Select IFSC Code</option>';
      		exit;
      	}
        $ifsclist = new BanksIFSC();
			  $arrIFSC = $ifsclist->getIFSC($bank,$ifsc);
        $strReturn = '<option value="">Select IFSC Code</option>';
        foreach ($arrIFSC as $ifsc)
        {
            $strReturn .= '<option value="'.$ifsc.'">'.$ifsc.'</option>';
        }
        print $strReturn;

        
        exit;
    }
    
    public function getBankdetailsAction(){
        $ifsc = $this->_getParam("q",0);  
      
      //$arrCity =  Util::getCity($state);
        $details = new BanksIFSC();
       
        $arrDetails = $details->getDetailsByIFSC($ifsc);
        if($arrDetails=="^^^^"){
        	echo "1"; exit;
        }
        print $arrDetails;

        
        exit;
    }
    /* getRemitterRegistrationFeeAction() will return the remittance fee againt agent product assigned
     * it will accept the product id 
     */
     public function getRemitterRegistrationFeeAction(){
        $productId = $this->_getParam("q",0);  
        $agentId = $this->_getParam("agent_id",0);  
        //print $productId.'---'.$agentId; exit;
      
      if($productId>0 && $agentId>0){
            $objFeePlan = new FeePlan();
            $arrDetails = $objFeePlan->getRemitterRegistrationFee($productId, $agentId);
            $fee = isset($arrDetails['txn_flat'])?$arrDetails['txn_flat']:0;
            print $fee;
            exit;
      }
    }
    public function getStateAction(){
        $bank = $this->_getParam("q",0);
        //$arrCity =  Util::getCity($state);
        $ifsclist = new BanksIFSC();
			  $arrIFSC = $ifsclist->getStateByName($bank);
        $strReturn = '<option value="">Select State</option>';
        foreach ($arrIFSC as $state)
        {
            $strReturn .= '<option value="'.$state.'">'.$state.'</option>';
        }
        print $strReturn;

        
        exit;
    }
    
    public function getCitiesAction(){
        $bank = $this->_getParam("q",0);
        $state = $this->_getParam("r",0);  
				$ifsclist = new BanksIFSC();
				$arrCity = $ifsclist->getCityByName($bank,$state);
			  $strReturn = '<option value="">Select City</option>';
        foreach ($arrCity as $city)
        {
            $strReturn .= '<option value="'.$city.'">'.$city.'</option>';
        }
        print $strReturn;

        
        exit;
       
    }
    public function getBranchesAction(){
        $bank = $this->_getParam("q",0);
        $city = $this->_getParam("i",0);  
        $BanksIFSC = new BanksIFSC();
				$arrBranches = $BanksIFSC->getBranchesByName($bank,$city);
			  $strReturn = '<option value="">Select Branch</option>';
        foreach ($arrBranches as $branch)
        {
            $strReturn .= '<option value="'.$branch.'">'.$branch.'</option>';
        }
        print $strReturn;

        
        exit;
    }
    public function getBranchaddAction(){
        $bank = $this->_getParam("q",0);
        $branch = $this->_getParam("i",0);  
				
        $details = new BanksIFSC();
       
        $arrDetails = $details->getBranchAddress($bank,$branch);
        
        print $arrDetails;

        
        exit;
    }
     public function getbasicIfscAction(){
        $bank = $this->_getParam("q",0);  
      
      //$arrCity =  Util::getCity($state);
        $ifsclist = new BanksIFSC();
       
        $arrIFSC = $ifsclist->getIFSC($bank);
        $strReturn = '<option value="">Select IFSC</option>';
        foreach ($arrIFSC as $ifsc)
        {
            $strReturn .= '<option value="'.$ifsc.'">'.$ifsc.'</option>';
        }
        print $strReturn;

        
        exit;
    }
    
    public function getbasicBankdetailsAction(){
        $ifsc = $this->_getParam("q",0);  
      
      //$arrCity =  Util::getCity($state);
        $details = new BanksIFSC();
       
        $arrDetails = $details->getDetailsByIFSC($ifsc);
        
        print $arrDetails;

        
        exit;
    }
    
    public function getStatecityAction(){
        $pinCode = $this->_getParam("q",0);  
      
      	$citylist = new CityList();
        $strReturn = $citylist->getCityByPincode($pinCode);
        if($strReturn==""){
        	echo "1"; exit;
        }
        echo $strReturn;
        exit;
    }
    public function getBatchbydateAction(){
        $cardholderModel = new Corp_Ratnakar_Cardholders();
        $dateArr = array('start_date' => $this->_getParam("q",FALSE),'end_date' => $this->_getParam("p",FALSE),'by_corporate_id' => $this->_getParam("u",FALSE));
        $batchArr = $cardholderModel->getBatchDDByDate($dateArr);
        $strReturn = '<option value="">Select Batch Name</option>'; 

        foreach ($batchArr as $value)
        {
            $strReturn .= '<option value="'.$value.'">'.$value.'</option>';
        }
        print $strReturn;

        exit;
    }
}