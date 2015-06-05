<?php

class Corp_Ratnakar_EditCardholderForm extends App_Corporate_Form
{
  
    public function  init()
    {   
        parent::init();
        $user = Zend_Auth::getInstance()->getIdentity();
        $statelist = new CityList();
        $stateOptionsList = $statelist->getStateList($countryCode = 356);
        
        $RemNameComplexityValidator = new Zend_Validate_Regex('/^[a-z\s]+$/i');
        $RemNameComplexityValidator->setMessage('Customer name must include alphabets only');
        $RemNameStrLengthValidator = new Zend_Validate_StringLength();
        $RemNameStrLengthValidator->setMin(1);
        $RemNameStrLengthValidator->setMax(20);
        $RemNameStrLengthValidator->setMessage('Customer name must be between 1 to 20 alphabets long');
        
        $product = new Zend_Form_Element_Select('product_id');
        $product->setOptions(
            array(
                'label'      => 'Product Name *',
                'multioptions'    => array('' => 'Select Product'),

                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                ),
            )
        );
        $this->addElement($product);
        
        $card_type = new Zend_Form_Element_Select('card_type');
        $card_type->setOptions(
            array(
                'label'      => 'Activation Type *',
                'multioptions'    => Util::getCardStatusList(),
                            
                       
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                ),
            )
        );
        $this->addElement($card_type);
        
        $card_number = $this->addElement('text', 'card_number', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty','Digits', array('StringLength', false, array(16, 16))),
            'required'   => false,
            'label'      => 'Card Number',
            'maxlength'  => '16',
        ));
        
        $card_number = $this->addElement('text', 'card_pack_id', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty','Digits', array('StringLength', false, array(14, 14))),
            'required'   => true,
            'label'      => 'Card Pack ID *',
            'maxlength'  => '14',
        ));
         $medi_assist_id = $this->addElement('text', 'medi_assist_id', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty','Digits', array('StringLength', false, array(0, 16))),
            'required'   => false,
            'label'      => 'Member ID',
            'maxlength'  => '16',
        ));
        
        
        $afn = $this->addElement('text', 'afn', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty','Alnum', array('StringLength', false, array(0, 16))),
            'required'   => false,
            'label'      => 'AFN',
            'maxlength'  => '16',
        ));
        
        
        $employee_id = $this->addElement('text', 'employee_id', array(
            'filters'    => array('StringTrim'),            
            'validators' => array('NotEmpty','Alnum', array('StringLength', false, array(0, 16))),
            'required'   => true,
            'label'      => 'Employee ID *',
            'maxlength'  => '16',
        ));
        
        $mobile_country_code = $this->addElement('select', 'mobile_country_code', array(

            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(1, 6)),),
            'required'   => true,
            'label'      => 'Mobile Country Code *',
            'multiOptions' => array_merge(array(''=>'Select'),Mobile::getCountryCodes()),
        ));
        
        $mobile_number = $this->addElement('text', 'mobile', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty','Digits', array('StringLength', false, array(10, 10)),),
            'required'   => true,
            'label'      => 'Mobile Number *',
            'maxlength'  => '10',
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
            'validators' => array('NotEmpty', 'Digits',array('StringLength', false, array(6, 6)),),
            'required'   => true,
            'label'      => 'Authorization Code *',
            'maxlength'  => '6',
        ));  
        
                
         $name = new Zend_Form_Element_Text('first_name');
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
                'maxlength'  => '20',
                
            )
        );
        
        $this->addElement($name);
        
         $name = new Zend_Form_Element_Text('middle_name');
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
                'maxlength'  => '26',
                
            )
        );
        
        $this->addElement($name);
        
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
                'maxlength'  => '20',
                
            )
        );
        
        $this->addElement($name);
        
        
         $name = new Zend_Form_Element_Text('name_on_card');
         $name->setOptions(
            array(
                'label'      => 'Name on Card *',
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
                'maxlength'  => '26',
                
            )
        );
        
        $this->addElement($name);
        
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
        $gender = $this->addElement('select', 'gender', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(4, 6)),),
            'required'   => true,
            'label'      => 'Gender *',
            'multioptions' => Util::getGender(),
        )); 
        
        $this->addElement(new ZendX_JQuery_Form_Element_DatePicker('date_of_birth',
            array('jQueryParams' => array('dateFormat' => 'dd-mm-yy','changeYear'=> 'true','yearRange'=>'-70:+0'),
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(10, 10)),),
            'required'   => true,
            'label'      => 'Date of Birth *(e.g. dd-mm-yyyy) ',
            'maxlength'  => '10',
        )));
        
        $res_address2 = new Zend_Form_Element_Text('address_line1');
        $res_address2->setOptions(
            array(
                'label'      => 'Address Line 1 * ',
                'required'   => TRUE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', array('StringLength', false, array(3, 50)),
                                ),
                'maxlength' => '30',
            )
        );
        $this->addElement($res_address2);
        
       $res_address2 = new Zend_Form_Element_Text('address_line2');
        $res_address2->setOptions(
            array(
                'label'      => 'Address Line 2',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', array('StringLength', false, array(3, 50)),
                                ),
                'maxlength' => '30',
            )
        );
        $this->addElement($res_address2);
        
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
            )
        );
        $res_city->setRegisterInArrayValidator(false);
        $this->addElement($res_city);   
        
        $pincode = new Zend_Form_Element_Select('pincode');
        $pincode->setOptions(
            array(
                'label'      => 'Pincode *',

                'required'   => True,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                
                'multioptions'    => array('' =>'Select Pincode'),
            )
        );
        $pincode->setRegisterInArrayValidator(false);
        $this->addElement($pincode); 
          $mobile = $this->addElement('text', 'landline', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty','Digits', array('StringLength', false, array(8, 15)),),
            'required'   => false,
            'label'      => 'Landline: ',
            'style'     => 'width:200px;',
            'maxlength'  => '15',
            'autocomplete'=> 'off',
            //'readonly' => true
           
        ));
        
        $pan = $this->addElement('text', 'pan', array(
           'filters'    => array('StringTrim'),
           'validators' => array('NotEmpty','Alnum', array('StringLength', false, array(10, 10)),),
           'required'   => false,
           'label'      => 'PAN',
           'maxlength'  => '10',
        )); 
        
        $aadhaar_no = $this->addElement('text', 'aadhaar_no', array(
           'filters'    => array('StringTrim'),
           'validators' => array('NotEmpty','Digits', array('StringLength', false, array(12, 12)),),
           'required'   => false,
           'label'      => 'Aadhaar No. (UID)',
           'maxlength'  => '12',
        )); 
        
                
        
        $email = $this->addElement('text', 'email', array(
            'filters'    => array('StringTrim'),
            'validators' => array('EmailAddress', array('StringLength', false, array(5, 50)),),
            'required'   => true,
            'label'      => 'Email *',
            'maxlength'  => '50',
        )); 
        
        
        $employer_name = $this->addElement('text', 'employer_name', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(3, 100)),),
            'required'   => true,
            'label'      => 'Employer Name *',
            'maxlength'  => '100',
        )); 
        
        $employee_id = $this->addElement('text', 'corporate_id', array(
            'filters'    => array('StringTrim'),            
            'validators' => array('NotEmpty','Alnum', array('StringLength', false, array(1, 16))),
            'required'   => true,
            'label'      => 'Corporate ID *',
            'maxlength'  => '16',
        ));
        $res_address2 = new Zend_Form_Element_Text('corp_address_line1');
        $res_address2->setOptions(
            array(
                'label'      => 'Corporate Address Line 1 *',
                'required'   => TRUE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', array('StringLength', false, array(3, 50)),
                                ),
                'maxlength' => '30',
            )
        );
        $this->addElement($res_address2);
        
       $res_address2 = new Zend_Form_Element_Text('corp_address_line2');
        $res_address2->setOptions(
            array(
                'label'      => 'Corporate Address Line 2 ',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', array('StringLength', false, array(3, 50)),
                                ),
                'maxlength' => '30',
            )
        );
        $this->addElement($res_address2);
        
        $res_state = new Zend_Form_Element_Select('corp_state');
        $res_state->setOptions(
            array(
                'label'      => 'Corporate State *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty'
                                ),
                'multioptions'    => $stateOptionsList,                       
            )
        );
        $this->addElement($res_state);

        
        $res_city = new Zend_Form_Element_Select('corp_city');
        $res_city->setOptions(
            array(
                'label'      => 'Corporate City *',
               
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty'
                                ),
                'multioptions'    => array('' =>'Select City'),
            )
        );
        $res_city->setRegisterInArrayValidator(false);
        $this->addElement($res_city);   
        
        $pincode = new Zend_Form_Element_Select('corp_pincode');
        $pincode->setOptions(
            array(
                'label'      => 'Corporate Pincode *',

                'required'   => True,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                
                'multioptions'    => array('' =>'Select Pincode'),
            )
        );
        $pincode->setRegisterInArrayValidator(false);
        $this->addElement($pincode);
     
          
        $Identification_type = new Zend_Form_Element_Select('id_proof_type');
        $Identification_type->setOptions(
            array(
                'label'      => 'Identification Type *',
                'multioptions'    => Util::getIdentificationType(),
                //'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty'
                                ),
            )
        );
        $this->addElement($Identification_type);
        
        $Identification_number = new Zend_Form_Element_Text('id_proof_number');
        $Identification_number->setOptions(
            array(
                'label'      => 'Identification No. *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty'
                                ),
                'maxlength' => '16',
            )
        );
        $this->addElement($Identification_number);
       
        
       
       $doc_file = new Zend_Form_Element_File('id_doc_path');
       $doc_file->setLabel('Upload Identification Document')
	         ->setRequired(false)
                 ->addValidator(new Zend_Validate_File_Size('5MB'));
       $this->addElement($doc_file);
       
        

       $Identification_type = new Zend_Form_Element_Select('address_proof_type');
        $Identification_type->setOptions(
            array(
                'label'      => 'Address Proof Type *',
                'multioptions'    => Util::getAddressProofType(),
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty'
                                ),
               
            )
        );
        $this->addElement($Identification_type);
        
        
        $Identification_number = new Zend_Form_Element_Text('address_proof_number');
        $Identification_number->setOptions(
            array(
                'label'      => 'Address Proof No. *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty'
                                ),
                 'maxlength' => '16',
            )
        );
        $this->addElement($Identification_number);

        $doc_file = new Zend_Form_Element_File('address_doc_path');
       $doc_file->setLabel('Upload Address Document')
	         ->setRequired(false)
                 ->addValidator(new Zend_Validate_File_Size('5MB'));
       $this->addElement($doc_file);
        
          
         $cty = $this->addElement('hidden', 'cty', array(
        ));
          $pin = $this->addElement('hidden', 'pin', array(
        )); 
     
             
         $cty = $this->addElement('hidden', 'comm_cty', array(
        ));
          $pin = $this->addElement('hidden', 'comm_pin', array(
        ));
          
        //$cty = $this->addElement('hidden', 'medi_assist_id', array(
        //));  
          
        $submit = new Zend_Form_Element_Submit('btn_add');
        $submit->setOptions(
            array(
                'label'      => 'Enroll Cardholder',
                'required'   => FALSE,
                'title'       => 'Enroll Cardholder',
                'class'     => 'tangerine',
            )
        );
        $this->addElement($submit);
        
         $mobile_number_old = $this->addElement('hidden', 'mobile_number_old', array(
           // 'filters'    => array('StringTrim'),
            //'validators' => array('NotEmpty', array('StringLength', false, array(10, 10)),),
            //'required'   => false,
            //'label'      => 'Mobile Number: *',
            //'style'     => 'width:200px;',
        ));
                  
         $send_auth_code = $this->addElement('hidden', 'send_auth_code', array(
           // 'filters'    => array('StringTrim'),
            //'validators' => array('NotEmpty', array('StringLength', false, array(10, 10)),),
            //'required'   => false,
            //'label'      => 'Mobile Number: *',
            //'style'     => 'width:200px;',
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