<?php
/**
 * Form that allows the user to change his/her password
 *
 * @category backoffice
 * @package backoffice
 * @subpackage backoffice_forms
 * @copyright company
 */

class ChangePasswordForm extends App_Operation_Form
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
        
        $user = Zend_Auth::getInstance()->getIdentity();
        
        $oldPasswordValidator = new App_Validate_PasswordExists(
            array(
                'table' => DbTable::TABLE_OPERATION_USERS,
                'field' => 'password',
                'treatment' => 'BaseUser::hashPassword',
                'userPkValue' => $user->id,
            )
        );
       
        $complexityValidator = new Zend_Validate_Regex('/^.*(?=.{7,})(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[=!:.#%&*()$@=<>,.?-])(^[a-zA-Z0-9@\$=!:.#%&*()=<>,.?-]+$)/');
        $complexityValidator->setMessage('The selected Password must be 7 to 20 chars long including alteast one capital letter, one digit and one special character (any of =!:.#%&*()$@=<>,.?-)');
        
        $stringLengthValidator = new Zend_Validate_StringLength();
        $stringLengthValidator->setMin(7);
        $stringLengthValidator->setMessage('Your password must be at least 7 characters long');
        
//        $passwordHistoryValidator = new App_Validate_NoPasswordExists(
//            array(
//                'table' => DbTable::TABLE_OPERATION_USERS,
//                'field' => 'password',
//                'treatment' => 'OperationUser::hashPassword',
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
        $sameAsValidator->setMessage('The two passwords do not match.', 
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
                'maxlength' => '20',
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
                   
        ));
               
        $this->setDecorators(array(
            'FormElements',
          
            array(array('Value'=>'HtmlTag'), array('tag'=>'dl','class'=>'innerbox form')),
            array('Description', array('placement' => 'prepend')),
            'Form'
        ));
        
    }
}