<?php
/**
 * User login form
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright company
 */

class LoginForm extends App_Corporate_Form
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
       
        $username = new Zend_Form_Element_Text('username');
        $username->setOptions(
            array(
                'label'      => 'Username',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                ),
                //'class'     => 'row'
            )
        );
        $this->addElement($username);
        
        
        $password = new Zend_Form_Element_Password('password');
        $password->setOptions(
            array(
                'label'      => 'Password',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                ),
                'class'     => 'row123'
            )
        );
               
        $this->addElement($password);
        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'label'      => 'LOGIN',
                'required'   => false,
                'class'     => 'btn right1 row',
                'title'       => 'Login',
            )
        );
        $this->addElement($submit);
        
        
        /**
         * for new UI
         */
         $this->setElementDecorators(array(
                    'viewHelper',
                    'Errors',
                    //array(array('data'=>'HtmlTag'),array('tag'=>'div')),
                    array('Label'),
                    array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'row'))
        ));
        $this->setDecorators(array(
            'FormElements',
           array(array('Value'=>'HtmlTag'), array('tag'=>'div',)),
            array('Description', array('placement' => 'prepend')),
            'Form'

        )); 
        
        /**
         * for new UI
         * /
         $this->setElementDecorators(array(
                    'viewHelper',
                    'Errors',
                    array(array('data'=>'HtmlTag'),array('tag'=>'div')),
                    array('Label',array('tag'=>'div')),
                    array(array('row'=>'HtmlTag'),array('tag'=>'div'))
        ));
        $this->setDecorators(array(
            'FormElements',
            array(array('Value'=>'HtmlTag'), array('tag'=>'div','class'=>'zend_form_custom')),
            //array('Description', array('placement' => 'prepend')),
            'Form'

        )); */
    }
}