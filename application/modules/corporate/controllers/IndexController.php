<?php
/**
 * Default entry point in the application
 *
 * @package frontend_controllers
 * @copyright company
 */

class IndexController extends App_Corporate_Controller
{
    /**
     * Overrides Zend_Controller_Action::init()
     *
     * @access public
     * @return void
     */
    public function init()
    {
        //App_Logger::log("Testing Error Message", Zend_Log::ERR);
        // init the parent
       /* $ecsApi = new App_Socket_ECS_Corp_Transaction();
        $val['transactionId'] = rand('111111111111','999999999999');
        $val['crn'] = '4780745100001187';
        $val['amount'] = '1234';
        $val['date_transaction'] = '';
        $resp = $ecsApi->reversalMACardLoad($val);        
        print $resp;exit('asfsdf');
        */
        
//        $userObj = new Mvc_Axis_CardholderUser();
//        $ecs = new App_Api_ECS();
//        $ecs->cardholderRegistration($userObj);
//        exit("testing Cardholder Registration");
        //echo urlencode(Util::ssl_encrypt('/index/test'));exit;
        //echo Util::ssl_decrypt('efe17010058074ea3dc0bcc41a2ab83a', 'U2FsdGVkX1+7vxcSOrJ8m8phsuRVB2Dz4R4SQcO14Qs=');
        //echo '<br />';
        //exit;
//        $a = base64_encode('/index/test');
//        $a = base64_encode('/profile/login');
//        print $a;exit;
//        $m = new App\Messaging\MVC\Axis\Agent();
//        $m->authCode(
//                array(
//                        'auth_code' => '123456',
//                        'mobile' => '9899195914',
//                 )
//        );
//        exit;            
        
        //echo Util::decryptURL(urldecode('7OGmf69Zal701Hfpy3x76Io1vaymE1ig1Irbvsud%2FmzBgouB%2Fj3n7%2FKVqxm2W%2B58d3tUm1HRzfEPNM6VQXAAG4Hkt4NJ%2Bc60jE9ZfTtuA7Jon7KlvOQCWpXug6QI%2BNaB'));
        //exit;
        parent::init();
    }
    
    /**
     * Controller's entry point
     *
     * @access public
     * @return void
     */
    public function indexAction()
    {
       $user = Zend_Auth::getInstance()->getIdentity();
       if(isset($user->id)) {
           $this->_redirect($this->formatURL('/profile/index'));
           exit;
       }else{
         $this->_redirect($this->formatURL('/profile/login/'));
       }
        //$this->_helper->layout()->setLayout('withoutlogin');        
       
    }

    

}
    


