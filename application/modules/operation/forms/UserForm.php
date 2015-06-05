<?php
/**
 * Form for editing users
 *
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright company
 */

class UserForm extends App_Operation_Form
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
        
        $groupModel = new Group();
        $groupsOptions = $groupModel->findPairs();
        
        $uniqueUsernameValidator = new Zend_Validate_Db_NoRecordExists(
            array(
                'table' => DbTable::TABLE_OPERATION_USERS,
                'field' => 'username',
            )
        );
        
        $uniqueEmailValidator = new Zend_Validate_Db_NoRecordExists(
            array(
                'table' => DbTable::TABLE_OPERATION_USERS,
                'field' => 'email',
            )
        );
        
        $groupsInArrayValidator = new Zend_Validate_InArray(array_keys($groupsOptions));
        $groupsInArrayValidator->setMessage('Please select at least one group. If you are not sure about which group is better, select "member".');
        
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
                                    $uniqueUsernameValidator,
                                ),
                'maxlength'  => 25,
            )
        );
        $this->addElement($username);
        
        $email = new Zend_Form_Element_Text('email');
        $email->setOptions(
            array(
                'label'      => 'Email',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                    $uniqueEmailValidator,
                                ),
                'maxlength'  => 100,
            )
        );
        $this->addElement($email);
        
        $firstname = new Zend_Form_Element_Text('firstname');
        $firstname->setOptions(
            array(
                'label'      => 'First Name',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                ),
                'maxlength'  => 20,
            )
        );
        $this->addElement($firstname);
        
        $lastname = new Zend_Form_Element_Text('lastname');
        $lastname->setOptions(
            array(
                'label'      => 'Last Name',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                ),
                'maxlength'  => 25,
            )
        );
        $this->addElement($lastname);
        
        $phoneNumber = new Zend_Form_Element_Text('mobile1');
        $phoneNumber->setOptions(
            array(
                'label'      => 'Phone Number',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', 'Digits',
                                ),
                'maxlength'  => 10,
            )
        );
        $this->addElement($phoneNumber);
        
        $groups = new Zend_Form_Element_Select('groups');
        $groups->setOptions(
            array(
                'label'      => 'Select User Group',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                    $groupsInArrayValidator,
                                ),
                'multiOptions' => $groupsOptions,
            )
        );
        $this->addElement($groups);
        
        $id = new Zend_Form_Element_Hidden('id');
        $id->setOptions(
            array(
                'validators' => array(
                    // either empty or numeric
                    new Zend_Validate_Regex('/^\d*$/'),
                ),
            )
        );
        $this->addElement($id);
        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'label'      => 'Save User',
                'required'   => FALSE,
                'title'       => 'Save User',
                'class'     => 'tangerine',
                'order'     => 10
            )
        );
        $this->addElement($submit);
        
        
        $this->addDisplayGroup(array('username', 'email', 'firstname', 'lastname', 'mobile1'), 'userdata')
             ->getDisplayGroup('userdata')
             ->setLegend('User Details');
         
        $this->addDisplayGroup(array('groups'), 'usergroups')
             ->getDisplayGroup('usergroups')
             ->setLegend('Groups');
        
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
    
    /**
     * Overrides isValid() in App_Form
     * 
     * @param array $data 
     * @access public
     * @return bool
     */
    public function isValid($data){
        if (isset($data['id']) && is_numeric($data['id'])) {
            $this->getElement('username')
                ->getValidator('Zend_Validate_Db_NoRecordExists')
                ->setExclude(array(
                     'field' => 'id',
                     'value' => $data['id']
                ));
            
            $this->getElement('email')
                ->getValidator('Zend_Validate_Db_NoRecordExists')
                ->setExclude(array(
                    'field' => 'id',
                    'value' => $data['id']
                ));
        }
        
        return parent::isValid($data);
    }
}