<?php
/**
 * User login form
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright company
 */

class AuthcodeForm extends App_Corporate_Form
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
        //parent::init();
        
        // set the form's method
        $this->setMethod('post');
        
        $username = new Zend_Form_Element_Text('authcode');
        $username->setOptions(
            array(
               // 'label'      => 'Authorization Code *',
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
                'style'     => 'margin-left:80px;margin-bottom:10px;'
                //'class'     => 'input-bg5',
            )
        );
        $this->addElement($username);
        
        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'label'      => 'Log in →',
                'required'   => FALSE,
                'title'       => 'Log in →',
                 //'class'     => 'btn right1'
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
                    //array('Label',array('tag'=>'div')),
                    array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'inputbox2'))
        ));
        $this->setDecorators(array(
            'FormElements',
           // array(array('Value'=>'HtmlTag'), array('tag'=>'div',)),
            //array('Description', array('placement' => 'prepend')),
            'Form'

        )); 
        
      
    }
}