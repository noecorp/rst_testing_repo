<?php
/*
 * Add Remitter Form
 */
class Remit_Kotak_AddRemitterDetailsForm extends App_Agent_Form
{
  
    public function  init()
    {       
        
         $bankList = new BanksIFSC();
         $bankListOptions = $bankList->getBank();
         $bankAccountType = Util::getBankAccountType();
         $statelist = new CityList();
         $stateOptionsList = $statelist->getStateList($countryCode = 356);
        
        $product_id = $this->addElement('select', 'product_id', array(
            'filters'    => array('StringTrim'),            
            'required'   => true,
            'label'      => 'Product Name: *',
            'style'     => 'width:258px;background:#FFFF99',
        ));
        
        
         $regnfee = new Zend_Form_Element_Text('regnfee');
         $regnfee->setOptions(
            array(
                'label'      => 'Remitter Registration Fee',
                'required'   => FALSE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'maxlength'  => '10',
                'readonly' => 'readonly',
                'disabled' => 'disabled',
                'style'     => 'background:#EAEBEC',
                
            )
        );
        
        $this->addElement($regnfee);
        
        
        $RemNameComplexityValidator = new Zend_Validate_Regex('/^[a-z\s]+$/i');
        $RemNameComplexityValidator->setMessage('Remitter name must include alphabets only');
        $RemNameStrLengthValidator = new Zend_Validate_StringLength();
        $RemNameStrLengthValidator->setMin(2);
        $RemNameStrLengthValidator->setMax(100);
        $RemNameStrLengthValidator->setMessage('Remitter name must be between 2 to 100 alphabets long');
        
        $name = new Zend_Form_Element_Text('name');
         $name->setOptions(
            array(
                'label'      => 'First Name *',
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
                'style'     => 'background:#FFFF99',
                
            )
        );
        
        $this->addElement($name);
        
         /*$name = new Zend_Form_Element_Text('middle_name');
         $name->setOptions(
            array(
                'label'      => 'Middle Name',
                'required'   => FALSE,
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
        
        $this->addElement($name);*/
        
         $name = new Zend_Form_Element_Text('last_name');
         $name->setOptions(
            array(
                'label'      => 'Last Name *',
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
                'style'     => 'background:#FFFF99',
                
            )
        );
        
        $this->addElement($name);
        /*$mother_m_name = new Zend_Form_Element_Text('mother_maiden_name');
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
                'style'     => 'background:#FFFF99',
            )
        );
        
        $this->addElement($mother_m_name);
        $mother_m_name->addValidator('Alpha', true, array('allowWhiteSpace' => true));
        */
        
        $this->addElement(new ZendX_JQuery_Form_Element_DatePicker('dob',
            array('jQueryParams' => array('dateFormat' => 'dd-mm-yy'),
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(10, 20)),),
            'required'   => false,
            'label'      => 'Date of Birth: *(e.g. dd-mm-yyyy) ',
            'maxlength'  => '20',
            'style'     => 'width:200px;background:#FFFF99',)
            

        )); 
        
        
        $address = $this->addElement('text', 'address', array(
            'filters'    => array('StringTrim'),            
            'required'   => true,
            'label'      => 'Address Line *',
            'maxlength' => '255',
            'style'     => 'background:#FFFF99',
        ));
        
       /*$res_address2 = new Zend_Form_Element_Text('address_line2');
        $res_address2->setOptions(
            array(
                'label'      => 'Address Line 2 ',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', array('StringLength', false, array(3, 50)),
                                ),
                'maxlength' => '50',
            )
        );
        $this->addElement($res_address2);*/
        $pincode = new Zend_Form_Element_Text('pincode');
        $pincode->setOptions(
            array(
                'label'      => 'Pincode *',

                'required'   => True,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                
                'validators' => array(
                                    'NotEmpty','Digits', array('StringLength', false, array(6, 6)),
                                ),
                'style'     => 'background:#FFFF99',
                'maxlength' => '6',
            )
        );
        //$pincode->setRegisterInArrayValidator(false);
        $this->addElement($pincode); 
        
        $res_state = new Zend_Form_Element_Select('state');
        $res_state->setOptions(
            array(
                'label'      => 'State *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty'
                                ),
                'multioptions'    => $stateOptionsList,                       
                'style'     => 'background:#FFFF99',
            )
        );
        $this->addElement($res_state);

        
        $res_city = new Zend_Form_Element_Select('city');
        $res_city->setOptions(
            array(
                'label'      => 'City *',
               
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty'
                                ),
                'multioptions'    => array('' =>'Select City'),
                'style'     => 'background:#FFFF99',
            )
        );
        $res_city->setRegisterInArrayValidator(false);
        $this->addElement($res_city);   
        
        
       /* $mobile_country_code = $this->addElement('select', 'mobile_country_code', array(

            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(1, 6)),),
            'required'   => true,
            'label'      => 'Mobile Country Code: *',
            'style'     => 'width:200px;background:#FFFF99',
            'multiOptions' => array_merge(array(''=>'Select'),Mobile::getCountryCodes()),
        ));*/
       
				         
        
        $mobile = $this->addElement('text', 'mobile', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty','Digits', array('StringLength', false, array(10, 10)),),
            'required'   => true,
            'label'      => 'Mobile Number: *',
            'style'     => 'width:200px;background:#FFFF99',
            'maxlength'  => '10',
            'autocomplete'=> 'off',
            //'readonly' => true
           
        ));
        
        /*$ifsc_code = new Zend_Form_Element_Text('ifsc_code');
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
                'style' => 'width:210px;',
            )
        );
        
        $this->addElement($ifsc_code);      
        
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
        $bankstate = new Zend_Form_Element_Select('bank_state');
        $bankstate->setOptions(
            array(
                    'label'      => 'Branch State',
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
                    'multioptions'    => array('' =>'Select State'),
                    
            )
        );
        $bankstate->setRegisterInArrayValidator(false);
        $this->addElement($bankstate);      
        
        $branchcity = new Zend_Form_Element_Select('branch_city');
        $branchcity->setOptions(
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
                    'style' => 'width:210px;',
                    'maxlength' => '100',
                    'multioptions'    => array('' =>'Select City'),
                    
            )
        );
        $branchcity->setRegisterInArrayValidator(false);
        $this->addElement($branchcity);     
        
       $branch_name = new Zend_Form_Element_Select('branch_name');
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
                'multioptions'    => array('' =>'Select Branch'),
            )
        );
        $this->addElement($branch_name);
        
        
        
        
        
        
        
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
                 'renderPassword'=>true,
                'autocomplete'=> 'off',
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
        $this->addElement($bank_account_type);*/
         $email = $this->addElement('text', 'email', array(
            'filters'    => array('StringTrim'),
            'validators' => array('EmailAddress', array('StringLength', false, array(5, 50)),),
            'required'   => false,
            'label'      => 'Email:',
            'style'     => 'width:200px;', 
            'maxlength'  => '50',
        )); 
        
       
        // We want to display a 'failed authentication' message if necessary;
        // we'll do that with the form 'description', so we need to add that
        // decorator.
        
        
        $legal_id = new Zend_Form_Element_Text('legal_id');
        $legal_id->setOptions(
            array(
                'label'      => 'Legal Id',
                'required'   => FALSE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
//                'validators' => array(
//                                    'NotEmpty'
//                                ),
                'maxlength' => '20',
            )
        );
        $this->addElement($legal_id);
     
         
        $btn_auth_code = $this->addElement('button', 'add_beni', array(
            'required' => false,
            'ignore'   => true,
            'label'    => 'Add Beneficiary',
            'onclick'     => "javascript:addBeni();",
            'class'     => 'tangerine',
            
        )); 	
        
	$form2 = new Remit_Kotak_AddBeneficiaryWithRemitterDetailsForm();
        $this->addElements($form2->getElements());
				
				
//        $btn_auth_code = $this->addElement('button', 'btn_auth_code', array(
//            'required' => false,
//            'ignore'   => true,
//            'label'    => 'Send Authorization Code',
//            'onclick'     => "javascript:sendAuthCode();",
//            'class'     => 'tangerine',
//            
//        )); 
//        
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
            'style'     => 'width:200px;background:#FFFF99',
            'maxlength'  => '6',
        ));   
       
       $profile_pic = new Zend_Form_Element_File('profile_pic');
       $profile_pic->setLabel('Remitter Profile Photo')
	         ->setRequired(false)
                 ->addValidator(new Zend_Validate_File_Size('5MB'));
       $this->addElement($profile_pic);
         
         
         
       

        
         /* $email = new Zend_Form_Element_Text('email');
        $email->setOptions(
            array(
                'label'      => 'Email *',
                'required'   => FALSE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'EmailAddress',array('StringLength', false, array(9, 50)),
                                ),
                'maxlength' => '50',
                'style'     => 'background:#FFFF99',
            )
        );
        $this->addElement($email);*/
       
     
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
        
        $email_old = $this->addElement('hidden', 'email_old', array(
        ));
        
        $send_auth_code = $this->addElement('hidden', 'send_auth_code', array(
        ));
        
         $cty = $this->addElement('hidden', 'cty', array(
        ));
          $pin = $this->addElement('hidden', 'pin', array(
        ));
        
          $mobile_old = $this->addElement('hidden', 'mobile_old', array(
        ));
        $this->addElement('hidden', 'regn_fee', array(
        ));
        $this->addElement('hidden', 'mobile_country_code', array(
				    'decorators' => array('ViewHelper'),
				    'value'      => "+91"
				)); 
				$this->addElement('hidden', 'middle_name', array(
				    'decorators' => array('ViewHelper'),
				    'value'      => " "
				)); 
				$this->addElement('hidden', 'address_line2', array(
				    'decorators' => array('ViewHelper'),
				    'value'      => " "
				)); 
				$this->addElement('hidden', 'mother_maiden_name', array(
				    'decorators' => array('ViewHelper'),
				    'value'      => "Mother"
				)); 
				
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
