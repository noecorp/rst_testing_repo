<?php

class Remit_Boi_AddRemitterDetailsForm extends App_Agent_Form
{
  
    public function  init()
    {       
        
         $bankList = new BanksIFSC();
         $bankListOptions = $bankList->getBank();
         $bankAccountType = Util::getBankAccountType();
        
        
        $product_id = $this->addElement('select', 'product_id', array(
            'filters'    => array('StringTrim'),            
            'required'   => true,
            'label'      => 'Product Name: *',
            'style'     => 'width:258px;',
        ));
        
        
         $regn_fee = new Zend_Form_Element_Text('regn_fee');
         $regn_fee->setOptions(
            array(
                'label'      => 'Remitter Registration Fee',
                'required'   => FALSE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'maxlength'  => '10',
                'readonly' => 'readonly',
            )
        );
        
        $this->addElement($regn_fee);
        
        $RemNameComplexityValidator = new Zend_Validate_Regex('/^[a-z\s]+$/i');
        $RemNameComplexityValidator->setMessage('Remitter name must include alphabets only');
        $RemNameStrLengthValidator = new Zend_Validate_StringLength();
        $RemNameStrLengthValidator->setMin(2);
        $RemNameStrLengthValidator->setMax(100);
        $RemNameStrLengthValidator->setMessage('Remitter name must be between 2 to 100 alphabets long');
        
        $name = new Zend_Form_Element_Text('name');
         $name->setOptions(
            array(
                'label'      => 'Remitter Name *',
                'required'   => TRUE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                        'NotEmpty',//,'Alpha' ,array('StringLength', false, array(2, 100))),
                                        $RemNameComplexityValidator,
                                        $RemNameStrLengthValidator
                                        //array('Regex', FALSE, array('pattern' => '/[a-z][A-Z] /')),
                                     ),
                'maxlength'  => '100',
                
            )
        );
        
        $this->addElement($name);
        
        $bankname = new Zend_Form_Element_Select('bank_name');
        $bankname->setOptions(
            array(
                    'label'      => 'Bank Name',
                    'required'   => false,
                    'filters'    => array(
                                        'StringTrim',
                                        'StripTags',
                                    ),
                    'validators' => array(
                                       'NotEmpty',
                                    ),
                    'style' => 'width:210px;',
                    'maxlength' => '100',
                    'multioptions'    => $bankListOptions,         
            )
        );
        $this->addElement($bankname);
        
        $ifsc_code = new Zend_Form_Element_Select('ifsc_code');
        $ifsc_code->setOptions(
            array(
                'label'      => 'IFSC Code',
               
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty'
                                ),
                'multioptions'    => array('' =>'Select IFSC Code'),
                'style' => 'width:210px;',
            )
        );
        $ifsc_code->setRegisterInArrayValidator(false);
        $this->addElement($ifsc_code);      
        
        
        
       $bank_account_number = new Zend_Form_Element_Text('bank_account_number');
        $bank_account_number->setOptions(
            array(
                'label'      => 'Bank Account No.',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty','Digits',
                                ),
                 'maxlength' => '35',
                 'renderPassword'=>true
            )
        );
        $this->addElement($bank_account_number);
        
        
        $bank_account_type = new Zend_Form_Element_Select('bank_account_type');
        $bank_account_type->setOptions(
            array(
                    'label'      => 'Bank Account Type',
                    'required'   => false,
                    'filters'    => array(
                                        'StringTrim',
                                        'StripTags',
                                    ),
                    'validators' => array(
                                        'NotEmpty', array('StringLength', false, array(4, 35)),
                                    ),
                    'style' => 'width:210px;',
                    'maxlength' => '35',
                    'multioptions'    => $bankAccountType,         
            )
        );
        $this->addElement($bank_account_type);
        
      
        $branch_name = new Zend_Form_Element_Text('branch_name');
        $branch_name->setOptions(
            array(
                'label'      => 'Branch Name',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                   'NotEmpty',
                                ),
                'maxlength' => '100',
            )
        );
        $this->addElement($branch_name);
        
        
        
        
        $branch_city = new Zend_Form_Element_Text('branch_city');
        $branch_city->setOptions(
            array(
                'label'      => 'Branch City',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                  'NotEmpty',
                                ),
                'maxlength' => '50',
            )
        );
        $this->addElement($branch_city);
        
        
        $branch_address = new Zend_Form_Element_Text('branch_address');
        $branch_address->setOptions(
            array(
                'label'      => 'Branch Address',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                  'NotEmpty',
                                ),
                'maxlength' => '250',
            )
        );
        $this->addElement($branch_address);
        
        
        
        
        
        $address = $this->addElement('text', 'address', array(
            'filters'    => array('StringTrim'),            
            'required'   => true,
            'label'      => 'Address *',
            'maxlength' => '255',
        ));
        
        $mobile_country_code = $this->addElement('select', 'mobile_country_code', array(

            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(1, 6)),),
            'required'   => true,
            'label'      => 'Mobile Country Code: *',
            'style'     => 'width:200px;',
            'multiOptions' => array_merge(array(''=>'Select'),Mobile::getCountryCodes()),
        ));
        
        $mobile = $this->addElement('text', 'mobile', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty','Digits', array('StringLength', false, array(10, 10)),),
            'required'   => true,
            'label'      => 'Mobile Number: *',
            'style'     => 'width:200px;',
            'maxlength'  => '10',
            //'readonly' => true
           
        ));
        
        $btn_auth_code = $this->addElement('button', 'btn_auth_code', array(
            'required' => false,
            'ignore'   => true,
            'label'    => 'Send Authorization Code',
            'onclick'     => "javascript:sendAuthCode();",
            'class'     => 'tangerine',
        )); 
        
        $auth_code = $this->addElement('text', 'auth_code', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(6, 6)),),
            'required'   => true,
            'label'      => 'Authorization Code: *',
            'style'     => 'width:200px;',
            'maxlength'  => '6',
        ));   
        
        
         $this->addElement(new ZendX_JQuery_Form_Element_DatePicker('dob',
            array('jQueryParams' => array('dateFormat' => 'dd-mm-yy'),
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(10, 20)),),
            'required'   => false,
            'label'      => 'Date of Birth: *(e.g. dd-mm-yyyy) ',
            'maxlength'  => '20',
            'style'     => 'width:200px;',)

        )); 
         
         
         
        $mother_m_name = new Zend_Form_Element_Text('mother_maiden_name');
        $mother_m_name->setOptions(
            array(
                'label'      => 'Mother Maiden Name *',
                'required'   => TRUE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty' ,array('StringLength', false, array(2, 25)),
                                ),
                'maxlength'  => '25',
            )
        );
        
        $this->addElement($mother_m_name);
        $mother_m_name->addValidator('Alpha', true, array('allowWhiteSpace' => true));
        
        
         $email = $this->addElement('text', 'email', array(
            'filters'    => array('StringTrim'),
            'validators' => array('EmailAddress', array('StringLength', false, array(5, 50)),),
            'required'   => false,
            'label'      => 'Email:',
            'style'     => 'width:200px;', 
            'maxlength'  => '50',
        )); 
       
       $profile_pic = new Zend_Form_Element_File('profile_pic');
       $profile_pic->setLabel('Profile Photo Path')
	         ->setRequired(false)
                 ->addValidator(new Zend_Validate_File_Size('5MB'));
       $this->addElement($profile_pic);
         
        $email_old = $this->addElement('hidden', 'email_old', array(
        ));
        
        $send_auth_code = $this->addElement('hidden', 'send_auth_code', array(
        ));
        
                
        $submit = new Zend_Form_Element_Submit('btn_add');
        $submit->setOptions(
            array(
                'label'      => 'Add Details',
                'required'   => FALSE,
                'class'     => 'tangerine',
                'title'      => 'Add Details',
                //'onclick'    => 'Javascript:checkDOB();',
            )
        );
        $this->addElement($submit);
         
         $mobile_old = $this->addElement('hidden', 'mobile_old', array(
        ));
         
         
        // We want to display a 'failed authentication' message if necessary;
        // we'll do that with the form 'description', so we need to add that
        // decorator.
        
        
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
?>
