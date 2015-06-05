<?php
/**
 * Form that allows the user to change his/her password
 *
 * @category backoffice
 * @package backoffice
 * @subpackage backoffice_forms
 * @copyright company
 */

class ChangePasswordForm extends App_Agent_Form
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
        
        $user = Zend_Auth::getInstance()->getIdentity();
        
        $oldPasswordValidator = new App_Validate_PasswordExists(
            array(
                'table' => DbTable::TABLE_AGENTS,
                'field' => 'password',
                'treatment' => 'BaseUser::hashPassword',
                'userPkValue' => $user->id,
            )
        );
        
        $complexityValidator = new Zend_Validate_Regex('/^.*(?=.{7,})(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[=!:.#%&*()$@=<>,.?-])(^[a-zA-Z0-9@\$=!:.#%&*()=<>,.?-]+$)/');
        //^.*(?=.{7,20})(?=.*\d)(?=.*[A-Z])(^[a-zA-Z0-9@\$=!:.#%&*()=<>,.?-_]+$
        $complexityValidator->setMessage('The selected Password must be 7 to 20 chars long including alteast one capital letter, one digit and one special character (any of =!:.#%&*()$@=<>,.?-)');
        
        $stringLengthValidator = new Zend_Validate_StringLength();
        $stringLengthValidator->setMin(7);
        $stringLengthValidator->setMessage('Your password must be at least 7 characters long');
        
//        $passwordHistoryValidator = new App_Validate_NoPasswordExists(
//            array(
//                'table' => DbTable::TABLE_AGENTS,
//                'field' => 'password',
//                'treatment' => 'BaseUser::hashPassword',
//                'userPkField' => 'id',
//                'userPkValue' => $user->id,
//            )
//        );
        
        $oldPassword = new Zend_Form_Element_Password('old_password');
        $oldPassword->setOptions(
            array(
                'label'      => 'Old password *',
                'required'   => TRUE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                    $oldPasswordValidator,
                                ),
                'maxlength' => '20',
            )
        );
        $this->addElement($oldPassword);
        
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
                                    //$stringLengthValidator,
                                    $complexityValidator,
                                    //$passwordHistoryValidator,
                                ),
                'maxlength' => '20',
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
            )
        );
        $this->addElement($retypeNewPassword);
        
       
        $submit = new Zend_Form_Element_Submit('btn_mob');
        $submit->setOptions(
            array(
                'label'      => 'Save password',
                'required'   => FALSE,
                'title'       => 'Save password',
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