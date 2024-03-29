<?php
/**
 * Renders the main menu for the site. 
 *
 * @category App
 * @package App_View
 * @subpackage Helper
 * @copyright company
 */

class App_View_Helper_RenderMenu extends Zend_View_Helper_Abstract
{
    /**
     * Template for the links
     * 
     * @var string
     * @access protected
     */
    // echo Util::formatURL('/index/test?a=acbadfadf&b=asdfdf&c=adfsfsdf');
    protected $_linkTemplate = '<li><a href="%1$s" title="%2$s">%2$s</a></li>';

    
    /**
     * Template for the selected link
     * 
     * @var string
     * @access protected
     */
    protected $_linkSelectedTemplate = '<li class="selected"><a href="%1$s" title="%2$s">%2$s</a></li>';
    
    /**
     * Convenience method
     * call $this->renderMenu() in the view to access 
     * the helper
     *
     * @access public
     * @return string
     */
    public function renderMenu(){

        $navigation = App_Agent_Navigation::getInstance()->getNavigation();
        //echo "<pre>";print_r($navigation);exit;
        $baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
        
        $menu = array();
        foreach ($navigation as $tab) {
            $tab = $tab['main'];
            
            if(isset($tab['action'])){
                $url = $baseUrl . '/' . $tab['controller'] . '/' . $tab['action'];
            }else{
                $url = $baseUrl . '/' . $tab['controller'];
            }
            //echo "<pre>";print($this->_linkSelectedTemplate."==".$url."===".$tab['label']);exit;
            if (isset($tab['active']) && $tab['active']) {
                $li = sprintf($this->_linkSelectedTemplate, Util::formatURL($url), $tab['label']);
            } else {
                $li = sprintf($this->_linkTemplate, Util::formatURL($url), $tab['label']);
            }
            
            $menu[] = $li;
        }
        
        //$xhtml = '<ul id="nav" class="prefix_1">' . PHP_EOL . implode(PHP_EOL, $menu) . PHP_EOL . '</ul>';
        $xhtml = '<ul  class="mainnav">' . PHP_EOL . implode(PHP_EOL, $menu) . PHP_EOL . '</ul>';//For New UI
        
        return $xhtml;
    }
}