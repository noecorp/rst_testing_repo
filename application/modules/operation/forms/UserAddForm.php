<?php
/**
 * Form for adding users 
 *
 * It extends the UserForm and adds additional password fields
 *
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright Local Billing Lid.
 */

class UserAddForm extends UserForm
{
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
                'maxlength'  => 12,
            )
        );
        $this->addElement($password);
        
        $sameAsValidator = new App_Validate_SameAs($password);
        $sameAsValidator->setMessage('The two passwords do not coincide.', 
                                     App_Validate_SameAs::NOT_THE_SAME);
        
        $retypePassword = new Zend_Form_Element_Password('retypePassword');
        $retypePassword->setOptions(
            array(
                'label'      => 'Retype password',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                    $sameAsValidator,
                                ),
                'maxlength'  => 12,
            )
        );
        $this->addElement($retypePassword);
        
        $this->addDisplayGroup(array('password', 'retypePassword'), 'passwords')
             ->getDisplayGroup('passwords')
             ->setLegend('Password (the user will be forced to changed it on the first login)');
        
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