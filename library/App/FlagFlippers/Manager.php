<?php
/**
 * Flag and flipper manager
 *
 * @category App
 * @package App_FlagFlippers
 * @copyright company
 */

/**
 * Handle different operations with the Flag and Flippers
 */
class App_FlagFlippers_Manager
{
    public static $indexKey = 'companyFlagFlippers';
//    private static $_membersAllowedResources = array(
//        'operation-index',  
//    );
    
    private static $_guestsAllowedResources = array(
        'operation-profile' => array('login', 'forgot-password', 'confirmation-code', 'new-password'),
        'agent-profile' => array('login', 'forgot-password', 'confirmation-code', 'new-password'),        
        'bank-profile' => array('login', 'forgot-password', 'confirmation-code', 'new-password'),        
        //'customer-profile' => array('login', 'forgot-password', 'confirmation-code', 'new-password'),//Customer Portal        
        'agent-signup' => array('index', 'verification', 'add', 'addeducation', 'addidentification', 'addaddress', 'addbank', 'detailscomplete'),        
        'operation-index' => array('index'),        
        'agent-index' => array('index'), 
        'agent-emailauthorization' => array('index'),
        'agent-authemailauthorization' => array('index'),
//        'operation-error' => array('error', 'forbidden', 'noscript')
        'customer-profile' => array('login', 'forgot-password', 'confirmation-code', 'new-password'),   
	 'corporate-emailauthorization'  => array('index','updateemail'),
        //'corporate-profile' => array('login', 'forgot-password', 'confirmation-code', 'new-password'), 
       	
    );
    
    private static $_guestsAllowedBankResources = array(
        'bank-profile' => array('login', 'forgot-password', 'confirmation-code', 'new-password'),        
    );
   
    private static $_guestsAllowedOperationResources = array(
        'operation-profile' => array('login', 'forgot-password', 'confirmation-code', 'new-password')  
    );
    
    private static $_guestsAllowedCorporateResources = array(
        'corporate-profile' => array('login', 'forgot-password', 'confirmation-code', 'new-password'),
	'corporate-signup' => array('index', 'verification', 'add', 'addeducation', 'addidentification', 'addaddress', 'addbank', 'detailscomplete'),
	'corporate-ajax'  => array('mobiledup','get-city','get-pincode','emaildup','send-download-link','arndup','resend-authcode','get-ifsc','get-bankdetails','get-remitter-registration-fee','get-cities','get-branches','get-branchadd','get-state','getbasic-ifsc','getbasic-bankdetails','get-statecity','get-batchbydate','get-rblregistercheck'),
	'corporate-emailauthorization'  => array('index'),
    );
    
    /* unused members group */
    private static $_membersAllowedResources = array(
        'operation-profile' => array('index', 'authcode', 'resend-authcode', 'edit', 'change-password', 'logout'),
        'agent-profile' => array('index', 'authcode', 'resend-authcode', 'edit', 'change-password', 'logout','resend-verificationlink','changemobilenumber','mobileverification'),
        // 'agent-agentfunding' => array('index', 'requestfund', 'fundrequest','viewfundrequest'),
        'customer-profile' => array('index', 'authcode', 'resend-authcode', 'edit', 'change-password', 'logout'),
        'bank-profile' => array('index', 'authcode', 'resend-authcode', 'edit', 'change-password', 'logout'),
       // 'corporate-profile' => array('index', 'authcode', 'resend-authcode', 'edit', 'change-password', 'logout'),
    );
    
    private static $_membersAllowedBankResources = array(
        'bank-profile' => array('index', 'authcode', 'resend-authcode', 'edit', 'change-password', 'logout'),
	
    );
    
    private static $_membersAllowedOperationResources = array(
        'operation-profile' => array('index', 'authcode', 'resend-authcode', 'edit', 'change-password', 'logout')
    );
    
    private static $_membersAllowedCorporateResources = array(
        'corporate-profile' => array('index', 'authcode', 'resend-authcode', 'edit', 'change-password', 'logout','webterm','privacy'),
	'corporate-ajax'  => array('mobiledup','get-city','get-pincode','emaildup','send-download-link','arndup','resend-authcode','get-ifsc','get-bankdetails','get-remitter-registration-fee','get-cities','get-branches','get-branchadd','get-state','getbasic-ifsc','getbasic-bankdetails','get-statecity','get-batchbydate','get-rblregistercheck'),
	'corporate-emailauthorization'  => array('index','updateemail'),
    );
    
    private static $_opsReports = array(
            'agentsummary'       => 'exportagentsummary',
            'agentfundrequests'     => 'exportagentfundrequests',
            'agentwisefundrequests' => 'exportagentwisefundrequests',
//            'commreport'            => 'exportcommreport',
//            'agentwisecommreport'   => 'exportagentwisecommreport',
            'agentcommissionsummary'   => 'exportagentcommissionsummary',
            'agentactivation'       => 'exportagentactivation',
            'agentbalancesheet'     => 'exportagentbalancesheet',
            'feereport'     => 'exportfeereport',
            'agentwisefeereport'    => 'exportagentwisefeereport',
            'agenttransaction'      => 'exportagenttransaction',
            'agentwisetransactions' => 'exportagentwisetransactions',
            'remitterregn'          => 'exportremitterregn',
            'agentsummary'          => 'exportagentsummary',
            'agentcommissionsummary'=> 'exportagentcommissionsummary',
            'remittertransaction'   => 'exportremittertransaction',
            'remittancerefund'      => 'exportremittancerefund',
            'feereport'             => 'exportfeereport',
            'userlogin'             => 'exportuserlogin',
            'loginsummary'          => 'exportloginsummary',
            'customerregistration'  => 'exportcustomerregistration',
            'wallettxn'  => 'exportwallettxn'
            

    );
    
    private static $_opsMvcAxisReports = array(
            'agentloadreload'       => 'exportagentloadreload',
            'agentwiseload'       => 'exportagentwiseload',
            'cardholderactivations' => 'exportcardholderactivations',
            'loadreloadcomm' => 'exportloadreloadcomm',
            'agentwiseloadreloadcomm' => 'exportagentwiseloadreloadcomm',
    );
    
    private static $_opsRemitReports = array(
            'remittancereport'       => 'exportremittancereport',
            'exportremittancereport'       => 'searchremit',
            'searchremit'       => 'exportsearchremit',
            'agentwiseremittancereport'       => 'exportagentwiseremittancereport',
            'remittancecommission'       => 'exportremittancecommission',
            'agentwiseremittancecommission'       => 'exportagentwiseremittancecommission',
            'remitterregn'       => 'exportremitterregn',
            'remittertransaction'   => 'exportremittertransaction',
            'neftresponse'   => 'exportneftresponse',
            'remittancerefundyettoclaim'             => 'exportremittancerefundyettoclaim',
            'remittanceexception'             => 'exportremittanceexception'
    );
    

    private static $_opsAgentFunding = array(
            'pendingfundrequest'       => 'exportpendingfundrequest',
            'unsettledbankstatement'       => 'exportunsettledbankstatement',
            'settledfundrequest'       => 'exportsettledfundrequest',
           
);
    private static $_opsCorpCardLoad = array(
            'walletstatus'       => 'exportwalletstatus',
            

    );
    private static $_opsCorpBoi = array(
            'customerregistration'       => 'exportcustomerregistration',
            

    );
    
    private static $_opsCorpCardholders = array(
            'batchstatus'       => 'exportbatchstatus',
            
    );
    /**
     * Load the ACL to the Registry if is not there
     * 
     * This function takes care about generating the acl from the db
     * if the info is not in the registry and/or cache.
     * 
     * If the acl is inside the cache we load it from there.
     * 
     * @return void
     */
    public static function load(){
        if(!App_FlagFlippers_Manager::_checkIfExist()){
            //if(!$acl = App_FlagFlippers_Manager::_getFromCache()){
            if(CURRENT_MODULE == MODULE_AGENT) {
                $acl = App_FlagFlippers_Manager::_generateFromDbAgent();
            }elseif(CURRENT_MODULE == MODULE_BANK) {
                $acl = App_FlagFlippers_Manager::_generateFromDbBank();
            }elseif(CURRENT_MODULE == MODULE_CUSTOMER) {
                $acl = App_FlagFlippers_Manager::_generateFromDbCustomer();
            } elseif(CURRENT_MODULE == MODULE_CORPORATE) {
                $acl = App_FlagFlippers_Manager::_generateFromDbCorporate();
            } else {
                $acl = App_FlagFlippers_Manager::_generateFromDb();
            }
            //echo "<pre>";print_r($acl);exit;
            //}
            
            App_FlagFlippers_Manager::_storeInRegistry($acl);
        }
    }
    
    
    
    /**
     * Regenerate the Acl from the DB and update the cache and Zend_Registry
     *
     * @return boolean
     */
    public static function save(){
        
        $acl = App_FlagFlippers_Manager::_generateFromDb();
        App_FlagFlippers_Manager::_storeInCache($acl);
        App_FlagFlippers_Manager::_storeInRegistry($acl);
    }
    
    /**
     * Check if a role is allowed for a certain resource
     *
     * @param string $role 
     * @param string $resource 
     * @return boolean
     */
    public static function isAllowed($role = NULL, $resource = NULL, $action = NULL){
     
        //print $role . ' : ' .  $resource . ' : ' .  $action . '<br />';
        if(CURRENT_MODULE == 'api') {
            return true;
        }

        if(empty($role)){
            $user = BaseUser::getSession();
            $role = $user->group->name;
        }
        
        if(!empty($resource)){
            $resource = strtolower(CURRENT_MODULE) . '-' . $resource;
        }
        
        if(!empty($action)){
            $action = App_Inflector::camelCaseToDash($action);
        }
        //return true;
        //$aclObject = new Zend_Acl();
        $aclObject = Zend_Registry::get("ACL");

        if($aclObject->has($resource)) 
        {
            return  App_FlagFlippers_Manager::_getFromRegistry()->isAllowed($role, $resource, $action);
//            print $role . ' : ' .  $resource . ' : ' .  $action .  ' : ' .  $flg . '<br />';
//	    return $flg;
        }
        return FALSE;
    }
    
    /**
     * Log a message related to Flag And Flippers
     *
     * @param string $msg 
     * @param string $level 
     * @return void
     */
    public static function log($msg, $level = Zend_Log::INFO){
        Zend_Registry::get('Zend_Log_FlagFlippers')->log($msg, $level);
    }
    
    /**
     * Check if the acl exists in Zend_Registry
     *
     * @return boolean
     */
    private static function _checkIfExist(){
        return Zend_Registry::isRegistered(App_FlagFlippers_Manager::$indexKey);
    }
    
    /**
     * Get Acl from Registry
     *
     * @return void
     */
    private static function _getFromRegistry(){
        if(App_FlagFlippers_Manager::_checkIfExist()){
            return Zend_Registry::get(App_FlagFlippers_Manager::$indexKey);
        }
        
        return FALSE;
    }
    
    /**
     * Retrieve the acl from the cache
     *
     * @return Zend_Acl | boolean
     */
    private static function _getFromCache(){
        $cacheHandler = App_DI_Container::get('CacheManager')->getCache('default');
        if($acl = $cacheHandler->load(App_FlagFlippers_Manager::$indexKey)){
            return $acl;
        }
        return FALSE;
    }
    
    /**
     * Generate the Acl object from the permission file
     *
     * @return Zend_Acl
     */
    private static function _generateFromDb(){
        //$aclObject = new Zend_Acl();
        
        $aclObject = Zend_Registry::get("ACL");
        
        $aclObject->deny();
        
        //Get all the models
        //$backofficeUserModel = new OperationUser();
        $groupModel = new Group();
        $flagModel = new Flag();
        $flipperModel = new Flipper();
        $userSession = BaseUser::getSession();
        
        //Add all groups
        $groups = $groupModel->fetchAllThreaded();
        foreach($groups as $group){
            if(!$aclObject->hasRole(new Zend_Acl_Role($group->name))) {
                if($group->parent_name){
                    $aclObject->addRole(new Zend_Acl_Role($group->name), $group->parent_name);
                }else{
                    $aclObject->addRole(new Zend_Acl_Role($group->name));
                }
            }
        }
        
        //Add all users
        /*$users = $backofficeUserModel->findAll();
        foreach($users as $user){
            //$aclObject->addRole(new Zend_Acl_Role($user->username), $user->groupNames);
            if(!$aclObject->hasRole($user->username)) {
                $aclObject->addRole(new Zend_Acl_Role($user->username), $user->groupNames);
            }
        }
        //Add all resources
        $flags = $flagModel->fetchAll();
        
        foreach($flags as $flag){
            if(!$aclObject->has($flag->name)) {
                $aclObject->addResource(new Zend_Acl_Resource($flag->name));
            }
        }*/
        
        //Add hardcoded resources
        /*if(!$aclObject->has('agent-error')) {
            $aclObject->addResource('agent-error');
        }*/
        if(!$aclObject->has('operation-error')) {
            $aclObject->addResource('operation-error');
        }
        if(!$aclObject->has('operation-index')) {
            $aclObject->addResource('operation-index');
        }
        /*if(!$aclObject->has('agent-index')) {
            $aclObject->addResource('agent-index');
        }*/
        if(!$aclObject->has('operation-reports')) {
            $aclObject->addResource('operation-reports');
        }
        
        //Add all resources
        $flags = $flagModel->getOperationFlags();

        foreach($flags as $flag){
            if(!$aclObject->has($flag->name)) {
                $aclObject->addResource(new Zend_Acl_Resource($flag->name));
            }
        }
            
        //Populate the ACLs
        //$flippers = $flipperModel->fetchAll();
        //print '<pre>';print_r($flippers);exit;
        
        if(isset($userSession->id) && $userSession->id > 0) {
            if(!$aclObject->hasRole($userSession->username)) {
                $aclObject->addRole(new Zend_Acl_Role($userSession->username), $userSession->group->name);
            }
            
            $flippers = $flipperModel->findByOpsId($userSession->id, $userSession->group->group_id);
            foreach($flippers as $flipper){
                //echo $flipper->flag_name.'====='.$flipper->privilege_name."<br>";
                switch(APPLICATION_ENV){
                    case APP_STATE_PRODUCTION:
//                        $flag = $flag->active_on_prod;
                        $flag = '0';
                        break;
                    default:
                        $flag = '0';
//                        $flag = $flag->active_on_dev;
                }

                /*$privilege = $flipper->findParentRow('Privilege');

                $flipper->privilegeName = (isset($privilege) && !empty($privilege->name)) ? $privilege->name : '';

                $group = $flipper->findParentRow('Group');
                $flipper->groupName = $group->name;
                $flag = $flipper->findParentRow('Flag');
                $flipper->flagName = $flag->name;*/

                $flipper->privilegeName = $flipper->privilege_name;
                $flipper->groupName = $flipper->group_name;
                $flipper->flagName = $flipper->flag_name;
                
                if(Zend_Registry::get('IS_PRODUCTION')){
                    $envAllowed = (isset($flag) && !empty($flag->active_on_prod)) ? $flag->active_on_prod : 0;
                }else{
                    $envAllowed = (isset($flag) && !empty($flag->active_on_prod)) ?  $flag->active_on_dev : 0;
                }

                if($flipper->allow  && !empty($flipper->groupName) && !empty($flipper->flagName) && !empty($flipper->privilegeName)){
                    $aclObject->allow($flipper->groupName, $flipper->flagName, $flipper->privilegeName);
                } else {
                    $aclObject->deny($flipper->groupName, $flipper->flagName, $flipper->privilegeName);
                }

            }
        }
        foreach(App_FlagFlippers_Manager::$_guestsAllowedOperationResources as $resource => $roles){
            if(!is_array($roles)){
                $aclObject->allow('guests', $resource);
            }else{
                foreach($roles as $r){
                    $aclObject->allow('guests', $resource, $r);
                }
            }
        }
        
        //Everbody can see the errors
        
        $aclObject->allow(null, 'operation-error');
        $aclObject->allow(null, 'operation-ajax');
        $aclObject->allow(null, 'operation-index');
        /*$aclObject->allow(null, 'agent-ajax');
        $aclObject->allow(null, 'agent-error');
        $aclObject->allow(null, 'agent-index');*/
        
        $user = BaseUser::getSession();
        if(isset($user->group->name) && !empty($user->group->name))
        {
            foreach(App_FlagFlippers_Manager::$_membersAllowedOperationResources as $resource => $roles){
                if(!is_array($roles)){
                    $aclObject->allow($user->group->name, $resource);
                }else{
                    foreach($roles as $r){
                        $aclObject->allow($user->group->name, $resource, $r);
                    }
                }
            }
            
            foreach(App_FlagFlippers_Manager::$_opsReports as $report => $export)
            {
               if($aclObject->isAllowed($user->group->name, 'operation-reports', $report))
                {
                    $aclObject->allow($user->group->name, 'operation-reports', $export);
                } 
            }
            
            foreach(App_FlagFlippers_Manager::$_opsMvcAxisReports as $report => $export)
            {
               if($aclObject->isAllowed($user->group->name, 'operation-mvc_axis_reports', $report))
                {
                    $aclObject->allow($user->group->name, 'operation-mvc_axis_reports', $export);
                } 
            }
            
            foreach(App_FlagFlippers_Manager::$_opsRemitReports as $report => $export)
            {
               if($aclObject->isAllowed($user->group->name, 'operation-remit_reports', $report))
                {
                    $aclObject->allow($user->group->name, 'operation-remit_reports', $export);
                } 
            }
            

            foreach(App_FlagFlippers_Manager::$_opsAgentFunding as $agentfunding => $export)
            {
               if($aclObject->isAllowed($user->group->name, 'operation-agentfunding', $agentfunding))
                {
                    $aclObject->allow($user->group->name, 'operation-agentfunding', $export);
} 
            }
             foreach(App_FlagFlippers_Manager::$_opsCorpCardLoad as $report => $export)
            {
               if($aclObject->isAllowed($user->group->name, 'operation-corp_ratnakar_cardload', $report))
                {
                    $aclObject->allow($user->group->name, 'operation-corp_ratnakar_cardload', $export);

                } 
            }
             foreach(App_FlagFlippers_Manager::$_opsCorpCardholders as $report => $export)
            {
               if($aclObject->isAllowed($user->group->name, 'operation-corp_ratnakar_cardholder', $report))
                {
                    $aclObject->allow($user->group->name, 'operation-corp_ratnakar_cardholder', $export);
                } 
            }
            foreach(App_FlagFlippers_Manager::$_opsCorpBoi as $report => $export)
            {
               if($aclObject->isAllowed($user->group->name, 'operation-corp_boi_customer', $report))
                {
                    $aclObject->allow($user->group->name, 'operation-corp_boi_customer', $export);

                } 
            }
                    
        }

        return $aclObject;
    }
    
    
    /**
     * Generate the Acl object from the permission file
     *
     * @return Zend_Acl
     */
    private static function _generateFromDbAgent(){
        //$aclObject = new Zend_Acl();
        
        $aclObject = Zend_Registry::get("ACL");
        
        $aclObject->deny();
        
        //Get all the models
        //$backofficeUserModel = new OperationUser();
        $userModel = new AgentUser();
        $flagModel = new Flag();
        $flipperModel = new Flipper();
        $privilegeModel = new Privilege();
        $productPrivileges = new ProductPrivilege();
        
        $aclObject->addRole(new Zend_Acl_Role('guests'));
        $aclObject->addRole(new Zend_Acl_Role('administrators'));
        $aclObject->addRole(new Zend_Acl_Role(MODULE_AGENT));    
        $userSession = BaseUser::getSession();        
        //Add all users
        //$users = $userModel->findAll();
        $users = $userModel->getApprovedAgents();

        foreach($users as $user){
            if(!$aclObject->hasRole($user->email)) {
                $aclObject->addRole(new Zend_Acl_Role($user->email), $user->groupNames);
            }
        }
        //Add all resources
        $flags = $flagModel->fetchAll();
        
        foreach($flags as $flag){
            if(!$aclObject->has($flag->name)) {
                $aclObject->addResource(new Zend_Acl_Resource($flag->name));
            }
        }
        
        //Add hardcoded resources
        if(!$aclObject->has('agent-error')) {
            $aclObject->addResource('agent-error');
        }
        if(!$aclObject->has('agent-index')) {
            $aclObject->addResource('agent-index');
        }
       
        
        //Populate the ACLs
        $productPrivilegesArr = $productPrivileges->fetchAll();
        //$flippers = $flipperModel->fetchAll();

        if(isset($userSession->id) && $userSession->id > 0) {
            $productPrivilegesArr = $productPrivileges->findByAgentId($userSession->id);
            foreach($productPrivilegesArr as $flipper){

                if(Zend_Registry::get('IS_PRODUCTION')){
                    $envAllowed = (isset($flag) && !empty($flag->active_on_prod)) ? $flag->active_on_prod : 0;
                }else{
                    $envAllowed = (isset($flag) && !empty($flag->active_on_prod)) ?  $flag->active_on_dev : 0;
                }
                
                //if($flipper->allow ){            
                if($flipper->allow  && !empty($flipper->email) && !empty($flipper->flag_name) && !empty($flipper->privilege_name)){                
                    $aclObject->allow($flipper->email, $flipper->flag_name, $flipper->privilege_name);
                } else {
                   $aclObject->deny($flipper->email, $flipper->flag_name, $flipper->privilege_name);
                }
            }
        }
        
        //Hardcode basic paths for guests
        foreach(App_FlagFlippers_Manager::$_guestsAllowedResources as $resource => $roles){
            if(!is_array($roles)){
                $aclObject->allow('guests', $resource);
            }else{
                foreach($roles as $r){
                    $aclObject->allow('guests', $resource, $r);
                }
            }
        }
        
        //Everbody can see the errors
        
        $aclObject->allow(null, 'agent-ajax');
        $aclObject->allow(null, 'agent-error');
        $aclObject->allow(null, 'agent-index');
        
        $user = BaseUser::getSession();
        if(isset($userSession->group->name) && !empty($userSession->group->name))
        {
            foreach(App_FlagFlippers_Manager::$_membersAllowedResources as $resource => $roles){
                if(!is_array($roles)){
                    $aclObject->allow($userSession->group->name, $resource);
                }else{
                    foreach($roles as $r){
                        $aclObject->allow($userSession->group->name, $resource, $r);
                    }
                }
            }
                  
        }
        
        $aclObject->allow(TYPE_ADMIN);
        return $aclObject;
    }
    
     /**
     * Generate the Bank Acl object from the permission file
     *
     * @return Zend_Acl
     */
     private static function _generateFromDbBank(){
        //$aclObject = new Zend_Acl();
        
        $aclObject = Zend_Registry::get("ACL");
        
        $aclObject->deny();
        
        //Get all the models
        $backofficeUserModel = new BankUser();
        $groupModel = new BankGroup();
        $flagModel = new Flag();
        $flipperModel = new Flipper();
        $privilegeModel = new Privilege();
        $aclObject->addRole(new Zend_Acl_Role('guests'));
        //Add all groups
        $groups = $groupModel->fetchAllThreaded();

        foreach($groups as $group){
            if(!$aclObject->hasRole(new Zend_Acl_Role($group->name))) {
                if($group->parent_name){
                    $aclObject->addRole(new Zend_Acl_Role($group->name), $group->parent_name);
                }else{
                    $aclObject->addRole(new Zend_Acl_Role($group->name));
                }
            }
        }
        
        //Add all users
        $users = $backofficeUserModel->findAll();
        foreach($users as $user){
            //$aclObject->addRole(new Zend_Acl_Role($user->username), $user->groupNames);
            if(!$aclObject->hasRole($user->username)) {
                $aclObject->addRole(new Zend_Acl_Role($user->username), $user->groupNames);
            }
        }
        
        //Add all resources
        $flags = $flagModel->getBankFlags();
        
        foreach($flags as $flag){
            if(!$aclObject->has($flag->name)) {
                $aclObject->addResource(new Zend_Acl_Resource($flag->name));
            }
        }
        //$aclObject->addResource('bank-ajax');
        $aclObject->addResource('bank-error');
        $aclObject->addResource('bank-index');
        //Add hardcoded resources
        //Populate the ACLs
        //$flippers = $flipperModel->fetchAll();
        $flippers = $flipperModel->fetchAllBankFlippers();
        //echo '<pre>';print_r($flippers);exit;
        foreach($flippers as $flipper){
            switch(APPLICATION_ENV){
                case APP_STATE_PRODUCTION:
                    $flag = $flag->active_on_prod;
                    break;
                default:
                    $flag = $flag->active_on_dev;
            }
            
            $privilege = $flipper->findParentRow('Privilege');

            $flipper->privilegeName = (isset($privilege) && !empty($privilege->name)) ? $privilege->name : '';
          //  echo '<pre>';print_r($flipper);exit('END');
            $group = $flipper->findParentRow('BankGroup');
            //echo '<pre>';print_r($group);exit('END');
            $flipper->groupName = $group->name;
            
            $flag = $flipper->findParentRow('Flag');
            $flipper->flagName = $flag->name;
            
            if(Zend_Registry::get('IS_PRODUCTION')){
                $envAllowed = (isset($flag) && !empty($flag->active_on_prod)) ? $flag->active_on_prod : 0;
            }else{
                $envAllowed = (isset($flag) && !empty($flag->active_on_prod)) ?  $flag->active_on_dev : 0;
            }
            
            //if($flipper->allow ){
            if($flipper->allow  && !empty($flipper->groupName) && !empty($flipper->flagName) && !empty($flipper->privilegeName)){            
              //  echo $flipper->groupName . ' : '. $flipper->flagName. ' : '. $flipper->privilegeName . '<br />';
                $aclObject->allow($flipper->groupName, $flipper->flagName, $flipper->privilegeName);
            } else {
                //echo 'DENY : ' .$flipper->groupName . ' : '. $flipper->flagName. ' : '. $flipper->privilegeName . '<br />';
                $aclObject->deny($flipper->groupName, $flipper->flagName, $flipper->privilegeName);
            }
        }
        //print '<pre>';print_r($flippers);exit;
        //foreach(App_FlagFlippers_Manager::$_guestsAllowedResources['bank-profile'] as $resource => $roles){
        foreach(App_FlagFlippers_Manager::$_guestsAllowedBankResources as $resource => $roles){
            if(!is_array($roles)){
                $aclObject->allow('guests', $resource);
            }else{
                foreach($roles as $r){
                    $aclObject->allow('guests', $resource, $r);
                }
            }
        }
        
        //Everbody can see the errors
        

        //$aclObject->allow(null, 'bank-ajax');
        $aclObject->allow(null, 'bank-error');
        $aclObject->allow(null, 'bank-index');
        
        $user = BaseUser::getSession();
        if(isset($user->group->name) && !empty($user->group->name))
        {
            foreach(App_FlagFlippers_Manager::$_membersAllowedBankResources as $resource => $roles){
                if(!is_array($roles)){
                    $aclObject->allow($user->group->name, $resource);
                }else{
                    foreach($roles as $r){
                        $aclObject->allow($user->group->name, $resource, $r);
                    }
                }
            }
            
                    
        }

        return $aclObject;
    }

    /**
     * Generate the Acl object from the permission file
     *
     * @return Zend_Acl
     */
    private static function _generateFromDbCustomer(){
        //$aclObject = new Zend_Acl();
        
        $aclObject = Zend_Registry::get("ACL");
        
        $aclObject->deny();
        
        //Get all the models
        //$backofficeUserModel = new OperationUser();
        $userModel = new CustomerMaster();
        $flagModel = new Flag();
        $flipperModel = new Flipper();
        $privilegeModel = new Privilege();
        $productPrivileges = new ProductPrivilege();
        
        $aclObject->addRole(new Zend_Acl_Role('guests'));
        $aclObject->addRole(new Zend_Acl_Role('administrators'));
        $aclObject->addRole(new Zend_Acl_Role(MODULE_AGENT));    
        $userSession = BaseUser::getSession();        
        //Add all users
        //$users = $userModel->findAll();
        $users = $userModel->getApprovedCustomer();
        //echo "<pre>";print_r($users->toArray());exit;

        
        foreach($users as $user){
            if(!$aclObject->hasRole($user->shmart_crn)) {
                $aclObject->addRole(new Zend_Acl_Role($user->shmart_crn), 'administrators');
            }
        }

        //Add all resources
        $flags = $flagModel->fetchAll();
        
        foreach($flags as $flag){
            if(!$aclObject->has($flag->name)) {
                $aclObject->addResource(new Zend_Acl_Resource($flag->name));
            }
        }
        
        //Add hardcoded resources
        if(!$aclObject->has('customer-error')) {
            $aclObject->addResource('customer-error');
        }
        if(!$aclObject->has('customer-index')) {
            $aclObject->addResource('customer-index');
        }
       
        
        //Populate the ACLs
        $flippers = $flipperModel->fetchAll();
        if(isset($userSession->id) && $userSession->id > 0) {
            
            foreach($flippers as $flipper){

            $privilege = $flipper->findParentRow('Privilege');
            $flipper->privilegeName = (isset($privilege) && !empty($privilege->name)) ? $privilege->name : '';
            $flipper->groupName = 'administrators';
            
            $flag = $flipper->findParentRow('Flag');
            $flipper->flagName = $flag->name;
            
            if(Zend_Registry::get('IS_PRODUCTION')){
                $envAllowed = (isset($flag) && !empty($flag->active_on_prod)) ? $flag->active_on_prod : 0;
            }else{
                $envAllowed = (isset($flag) && !empty($flag->active_on_prod)) ?  $flag->active_on_dev : 0;
            }
            
            //if($flipper->allow ){
            if($flipper->allow  && !empty($flipper->groupName) && !empty($flipper->flagName) && !empty($flipper->privilegeName)){            
                $aclObject->allow($flipper->groupName, $flipper->flagName, $flipper->privilegeName);
            } else {
                $aclObject->deny($flipper->groupName, $flipper->flagName, $flipper->privilegeName);
            }
            }
        }
        
        //Hardcode basic paths for guests
        foreach(App_FlagFlippers_Manager::$_guestsAllowedResources as $resource => $roles){
            if(!is_array($roles)){
                $aclObject->allow('guests', $resource);
            }else{
                foreach($roles as $r){
                    $aclObject->allow('guests', $resource, $r);
                }
            }
        }
        
      
        $aclObject->allow(null, 'customer-error');
        $aclObject->allow(null, 'customer-index');
        
        $user = BaseUser::getSession();
        if(isset($userSession->group->name) && !empty($userSession->group->name))
        {
            foreach(App_FlagFlippers_Manager::$_membersAllowedResources as $resource => $roles){
                if(!is_array($roles)){
                    $aclObject->allow($userSession->group->name, $resource);
                }else{
                    foreach($roles as $r){
                        $aclObject->allow($userSession->group->name, $resource, $r);
                    }
                }
            }
                  
        }
        //Admins are allowed everywhere
        $aclObject->allow('administrators');
        
        return $aclObject;
    }
    /**
     * Generate the Bank Acl object from the permission file
     *
     * @return Zend_Acl
     */
     private static function _generateFromDbCorporate(){
	
        $aclObject = Zend_Registry::get("ACL");
        
        $aclObject->deny();
        
        //Get all the models
        //$backofficeUserModel = new OperationUser();
        $userModel = new CorporateUser();
        $flagModel = new Flag();
        $flipperModel = new Flipper();
        $privilegeModel = new Privilege();
        $productPrivileges = new CorporateProductPrivilege();
        
        $aclObject->addRole(new Zend_Acl_Role('guests'));
        $aclObject->addRole(new Zend_Acl_Role('administrators'));
	$aclObject->addRole(new Zend_Acl_Role('local'));
        $aclObject->addRole(new Zend_Acl_Role('regional'));
        $aclObject->addRole(new Zend_Acl_Role('head'));
        $aclObject->addRole(new Zend_Acl_Role(MODULE_CORPORATE));    
        $userSession = BaseUser::getSession();        
        //Add all users
        //$users = $userModel->findAll();
        $users = $userModel->getApprovedCorporates();

        foreach($users as $user){
            if(!$aclObject->hasRole($user->email)) {
                $aclObject->addRole(new Zend_Acl_Role($user->email), $user->groupNames);
            }
        }
        //Add all resources
        $flags = $flagModel->getCorporateFlags();
        
        foreach($flags as $flag){
            if(!$aclObject->has($flag->name)) {
                $aclObject->addResource(new Zend_Acl_Resource($flag->name));
            }
        }
        
        
        
        if(isset($userSession->id) && $userSession->id > 0) {
            $productPrivilegesArr = $productPrivileges->findByCorporateId($userSession->id);
	    //echo "<pre>"; print_r($productPrivilegesArr); exit;  
            foreach($productPrivilegesArr as $flipper){
		
                if(Zend_Registry::get('IS_PRODUCTION')){
                    $envAllowed = (isset($flag) && !empty($flag->active_on_prod)) ? $flag->active_on_prod : 0;
                }else{
                    $envAllowed = (isset($flag) && !empty($flag->active_on_prod)) ?  $flag->active_on_dev : 0;
                }
                
                //if($flipper->allow ){            
                if($flipper->allow  && !empty($flipper->email) && !empty($flipper->flag_name) && !empty($flipper->privilege_name)){                
                    $aclObject->allow($flipper->email, $flipper->flag_name, $flipper->privilege_name);
		    //echo "ALLOW ".$flipper->email."=>".$flipper->flag_name."=>".$flipper->privilege_name."<br>";
                } else {
                    $aclObject->deny($flipper->email, $flipper->flag_name, $flipper->privilege_name);
		    //echo "DENY".$flipper->email."=>".$flipper->flag_name."=>".$flipper->privilege_name."<br>";   
                }
            }
        }
// echo '<pre>';print_r(App_FlagFlippers_Manager::$_guestsAllowedCorporateResources);exit('-->');
 try {       
        //Hardcode basic paths for guests
        foreach(App_FlagFlippers_Manager::$_guestsAllowedCorporateResources as $resource => $roles){
            if(!is_array($roles)){
                $aclObject->allow('guests', $resource);
            }else{
                foreach($roles as $r){
                    $aclObject->allow('guests', $resource, $r);
                }
            }
        }
} catch ( Exception $e ) {
//echo '<pre>';print_r($e);exit('--');
}
  //     echo '<pre>';print_r($aclObject);exit('-->');
 
	if(!$aclObject->has('corporate-error')) {
            $aclObject->addResource('corporate-error');
        }
       
        if(!$aclObject->has('corporate-index')) {
            $aclObject->addResource('corporate-index');
        }
       
       
        //Everbody can see the errors
        $aclObject->allow(NULL, 'corporate-error');
        $aclObject->allow(NULL, 'corporate-index');
      
        
        $user = BaseUser::getSession();
        if(isset($userSession->group->name) && !empty($userSession->group->name))
        {
            foreach(App_FlagFlippers_Manager::$_membersAllowedCorporateResources as $resource => $roles){
                if(!is_array($roles)){
                    $aclObject->allow($userSession->group->name, $resource);
                }else{
                    foreach($roles as $r){
                        $aclObject->allow($userSession->group->name, $resource, $r);
                    }
                }
            }
                  
        }
        
        $aclObject->allow(TYPE_ADMIN);
	//echo "<pre>";print_r($aclObject); //exit;
        return $aclObject;    
    
    
    }

    /**
     * Store the Acl in the cache
     *
     * @return void
     */
    private static function _storeInCache($acl = NULL){
        if(is_null($acl) && App_FlagFlippers_Manager::_checkIfExist()){
            $acl = App_FlagFlippers_Manager::_getFromRegistry();
        }
        
        if(empty($acl)){
            throw new Exception('You must provide a valid Acl in order to store it');
        }
        
        $cacheHandler = App_DI_Container::get('CacheManager')->getCache('default');
        
        $cacheHandler->save($acl, App_FlagFlippers_Manager::$indexKey);
    }
    
    /**
     * Store the Acl in the Registry
     *
     * @return void
     */
    private static function _storeInRegistry($acl){
        Zend_Registry::set(App_FlagFlippers_Manager::$indexKey, $acl);
    }
}