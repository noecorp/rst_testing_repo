<?php
/**
 * Parent form for all the backoffice forms
 *
 * @category App
 * @package App_Backoffice
 * @copyright company
 */

abstract class App_Agent_Form extends App_Form
{
    /**
     * URL for the cancelLink
     * 
     * @var mixed
     * @access protected
     */
    protected $_cancelLink = FALSE;
    
    public $buttonDecorators = array(
        'ViewHelper',
        array('HtmlTag', array('tag' => 'div', 'class' => 'form-button'))
    );
    
    /**
     * Overrides init() in App_Form
     * 
     * @access public
     * @return void
     */
    public function init(){
        parent::init();
        
        $config = App_DI_Container::get('ConfigObject');
        
        // add an anti-CSRF token to all forms
        $csrfHash = new Zend_Form_Element_Hash('csrfhash');
        $csrfHash->setOptions(
            array(
                'required'   => TRUE,
                'filters'    => array(
                    'StringTrim',
                    'StripTags',
                ),
                'validators' => array(
                    'NotEmpty',
                ),
                'salt' => $config->security->csrfsalt . get_class($this),
            )
        );
        $this->addElement($csrfHash);

       $formName = new Zend_Form_Element_Hidden('formName');
        $formName->setOptions(
            array(
                'filters'    => array(
                    'StringTrim',
                    'StripTags',
                ),
                'value' => get_class($this)
            )
        );
        $this->addElement($formName);        
   
    }
    
    /**
     * Overrides render() in App_Form
     * 
     * @param Zend_View_Interface $view 
     * @access public
     * @return string
     */
    public function render(Zend_View_Interface $view = NULL){
        //echo "<pre>";print_r($this->getElements());exit;
        foreach($this->getElements() as $element) {
            //echo "<pre>";print_r($element);
            $this->_replaceLabel($element);
            
            switch(TRUE){
                case $element instanceof Zend_Form_Element_Hidden:
                case $element instanceof Zend_Form_Element_Hash:
                    $this->_addHiddenClass($element);
                    break;
                case $element instanceof Zend_Form_Element_Checkbox:
                   // $this->_appendLabel($element);
                    break;
//                 case $element instanceof Zend_Form_Element_Button:
//                 
//                    $element->setDecorators(array(
//                                      'viewHelper',
//                                      'Errors',
//                                      array(array('data'=>'HtmlTag'),array('tag'=>'dd','class'=>'form-field-column edit','style' => 'float: left;')),
//                                      array('Label',array('tag'=>'dt','class'=>'form-name-column')),
//
//                          ));
//                    
//                    $element->removeDecorator('Label');
//                    //$this->_cancelSubmitLink($element);
//                    break;
                case $element instanceof Zend_Form_Element_Submit:
                 
                    $element->setDecorators(array(
                                      'viewHelper',
                                      'Errors',
                                      array(array('data'=>'HtmlTag'),array('tag'=>'dd','class'=>'form-field-column edit','style' => 'float: left; clear: both;')),
                                      array('Label',array('tag'=>'dt','class'=>'form-name-column')),

                          ));
                    
                    $element->removeDecorator('Label');
                    //$this->_cancelSubmitLink($element);
                    break;
                
               
                
                case $element instanceof ZendX_JQuery_Form_Element_DatePicker:
                    $this->_appendLabel($element);                    
                    $element->setDecorators(array(
                        'UiWidgetElement',
                        array(array('data'=>'HtmlTag'),array('tag'=>'dd','class'=>'form-field-column edit')),
                        array('Label',array('tag'=>'dt','class'=>'form-name-column')),
                    ));
                    //$decorators = $element->getDecorators();
                    //$element->clearDecorators();
                    break;
                case $element instanceof Zend_Form_Element_File:
                    //echo "<pre>";print_r($element);
                    $this->_appendLabel($element);                    
                    $element->setDecorators(array(
                        'File',
                        array(array('data'=>'HtmlTag'),array('tag'=>'dd','class'=>'form-field-column edit')),
                        array('Label',array('tag'=>'dt','class'=>'form-name-column')),
                    ));
                    //$decorators = $element->getDecorators();
                    //$element->clearDecorators();
                    break;
                
                case $element instanceof Zend_Form_Element_MultiCheckbox:
                    $element->getDecorator('Label')->setOption('tagOptions', array('class' => 'checkboxGroup'));
                    $element->getDecorator('HtmlTag')->setOption('class', 'checkboxGroup');
                    break;
                
                 case $element instanceof Zend_Form_Element_Text:
                    //$this->_appendLabel($element);                    
                    $class = $element->getAttrib("addRupeeSymbol");
                    //echo "<pre>";print_r($class);
                    if($class == true) {
                        $element->removeDecorator("addRupeeSymbol");
                        //$element->
                         $element->setDecorators(array(
                                'viewHelper',
                                'Errors',
                                array(array('data'=>'HtmlTag'),array('tag'=>'dd','class'=>'form-field-column-webrupee edit WebRupee WebRupeeText')),
                                array('Label',array('tag'=>'dt','class'=>'form-name-column')),
                        ));
                    }
                    break;                
            }
        }
        $this->_cancelLink();
        /*if(!empty($this->getDecorator('HtmlTag'))) {
            $this->getDecorator('HtmlTag')->setOption('class', 'zend_form clearfix');
        }*/
        
        if (NULL === $this->getAttrib('id')) {
            $controllerName = Zend_Registry::get('controllerName');
            $actionName = Zend_Registry::get('actionName');
            
            $this->setAttrib('id', $controllerName . '-' . $actionName);
        }

        
        return parent::render($view);
    }
    
    /**
     * Add the hidden class
     * 
     * @param Zend_Form_Element_Abstract $element 
     * @access protected
     * @return void
     */
    protected function _addHiddenClass($element){
        $label = $element->getLabel();
        if (empty($label)) {
            $element->setLabel('');
        }
        
        $decorator = $element->getDecorator('HtmlTag');
        if(!empty($decorator)) {
            $decorator->setOption('class', 'hidden');
        }
        $decorator = $element->getDecorator('Label');
        if(!empty($decorator)) {
            $decorator->setOption('tagOptions', array('class' => 'hidden'));
           // $element->clearDecorators();
        }
    }
    
    /**
     * Forces the element's label to be appended to it rather
     * than prepend it
     * 
     * @param Zend_Form_Element_Abstract $element 
     * @access protected
     * @return void
     */
    protected function _appendLabel($element){
        $element->getDecorator('Label')
                ->setOption('placement', Zend_Form_Decorator_Abstract::APPEND);
    }
    
    /**
     * Replaces the default label decorator with a more
     * versatile one
     * 
     * @param Zend_Form_Element_Abstract $element 
     * @access protected
     * @return void
     */
    protected function _replaceLabel($element){
        
        /*$decorators = $element->getDecorators();
        //echo "<pre>";print_r($decorators);exit;
        if (isset($decorators['Zend_Form_Decorator_Label'])) {
            $newDecorators = array();
            foreach ($decorators as $key => $decorator) {
                if ($key === 'Zend_Form_Decorator_Label') {
                    $label = new App_Form_Decorator_Label();
                    $label->setOptions($decorator->getOptions());
                    
                    $newDecorators['App_Form_Decorator_Label'] = $label;
                } else {
                    $newDecorators[$key] = $decorator;
                }
            }
            $element->clearDecorators();
            $element->setDecorators($newDecorators);
        }*/
         
    }
    
    
    /**
     * Adds a cancel link to the form
     * 
     * @access protected
     * @return void
     */
    protected function _cancelLink(){
        
        if ($this->_cancelLink !== FALSE) {
            
            if ($this->_cancelLink === NULL) {
                $this->_cancelLink = '/' . Zend_Registry::get('controllerName').'/';
            }
           
            $cancelLink = Zend_Controller_Front::getInstance()->getBaseUrl() . $this->_cancelLink;
            
            $cancelLinkDecorator = new App_Form_Decorator_Backlink();
            $cancelLinkDecorator->setOption('url', $cancelLink);
            
            //$element = $this->getElement('submit');
            
            $element = $this->getElement('submit');
            if(!empty($element)) {
            $decorators = $element->getDecorators();
            //$decorators = $element->getDecorators();
            $element->clearDecorators();
            
            foreach($decorators as $decorator) {
                $element->addDecorator($decorator);
                if ($decorator instanceof Zend_Form_Decorator_ViewHelper) {
                    $element->addDecorator($cancelLinkDecorator);
                }
            }
            }
        }
    }
    
    
    /**
     * Setter for $this->_cancelLink
     *
     * @param string $cancelLink
     * @access public
     * @return void
     */
    public function setCancelLink($cancelLink){
        $this->_cancelLink = $cancelLink;
    }
    
    /**
     * Getter for $this->_cancelLink
     *
     * @access public
     * @return string
     */
    public function getCancelLink(){
        if (NULL === $this->_cancelLink) {
            $this->_cancelLink = '/' . Zend_Registry::get('controllerName') . '/';
        }
        
        return $this->_cancelLink;
    }
}