<?php
/**
 * User login form
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright company
 */

class LoginForm extends App_Bank_Form
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
                                    'NotEmpty', array('StringLength', false, array(4, 50)),
                                ),
                'class'     => 'bank-login-password'                
                //'class'     => 'input-bg1'
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
                'class'     => 'bank-login-password'
                //'before'    => '<label><i class="icon-phone"></i>Phone no</label>',
            )
        );
        $this->addElement($password);
        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'label'      => 'Log in →',
                'required'   => false,
                //'class'     => 'btn right1',
                'title'       => 'Log in →',
                
            )
        );
        $this->addElement($submit);
        
//         $this->setElementDecorators(array(
//                  //  'viewHelper',
//                    'Errors',
//                    array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'inputbox'))
//        ));
        $this->setDecorators(array(
            'FormElements',
            'Form'
        )); 

    }
}