<?php
/*
 * Add Remitter Form
 */
class Corp_Kotak_ResubmitCustomerDetailsForm extends App_Agent_Form
{
  
    public function  init()
    {       
         $this->setAttrib('enctype', 'multipart/form-data');
         
         $statelist = new CityList();
         $stateOptionsList = $statelist->getStateList($countryCode = 356);
        
         $product_id = $this->addElement('select', 'product_id', array(
            'filters'    => array('StringTrim'),            
            'required'   => true,
            'label'      => 'Product Name: *',
            'style'     => 'width:258px;',
          ));
        
               
        $RemNameComplexityValidator = new Zend_Validate_Regex('/^[a-z\s]+$/i');
        $RemNameComplexityValidator->setMessage('Customer name must include alphabets only');
        $RemNameStrLengthValidator = new Zend_Validate_StringLength();
        $RemNameStrLengthValidator->setMin(1);
        $RemNameStrLengthValidator->setMax(22);
        $RemNameStrLengthValidator->setMessage('Customer name must be between 1 to 22 alphabets long');
        

        $afn = new Zend_Form_Element_Text('afn');
        $afn->setOptions(
            array(
                'label'      => 'AFN *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',array('StringLength', false, array(5, 10)),
                                ),
                 'maxlength' => '10',
            )
        );
        $this->addElement($afn);
      
         
       
        
        $afn = new Zend_Form_Element_Text('place_application');
        $afn->setOptions(
            array(
                'label'      => 'Place *',
                'required'   => TRUE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',array('StringLength', false, array(5, 11)),
                                ),
                 'maxlength' => '11',
            )
        );
        $this->addElement($afn);
        
         $afn = new Zend_Form_Element_Text('member_id');
        $afn->setOptions(
            array(
                'label'      => 'Member Id *',
                'required'   => TRUE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',array('StringLength', false, array(2, 11)),
                                ),
                 'maxlength' => '11',
            )
        );
        $this->addElement($afn);
        
       
        
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
                'maxlength'  => '30',
                
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
                'maxlength'  => '30',
                
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
                'maxlength'  => '30',
                
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
                'maxlength'  => '22',
                
            )
        );
        
        $this->addElement($name);
        
        $this->addElement(new ZendX_JQuery_Form_Element_DatePicker('date_of_birth',
            array('jQueryParams' => array('dateFormat' => 'dd-mm-yy'),
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(10, 20)),),
            'required'   => TRUE,
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
        
       $mobile = $this->addElement('text', 'mobile', array(
            'filters'    => array('StringTrim'),
            'validators' => array('Digits', array('StringLength', false, array(10, 10)),),
            'required'   => false,
            'label'      => 'Mobile Number ',
            'style'     => 'width:200px;',
            'maxlength'  => '10',
            'autocomplete'=> 'off',
            //'readonly' => true
           
        ));
        
         $mobile = $this->addElement('text', 'landline', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty','Digits', array('StringLength', false, array(10, 10)),),
            'required'   => false,
            'label'      => 'Landline: ',
            'style'     => 'width:200px;',
            'maxlength'  => '10',
            'autocomplete'=> 'off',
            //'readonly' => true
           
        ));
        
        
          $email = new Zend_Form_Element_Text('email');
        $email->setOptions(
            array(
                'label'      => 'Email',
                'required'   => FALSE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'EmailAddress',array('StringLength', false, array(9, 50)),
                                ),
                'maxlength' => '50',
            )
        );
        $this->addElement($email);
           
        $afn = new Zend_Form_Element_Text('aadhaar_no');
        $afn->setOptions(
            array(
                'label'      => 'Aadhar Number',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',array('StringLength', false, array(5, 16)),
                                ),
                 'maxlength' => '16',
            )
        );
        $this->addElement($afn);
        
       $profile_pic = new Zend_Form_Element_File('profile_pic');
       $profile_pic->setLabel('Upload Application Form')
	         ->setRequired(false)
                 ->addValidator(new Zend_Validate_File_Size('5MB'));
       $this->addElement($profile_pic);
         
        
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
        
         
       
        
        $res_address2 = new Zend_Form_Element_Text('comm_address_line1');
        $res_address2->setOptions(
            array(
                'label'      => 'Communication Address Line 1 *',
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
        
       $res_address2 = new Zend_Form_Element_Text('comm_address_line2');
        $res_address2->setOptions(
            array(
                'label'      => 'Communication Address Line 2 ',
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
        
        $res_state = new Zend_Form_Element_Select('comm_state');
        $res_state->setOptions(
            array(
                'label'      => 'Communication State *',
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

        
        $res_city = new Zend_Form_Element_Select('comm_city');
        $res_city->setOptions(
            array(
                'label'      => 'Communication City *',
               
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
        
        $pincode = new Zend_Form_Element_Select('comm_pin');
        $pincode->setOptions(
            array(
                'label'      => 'Communication Pincode *',

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
                'multioptions'    => Util::getIdentificationType($additional = TRUE),
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
        
            
        $Identification_number = new Zend_Form_Element_Text('id_proof_number');
        $Identification_number->setOptions(
            array(
                'label'      => 'Identification Proof No. *',
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
       $doc_file->setLabel('Identification Document *')
	         ->setRequired(false)
                 ->addValidator(new Zend_Validate_File_Size('5MB'));
       $this->addElement($doc_file);
       
        

       $Identification_type = new Zend_Form_Element_Select('address_proof_type');
        $Identification_type->setOptions(
            array(
                'label'      => 'Address Proof Type ',
                'multioptions'    => Util::getAddressProofType(),
                'required'   => false,
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
                'label'      => 'Address Proof No. ',
                'required'   => false,
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
       $doc_file->setLabel('Address Document')
	         ->setRequired(false)
                 ->addValidator(new Zend_Validate_File_Size('5MB'));
       $this->addElement($doc_file);
        
         
       
       
       $Identification_number = new Zend_Form_Element_Text('society_id');
        $Identification_number->setOptions(
            array(
                'label'      => 'Society Id ',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty'
                                ),
                 'maxlength' => '10',
            )
        );
        $this->addElement($Identification_number);
        
        
        $Identification_number = new Zend_Form_Element_Text('society_name');
        $Identification_number->setOptions(
            array(
                'label'      => 'Society Name',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty'
                                ),
                 'maxlength' => '50',
            )
        );
        $this->addElement($Identification_number);
        
       
         $Identification_number = new Zend_Form_Element_Text('nominee_name');
        $Identification_number->setOptions(
            array(
                'label'      => 'Nominee Name ',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty'
                                ),
                 'maxlength' => '100',
            )
        );
        $this->addElement($Identification_number);
        
        
        $Identification_number = new Zend_Form_Element_Text('nominee_relationship');
        $Identification_number->setOptions(
            array(
                'label'      => 'Nominee Relationship',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty'
                                ),
                 'maxlength' => '10',
            )
        );
        $this->addElement($Identification_number);
        
        $remarks = new Zend_Form_Element_Textarea('comments');
        $remarks->setOptions(
            array(
                'label'      => 'Add your remarks *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'style' => 'height:100px;width:300px;',
                'validators' => array(
                                    'NotEmpty',array('StringLength', false, array(5, 255)),
                                ),
                 'maxlength' => '255',
                
            )
        );
        $this->addElement($remarks);
        
        
         $cty = $this->addElement('hidden', 'cty', array(
        ));
          $pin = $this->addElement('hidden', 'pin', array(
        ));
          
            $cty = $this->addElement('hidden', 'comm_cty', array(
        ));
          $pin = $this->addElement('hidden', 'c_pin', array(
        ));
         
        $submit = new Zend_Form_Element_Submit('submit_partner');
        $submit->setOptions(
            array(
                'label'      => 'Reject and Submit to Partner',
                'required'   => FALSE,
                'class'     => 'tangerine',
                'title'      => 'Reject and Submit to Partner',
            )
        );
        $this->addElement($submit);
        
        $submit = new Zend_Form_Element_Submit('submit_bank');
        $submit->setOptions(
            array(
                'label'      => 'Re-Submit to Bank',
                'required'   => FALSE,
                'class'     => 'tangerine',
                'title'      => 'Re-Submit to Bank',
            )
        );
        $this->addElement($submit);
      
         
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
