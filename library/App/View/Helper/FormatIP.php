<?php
/**
 * Helper that will be used to format IP throughout 
 * the application
 *
 * 
 * @category App
 * @package App_View
 * @subpackage Helper
 * @copyright company
 */

class App_View_Helper_FormatIP extends Zend_View_Helper_Abstract
{
    /**
     * Convenience method
     * call $this->formatDate() in the view to access 
     * the helper
     *
     * @access public
     * @return string
     */
    public function FormatIP(){
        return $this;
    }
    
    
        /**
     * Return formated ip address
     *
     * @param string $ip
     * @return string ip in format AAAABBBBCCCCDDDD
     * */    
    public function formatIpAddress($ip)
    {
        $quads = explode(".", $ip);
        if (count($quads) != 4) {
                throw new Exception(__METHOD__ . ' invalid arguments passed');
        }

        return sprintf("%03d%03d%03d%03d", (int) $quads[0], (int) $quads[1], (int) $quads[2], (int) $quads[3]);
    }
    /**
     * Return restored ip address
     * 
     * @param type $ip
     * @return string ip in AAA.BBB.CCC.DDD
     */
    public function restoreIpAddress($ip)
    {
            return (string)((int)substr($ip, 0, 3) . '.' . (int)substr($ip, 3, 3) . '.' . (int)substr($ip, 6, 3) . '.' . (int)substr($ip, 9, 3));
    }    
}