<?php
/**
 * Form that allows the user to change his/her password
 *
 * @category backoffice
 * @package backoffice
 * @subpackage backoffice_forms
 * @copyright company
 */

class NewPasswordForm extends App_Corporate_Form
{
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
        
        
        
        
        $complexityValidator = new Zend_Validate_Regex('/^(?=.*\d)(?=.*[a-z|A-Z]).{7,}$/');
        $complexityValidator->setMessage('The selected password does not meet the required complexity requirements');
        
        $stringLengthValidator = new Zend_Validate_StringLength();
        $stringLengthValidator->setMin(7);
        $stringLengthValidator->setMessage('Your password must be at least 7 characters long');
        
        
        
       
        
        $password = new Zend_Form_Element_Password('password');
        $password->setOptions(
            array(
                'label'      => 'New password *',
                'required'   => TRUE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                    $stringLengthValidator,
                                    $complexityValidator,
                                    ),
                'maxlength' => '40',
            )
        );
        $this->addElement($password);
        
        $sameAsValidator = new App_Validate_SameAs($password);
        $sameAsValidator->setMessage('The two passwords do not coincide.', 
                                     App_Validate_SameAs::NOT_THE_SAME);
        
        $retypeNewPassword = new Zend_Form_Element_Password('retype_new_password');
        $retypeNewPassword->setOptions(
            array(
                'label'      => 'Retype new password *',
                'required'   => TRUE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                    $sameAsValidator,
                                ),
                'maxlength' => '40',
            )
        );
        $this->addElement($retypeNewPassword);
        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'label'      => 'Save Password',
                'required'   => FALSE,
                'title'       => 'Save Password',
                'class'     => 'tangerine',
            )
        );
        $this->addElement($submit);
        
        $this->setElementDecorators(array(
                    'viewHelper',
                    'Errors',
                    array(array('data'=>'HtmlTag'),array('tag'=>'dd','class'=>'form-field-column edit')),
                    array('Label',array('tag'=>'dt','class'=>'form-name-column')),
                    //array('Label',array('tag'=>'div')),
                   // array(array('row'=>'HtmlTag'),array('tag'=>'div','class'=>'formrow')),
        ));
                // We want to display a 'failed authentication' message if necessary;
        // we'll do that with the form 'description', so we need to add that
        // decorator.
        $this->setDecorators(array(
            'FormElements',
            //array('HtmlTag', array('tag' => 'div', 'class' => 'innerbox')),
            array(array('Value'=>'HtmlTag'), array('tag'=>'dl','class'=>'innerbox form')),
            array('Description', array('placement' => 'prepend')),
            'Form'
        ));
        
    }
}