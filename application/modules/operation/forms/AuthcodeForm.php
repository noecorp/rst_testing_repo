<?php
/**
 * User login form
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright company
 */

class AuthcodeForm extends App_Operation_Form
{
    /**
     * This form does not have a cancel link
     * 
     * @var mixed
     * @access protected
     */
    protected $_cancelLink = false;
    
    /**
     * Overrides init() in Zend_Form
     * 
     * @access public
     * @return void
     */
    public function init() {
        // init the parent
        parent::init();
        
        // set the form's method
        $this->setMethod('post');
        
        $username = new Zend_Form_Element_Text('authcode');
        $username->setOptions(
            array(
                'label'      => 'Authorization Code:',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                    'Digits'
                                ),
                 'maxlength' => '6',
                'class'     => 'input-bg5',
            )
        );
        $this->addElement($username);
        
        
        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'ignore'   => true,
                'label'      => 'Log in →',
                //'required'   => true,
                'class'     => 'btn right1',
                'title'       => 'Log in →',
            )
        );
        $submit->removeDecorator('Label');
        $this->addElement($submit);
        
        /**
         * for new UI
         */
         $this->setElementDecorators(array(
                    'viewHelper',
                    'Errors',
                    //array(array('data'=>'HtmlTag'),array('tag'=>'div')),
                    //array('Label',array('tag'=>'div')),
                    array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'inputbox'))
        ));
        $this->setDecorators(array(
            'FormElements',
           // array(array('Value'=>'HtmlTag'), array('tag'=>'div',)),
            //array('Description', array('placement' => 'prepend')),
            'Form'

        )); 
        
    }
}