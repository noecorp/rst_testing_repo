<?php
/**
 * Default entry point in the application
 *
 * @package frontend_controllers
 * @copyright company
 */

class AjaxController extends App_Operation_Controller
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
        
      $this->_helper->layout()->setLayout('ajax');
    }
    
    public function getCityAction()
    {
        $stateCode = $this->_getParam("q",0);
        $allCities = $this->_getParam("c",1);
        //$arrCity =  Util::getCity($state);
        $citylist = new CityList();
        $arrCity = $citylist->getCityByStateCode($stateCode);
        
        if ($allCities == 'all'){
            $strReturn = '<option value="">All Cities</option>';
        }
        else{
           $strReturn = '<option value="">Select City</option>'; 
        }
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
    
    
    public function getFeeAction()
    {
        
        $bank = $this->_getParam("q",0);
        
        $product = new Approveagent();
        $arrProduct = $product->getFeeBybankId($bank);
        $strReturn = '<option value="">Select Fee</option>';
        foreach ($arrProduct as $key => $value)
        {
            $strReturn .= '<option value="'.$key.'">'.$value.'</option>';
        }
        print $strReturn;

        
        exit;
        
    }
    
     public function getProductAction()
    {
       
        $bank = $this->_getParam("q",0);
        
        $product = new Approveagent();
        $arrProduct = $product->getproductByBankId($bank);
        $strReturn = '<option value="">Select Product</option>';
        foreach ($arrProduct as $key => $value)
        {
            $strReturn .= '<option value="'.$key.'">'.$value.'</option>';
        }
        print $strReturn;

        
        exit;
        
    }
    
     public function getProductlimitAction()
    {
       
        $prd = $this->_getParam("q",0);
        
        $product = new Approveagent();
        $productModel = new Products();
        $arrProductlimit = $product->getlimitByproductId($prd);
        $prdDetails = $productModel->getProductInfo($prd);                
        $strReturn = '<option value="">Select Product Limit</option>';
        
        if($prdDetails['program_type'] != PROGRAM_TYPE_DIGIWALLET) {
            foreach ($arrProductlimit as $key => $value)
            {
                $strReturn .= '<option value="'.$key.'">'.$value.'</option>';
            }
        }
        print $strReturn;

        
        exit;
        
    }
    
     public function getBankbyproductAction()
    {
       
        $prd = $this->_getParam("q",0);
        
        $product = new Approveagent();
        $arrProductlimit = $product->getBankByProduct($prd);
        $strReturn = '<option value="">Select Bank</option>';
        foreach ($arrProductlimit as $key => $value)
        {
            $strReturn .= '<option value="'.$key.'">'.$value.'</option>';
        }
        print $strReturn;

        
        exit;
        
    }
    
    public function getAgentproductAction()
    {
       
        $id = $this->_getParam("q",0);
        
        $product = new Approveagent();
        $arrProductlimit = $product->getProductById($id);
        $strReturn = '<option value="">Select Product</option>';
        foreach ($arrProductlimit as $key => $value)
        {
            $strReturn .= '<option value="'.$key.'">'.$value.'</option>';
        }
        print $strReturn;

        
        exit;
        
    }
    
   public function resendAuthcodeAction(){
        $user = Zend_Auth::getInstance()->getIdentity();
        $userModel = new OperationUser();
        $dataArr = $userModel->findDetails($user->username,DbTable::TABLE_OPERATION_USERS);
        
        $alert = new Alerts();
        $alert->sendAuthCode($dataArr,'operation');
        print 'Mail Sent';
        
        exit;

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
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                print $e->getMessage();
            }
            
        } else {
            print 'Invalid Mobile Number';
        }
        
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
        //exit("HERE");
        
        
        $mob = $this->_getParam("mob",'');
        if($mob== '') {
            print 'Mobile number not provided';
            exit;
        }
        
        //exit($mob);
        try {
        
         //Mobile Validation
            //Check is mobile valid
            //
        //Send Download Link    
       // $mvc = new App_Api_MVC();
        //$flg = $mvc->sendDownloadLink($mob);
        //$flg = $mvc->sendDownloadLink($mob);
            $flg = true;
        
        } catch (Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            print $e->getMessage();
        }
        //echo "<pre>";print_r($flg);
        if($flg) {
            echo "Download link sent successfully.";
        } else {
            echo "Download link could not be sent.";
            //echo $ecs->getError();

        }
        $this->_helper->viewRenderer->setNoRender(true);
        //$this->view->render(false);
        //$viewRenderer = Zend_Controller_Action_HelperBroker::getExistingHelper('viewRenderer');
        //$viewRenderer->setNeverRender(true);
        
        exit;
        
        //$this->_helper->layout()->disableLayout();

    }
    public function getIfscAction(){
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
    
    public function getBankdetailsAction(){
        $ifsc = $this->_getParam("q",0);  
      
      //$arrCity =  Util::getCity($state);
        $details = new BanksIFSC();
       
        $arrDetails = $details->getDetailsByIFSC($ifsc);
        
        print $arrDetails;

        
        exit;
    }
     public function arndupAction(){
        
        $arn = $this->_getParam("q",0);
        $tablename = $this->_getParam("tablename",'');         
             
        //Checking Validation
        $objValid = new Validator();
            
            try {                
                print $objValid->checkARNDuplicate(array('tablename'=>$tablename, 'arn'=>$arn));                
                exit;
                
            } catch (Exception $e ) {
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                print $e->getMessage();
                exit;
            }
      }
      
      public function getProgramproductsAction()
    {
       
        $bank = $this->_getParam("q",0);
        $programType = $this->_getParam("p",0);
        
        $product = new Products();
        $arrProduct = $product->getBankProgramProducts($bank, $programType);
        $strReturn = '<option value="">All Products</option>';
        foreach ($arrProduct as $key => $value)
        {
            $strReturn .= '<option value="'.$key.'">'.$value.'</option>';
        }
        print $strReturn;

        
        exit;
        
    }
      
       public function getAgentsdropdownAction()
    {
        $bank = new Banks();
        $bankProduct = $bank->getProductsByBankUnicode($this->_getParam("q",0));
       
        $objAU = new AgentUser();
        $str = STATUS_UNBLOCKED."', '".STATUS_BLOCKED."', '".STATUS_LOCKED;
        $agentsArr = $objAU->getAgentsForDD(array('status'=> $str, 'enroll_status'=>ENROLL_APPROVED_STATUS, 'agent_details_status'=>STATUS_ACTIVE,'bank_products' => $bankProduct  ));
        if($this->_getParam("p",FALSE) == TRUE){
           $strReturn = '<option value="">Select All</option>'; 
        }else{
           $strReturn = '<option value="">Select Agent</option>'; 
        }
        
        foreach ($agentsArr as $key => $value)
        {
            $strReturn .= '<option value="'.$key.'">'.$value.'</option>';
        }
        print $strReturn;

        exit;
        
    }
    
    /**
     * get agent list on the basis of bank univode and program type
     */
      public function getProgramagentsdropdownAction()
    {
        $bank = new Banks();
        $bankProduct = $bank->getProductsByBankUnicodeProgram($this->_getParam("q",0),$this->_getParam("pt",0),$this->_getParam("p",0));
       
        $objAU = new AgentUser();
        $str = STATUS_UNBLOCKED."', '".STATUS_BLOCKED."', '".STATUS_LOCKED;
        $agentsArr = $objAU->getAgentsForDD(array('status'=> $str, 'enroll_status'=>ENROLL_APPROVED_STATUS, 'agent_details_status'=>STATUS_ACTIVE,'bank_products' => $bankProduct  ));
        if($this->_getParam("p",FALSE) == TRUE){
           $strReturn = '<option value="">Select All</option>'; 
        }else{
           $strReturn = '<option value="">Select Agent</option>'; 
        }
        
        foreach ($agentsArr as $key => $value)
        {
            $strReturn .= '<option value="'.$key.'">'.$value.'</option>';
        }
        print $strReturn;

        exit;
        
    }
    /**
     * 
     */
      public function getBatchnameAction()
    {
        $corpLoadRequestModel = new Corp_Ratnakar_Cardload();
        
        $batchArr = $corpLoadRequestModel->getBatchName($this->_getParam("q",FALSE));
        $strReturn = '<option value="">Select Batch Name</option>'; 
        
        foreach ($batchArr as $value)
        {
            $batchName = explode("_",$value['batch_name']);
            $strReturn .= '<option value="'.$value['batch_name'].'">'.$value['batch_name'].'</option>';
        }
        print $strReturn;

        exit;
        
    }   
      public function getBankproductsAction()
    {
        $productModel = new Products();
        
        $prodArr = $productModel->getBankProductsByUnicodeDD($this->_getParam("q",FALSE));
        $strReturn = '<option value="">Select Product</option>'; 
        
        foreach ($prodArr as $value)
        {
            $strReturn .= '<option value="'.$value['id'].'">'.$value['name'].'</option>';
        }
        print $strReturn;

        exit;
        
    } 
    
    public function getBankproductscommonAction()
    {
        $productModel = new Products();
        $program_type = '';
        if($this->_getParam("c")){
            $program_type = Util::getProgramTypeArray();
        }
        
        $prodArr = $productModel->getBankProductsByUnicodeDD($this->_getParam("q",FALSE), 'common', $program_type);
        
        if($this->_getParam("p")){
            $strReturn = '<option value="">Select Product</option>'; 
        }
        else{
        $strReturn = '<option value="0">All Products</option>'; 
        }
        foreach ($prodArr as $value)
        {
            $strReturn .= '<option value="'.$value['id'].'">'.$value['name'].'</option>';
        }
        print $strReturn;

        exit;
        
    } 
    /*
     * Get limit of the products of relevent Bank as well program 
     */
    public function getBankproductsprogramcommonAction()
    {
        $productModel = new Products();
        
        $prodArr = $productModel->getBankProductsProgramByUnicodeDD($this->_getParam("q",FALSE),$this->_getParam("pt",FALSE), 'common');
        
        if($this->_getParam("p")){
            $strReturn = '<option value="">Select Product</option>'; 
        }
        else{
        $strReturn = '<option value="0">All Products</option>'; 
        }
        
        foreach ($prodArr as $value)
        {
            $strReturn .= '<option value="'.$value['unicode'].'">'.$value['name'].'</option>';
        }
        print $strReturn;

        exit;
        
    }
       public function getBatchnameamulAction()
    {
        $corpLoadRequestModel = new Corp_Kotak_Cardload();
        
        $batchArr = $corpLoadRequestModel->getBatchName($this->_getParam("q",FALSE));
        $strReturn = '<option value="">Select Batch Name</option>'; 
        
        foreach ($batchArr as $value)
        {
            //$batchName = explode("_",$value['batch_name']);
            $strReturn .= '<option value="'.$value['batch_name'].'">'.$value['batch_name'].'</option>';
        }
        print $strReturn;

        exit;
        
    }   
    
     
    public function getBatchbydateAction(){
        $cardholderModel = new Corp_Ratnakar_Cardholders();
        $dateArr = array('start_date' => $this->_getParam("q",FALSE),'end_date' => $this->_getParam("p",FALSE));
        $batchArr = $cardholderModel->getBatchDDByDate($dateArr);
        $strReturn = '<option value="">Select Batch Name</option>'; 

        foreach ($batchArr as $value)
        {
            $strReturn .= '<option value="'.$value.'">'.$value.'</option>';
        }
        print $strReturn;

        exit;

    }
    
       public function getPincodeListByStateAction(){
        $stateCode = $this->_getParam("q",0);  
        $citylist = new CityList();
        $arrPin = $citylist->getPincodeByState($stateCode);
        $strReturn = '<option value="">Select Pincode</option>';
        foreach ($arrPin as $pin)
        {
            $strReturn .= '<option value="'.$pin.'">'.$pin.'</option>';
        }
        print $strReturn;

        
        exit;
         
     }
     
       public function getBatchnamensdcAction()
    {
        $corpLoadRequestModel = new Corp_Boi_Cardload();
        
        $batchArr = $corpLoadRequestModel->getBatchName($this->_getParam("q",FALSE));
        $strReturn = '<option value="">Select Batch Name</option>'; 
        
        foreach ($batchArr as $value)
        {
            $batchName = explode("_",$value['batch_name']);
            $strReturn .= '<option value="'.$value['batch_name'].'">'.$batchName[0].'</option>';
        }
        print $strReturn;

        exit;
        
    } 
    
    
     
    public function getPurseAction()
    {
        $purseList = new MasterPurse();
        $purseListOptions = $purseList->getPurseList($this->_getParam("q",FALSE));
        //$purseListOptions = $purseList->getPurseDetailsbyProductIdSql($this->_getParam("q",FALSE),'name');
        
        foreach ($purseListOptions as $key => $value)
        {
            //$purseListOptions = explode("_",$value['name']);
           $strReturn .= '<option value="'.$key.'">'.$value.'</option>';
        }
        print $strReturn;

        exit;
        
        
        
    }
    
    
    
    

       public function getKotakbatchnameAction()
   {
        $flagModel = new Corp_Kotak_DeliveryFlag();
        $data = array(
            'product_id' => $this->_getParam("q",FALSE)
        );
       
        $batchArr = $flagModel->getBatchNameSql($data);
        $strReturn = '<option value="">Select Batch Name</option>'; 
        
        foreach ($batchArr as $value)
        {
            $batchName = explode("_",$value['batch_name']);
            $strReturn .= '<option value="'.$value['batch_name'].'">'.$batchName[0].'</option>';
        }
        print $strReturn;

        exit;
        
    }  
    
       public function getAgentsunderdistAction()
    {
        $objAU = new Agents();
        $str = STATUS_UNBLOCKED."', '".STATUS_BLOCKED."', '".STATUS_LOCKED;
        $agentsArr = $objAU->getBCListUnderDistributor(array('status'=> $str, 'enroll_status'=>ENROLL_APPROVED_STATUS, 'product_id' => $this->_getParam("p",FALSE),'agent_id' => $this->_getParam("q",FALSE) ));
      
        $agentsArr = Util::toArray($agentsArr);
        $strReturn = '<option value="">Select BC</option><option value="all">All</option>'; 
        foreach ($agentsArr as $key )
        {
            $strReturn .= '<option value="'.$key['id'].'">'.$key['agent_name'].'</option>';
        }
        print $strReturn;

        exit;
        
    }
    
    
    
    public function getkotakBatchbydateAction(){
        $cardholderModel = new Corp_Kotak_Customers();
        $dateArr = array('start_date' => $this->_getParam("q",FALSE),'end_date' => $this->_getParam("p",FALSE),'product_id' => $this->_getParam("r",FALSE));
        $batchArr = $cardholderModel->getkotakBatchDDByDate($dateArr);
        $strReturn = '<option value="">Select Batch Name</option>'; 

        foreach ($batchArr as $value)
        {echo $value;
            $strReturn .= '<option value="'.$value.'">'.$value.'</option>';
        }
        print $strReturn;

        exit;

    }
    
      public function getCustomerbymemberidAction(){
        $memberID = $this->_getParam("p",0);  
        $productID = $this->_getParam("q",0);  
      
        $details = new Corp_Kotak_Customers();
       
        $arrDetails = $details->getCardholderBymemberID(array('member_id' => $memberID,'product_id' => $productID));
        
        print $arrDetails['id_proof_type'].'^'.$arrDetails['id_proof_number'].'^'.$arrDetails['address_proof_type'].'^'.$arrDetails['address_proof_number'];
        

        
        exit;
    }

    
    public function getCorpprogramproductsAction()
    {
       
        $bank = $this->_getParam("q",0);
        $programType = $this->_getParam("p",0);
        
        $product = new Products();
        $arrProduct = $product->getCorporateBankProgramProducts($bank, $programType);
        $strReturn = '<option value="">Select Product</option>';
        foreach ($arrProduct as $key => $value)
        {
            $strReturn .= '<option value="'.$key.'">'.$value.'</option>';
        }
        print $strReturn;

        
        exit;
        
    }
    public function getDistinctbatchsAction()
    {
       
        $productId = $this->_getParam("q",0);
        
        $cardholderModel = new BatchAdjustment();
        $data = $cardholderModel->getDistinctBatchs($productId);
        $strReturn = '<option value="">Select Batch</option>';
        foreach ($data as $key => $value)
        {
            $strReturn .= '<option value="'.$key.'">'.$value.'</option>';
        }
        print $strReturn;

        
        exit;
        
    }
    
    

     public function getRatbatchbydateAction(){
        $cardholderModel = new Corp_Ratnakar_Cardholders();
        $dateArr = array('start_date' => $this->_getParam("q",FALSE),'end_date' => $this->_getParam("p",FALSE),'product_id'=> $this->_getParam("r",FALSE));
        $batchArr = $cardholderModel->getBatchDDByDate($dateArr);
        $strReturn = '<option value="">Select Batch Name</option>'; 

        foreach ($batchArr as $value)
        {
            $strReturn .= '<option value="'.$value.'">'.$value.'</option>';
        }
        print $strReturn;

        exit;

    }
    
    public function getBankproductIdAction()
    {
        $productModel = new Products();
        
        $prodArr = $productModel->getBankProductsProgramByUnicodeDD($this->_getParam("q",FALSE),$this->_getParam("pt",FALSE), 'common');
        
        $strReturn = '<option value="">Select Product</option>'; 
        
        foreach ($prodArr as $value)
        {
            $strReturn .= '<option value="'.$value['id'].'">'.$value['name'].'</option>';
        }
        print $strReturn;

        exit;
        
    } 
    
    public function getBankproductslistAction()
    {
        $bankUnicodeArr = Util::bankUnicodesArray();
        
        if($this->_getParam("q") == $bankUnicodeArr['2'])
        {
            $productModel = new Products();
            $program_type = Util::getProgramTypeArray();

            $prodArr = $productModel->getBankProductsByUnicodeDD($this->_getParam("q",FALSE), 'common', $program_type);

            if($this->_getParam("p")){
                $strReturn = '<option value="">Select Product</option>'; 
            }
            else{
            $strReturn = '<option value="0">All Products</option>'; 
            }
            foreach ($prodArr as $value)
            {
                $strReturn .= '<option value="'.$value['id'].'">'.$value['name'].'</option>';
            }
        } else {
            $strReturn = '<option value="">Select Product</option>';
        }
        print $strReturn;
        exit;
    }
    
    public function getDistributoragentddAction()
    {
        $agentModel = new AgentUser();
        $obj = new ObjectRelations();
        $label = $agentModel->getObjectRelationshipLabel(DISTRIBUTOR_AGENT);
        $data = $obj->getToObjectInfoAgent($this->_getParam("q"), $label, true); 
        $strReturn = '<option value="">Select Distributor</option>';        
        foreach ($data as $value) { 
            $strReturn .= '<option value="'.$value['id'].'">'.$value['first_name'].' '.$value['last_name'].' ('.$value['agent_code'].')'.'</option>';
        }
        print $strReturn;exit;
    }
    
    public function getProductslistbyprogramAction() {
        $productModel = new Products();
	$ptArr = array(PROGRAM_TYPE_DIGIWALLET,PROGRAM_TYPE_REMIT) ; 
        $prodArr = $productModel->getListProductsByprogram($this->_getParam("q",FALSE),$ptArr);
        
        if($this->_getParam("p")){
            $strReturn = '<option value="">Select Product</option>'; 
        } else{
	    $strReturn = '<option value="">Select Product</option>'; 
        }
        foreach ($prodArr as $value) {
            $strReturn .= '<option value="'.$value['id'].'">'.$value['name'].'</option>';
        }
        print $strReturn; exit; 
    }
}
