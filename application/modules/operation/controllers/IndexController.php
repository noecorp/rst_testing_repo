<?php
//namespace Test;

//require_once APPLICATION_PATH .'..\App\Messaging\MVC\Axis\Operation.php';
//require_once 'App\Messaging\MVC\Axis\Operation.php';
//use \App\Messaging;
/**
 * Default entry point in the application
 *
 * @category backoffice
 * @package backoffice_controllers
 * @copyright company
 */

class IndexController extends App_Operation_Controller
{
    /**
     * Overrides Zend_Controller_Action::init()
     *
     * @access public
     * @return void
     */
    public function init(){
        //phpinfo();exit;
        // init the parent
        //$m = new \App\Messaging\Mail();
        //$m = new \App\Messaging\MVC();
        //$m = new \App\Messaging\MVC();
   //     $m->test();
        //$m->sendAuthCode();
    
//        $m = new App\Messaging\Mail();
       /* $m = new App\Messaging\MVC\Axis\Operation();
        $flg = $m->authCode(
                array(
                        'auth_code' => '123456',
                        'mobile' => '9899195914',
                        'email' => 'vikram@transerv.co.in',
                        'host' => 'abc.com',
                 )
        );
        if(!$flg) {
            print 'Getting Error in Controller : ' .$m->getError();
        }
        exit('herere');    */    
        parent::init();
        
    }
    
    /**
     * Controller's entry point
     *
     * @access public
     * @return void
     */
    public function indexAction(){
        $this->_redirect($this->formatURL('/profile/login/'));
    }
    
   public function noscriptAction(){
        // use the login layout
        $this->_helper->layout()->setLayout('withoutlogin');
        
    }
   public function nocookieAction(){
        // use the login layout
        $this->_helper->layout()->setLayout('withoutlogin');
        
    }
}