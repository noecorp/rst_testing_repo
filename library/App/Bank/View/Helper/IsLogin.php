<?php
/**
 * Validate the current Logged in user
 *
 * @category App
 * @package App_View
 * @subpackage Helper
 * @copyright company
 */

class App_View_Helper_IsLogin extends Zend_View_Helper_Abstract
{
        
    /**
     * Convenience method
     * call $this->IsLogin() in the view to access 
     * the helper
     *
     * @access public
     * @return string
     */
    public function IsLogin(){
        $user = Zend_Auth::getInstance()->getIdentity();
        if(isset($user->id) && $user->id > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
        //echo "<pre>";print_r($user);exit;
    }
}