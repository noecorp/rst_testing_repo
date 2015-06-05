<?php
/**
 * Helper that will be used to format url throughout 
 * the application
 *
 * 
 * @category App
 * @package App_View
 * @subpackage Helper
 * @copyright company
 */

class App_View_Helper_FormatURL extends Zend_View_Helper_Abstract
{
    /**
     * Convenience method
     * call $this->formatDate() in the view to access 
     * the helper
     *
     * @access public
     * @return string
     */
    public function formatURL($url){
        if($url != '' || $url != '/') {
            $url = Util::encryptURL($url);
        }
        return $url;
    }
}