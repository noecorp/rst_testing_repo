<?php
/**
 * Allows users to update their profiles 
 *
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright company
 */

class ProfileForm extends App_Operation_Form
{
    /**
     * Overrides init() in Zend_Form
     * 
     * @access public
     * @return void
     */
    public function init() {
        
        $this->_cancelLink = false;
        // init the parent
        parent::init();
        
        // set the form's method
        $this->setMethod('post');
        
        $username = new Zend_Form_Element_Text('username');
        $username->setOptions(
            array(
                'label'      => 'Username *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                ),
                'readonly'   => 'readonly',
            )
        );
        $this->addElement($username);
        
        $email = new Zend_Form_Element_Text('email');
        $email->setOptions(
            array(
                'label'      => 'Email Address *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                    'EmailAddress',
                                ),
                'maxlength'  => 100
            )
        );
        $this->addElement($email);
        
        $firstname = new Zend_Form_Element_Text('firstname');
        $firstname->setOptions(
            array(
                'label'      => 'First Name *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                ),
                'maxlength'  => 20
            )
        );
        $this->addElement($firstname);
        
        $lastname = new Zend_Form_Element_Text('lastname');
        $lastname->setOptions(
            array(
                'label'      => 'Last Name *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                ),
                'maxlength'  => 30
            )
        );
        $this->addElement($lastname);
        
        $phoneNumber = new Zend_Form_Element_Text('mobile1');
        $phoneNumber->setOptions(
            array(
                'label'      => 'Phone Number *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                    'Digits'
                                ),
                'maxlength'  => 10
            )
        );
        $this->addElement($phoneNumber);
        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'label'      => 'Save',
                'required'   => FALSE,
                'title'       => 'Save',
                'class'     => 'tangerine',
            )
        );
        $this->addElement($submit);
        
        $this->setElementDecorators(array(
                    'viewHelper',
                    'Errors',
                    array(array('data'=>'HtmlTag'),array('tag'=>'dd','class'=>'form-field-column edit')),
                    array('Label',array('tag'=>'dt','class'=>'form-name-column')),
                   
        ));
               
        $this->setDecorators(array(
            'FormElements',
          
            array(array('Value'=>'HtmlTag'), array('tag'=>'dl','class'=>'innerbox form')),
            array('Description', array('placement' => 'prepend')),
            'Form'
        ));
        
    }
}