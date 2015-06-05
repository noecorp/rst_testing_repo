<?php
/*
 * Add Remitter Form
 */
class Corp_Boi_EditCustomerDetailsForm extends App_Agent_Form
{
  
    public function  init()
    {       
        
         $rctMasterModel = new RctMaster();
         $stateList = $rctMasterModel->getStateList();
         $cityList = $rctMasterModel->getCityList();
         $occupationList = $rctMasterModel->getOccupationList();
         $relationshipList = $rctMasterModel->getRelationshipList();
         $nomineeRelationshipList =  $rctMasterModel->getNomineeRelationshipList();
         $communityList = $rctMasterModel->getCommunityList();
         $districtList = $rctMasterModel->getLocationList();
       
               
        $AddComplexityValidator = new Zend_Validate_Regex('/^[a-zA-Z.,0-9\s]+$/i');
        $AddComplexityValidator->setMessage('Only special characters . and , (dot and comma) are allowed.');
        
//        $RemNameComplexityValidator = new Zend_Validate_Regex('/^[a-z\s]+$/i');
        $RemNameComplexityValidator = new Zend_Validate_Regex('/^[a-zA-Z.,\s]+$/i');
        $RemNameComplexityValidator->setMessage('Only special characters . and , (dot and comma) are allowed.');
        $RemNameStrLengthValidator = new Zend_Validate_StringLength();
        $RemNameStrLengthValidator->setMin(1);
        $RemNameStrLengthValidator->setMax(20);
        $RemNameStrLengthValidator->setMessage('Name must be between 2 to 100 alphabets long');
        
        
        $afn = new Zend_Form_Element_Text('sol_id');
        $afn->setOptions(
            array(
                'label'      => 'Linked Branch SOL ID*',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', 'Digits', array('StringLength', false, array(5, 5)),
                                ),
                 'maxlength' => '5',
            )
        );
        $this->addElement($afn);
       
     
        
       $title = new Zend_Form_Element_Select('title');
        $title->setOptions(
            array(
                'label'      => 'Title *',
                'multioptions'    => Util::getTitle(BANK_BOI_NDSC),
                            
                       
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
        $this->addElement($title);
        
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
                                        //$RemNameComplexityValidator,
                                        $RemNameStrLengthValidator,
                                         array('regex', false, array(
                  'pattern'   => '/[^<>]/i',
                  'messages'  =>  'Your first name cannot contain those characters : < >')),
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
                'label'      => 'Surname *',
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
        
          $afn = new Zend_Form_Element_Text('aadhaar_no');
        $afn->setOptions(
            array(
                'label'      => 'Aadhaar Number',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',array('StringLength', false, array(5, 12)),
                                ),
                 'maxlength' => '12',
            )
        );
        $this->addElement($afn);
 
          $afn = new Zend_Form_Element_Text('uid_no');
        $afn->setOptions(
            array(
                'label'      => 'Aadhaar Enrollment ID',
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
 
        $Identification_number = new Zend_Form_Element_Text('nsdc_enrollment_no');
        $Identification_number->setOptions(
            array(
                'label'      => 'NSDC Enrollment No.',
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
        
         $Identification_number = new Zend_Form_Element_Text('debit_mandate_amount');
        $Identification_number->setOptions(
            array(
                'label'      => 'Debit Mandate Amount *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', 'Digits', array('StringLength', false, array(1, 10)),
                                ),
                 'maxlength' => '10',
            )
        );
        $this->addElement($Identification_number);
        
        $Identification_number = new Zend_Form_Element_Text('training_center_id');
        $Identification_number->setOptions(
            array(
                'label'      => 'Training Center ID',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
            )
        );
        $this->addElement($Identification_number);
        
        $Identification_number = new Zend_Form_Element_Text('traning_center_name');
        $Identification_number->setOptions(
            array(
                'label'      => 'Traning Center Name',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
            )
        );
        $this->addElement($Identification_number);
        
        $Identification_number = new Zend_Form_Element_Text('training_partner_name');
        $Identification_number->setOptions(
            array(
                'label'      => 'Training Partner Name',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
            )
        );
        $this->addElement($Identification_number);
        
          
         $afn = new Zend_Form_Element_Text('pan');
        $afn->setOptions(
            array(
                'label'      => 'PAN',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',array('StringLength', false, array(10, 10)),
                                ),
                 'maxlength' => '10',
            )
        );
        $this->addElement($afn);
        
        
         
        $gender = new Zend_Form_Element_Select('gender');
        $gender->setOptions(
            array(
                'label'      => 'Sex *',
                'multioptions'    => Util::getGender(BANK_BOI_NDSC),
                            
                       
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
        $this->addElement($gender);
        $this->addElement(new ZendX_JQuery_Form_Element_DatePicker('date_of_birth',
            array('jQueryParams' => array('dateFormat' => 'dd-mm-yy'),
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(10, 20)),),
            'required'   => TRUE,
            'label'      => 'Date of Birth *(e.g. dd-mm-yyyy) ',
            'maxlength'  => '20',
            'style'     => 'width:200px;',)

        )); 
       
          
        $comm = new Zend_Form_Element_Radio('marital_status');
        $comm->setLabel('Marital Status *')
            ->addMultiOptions(array(
                 'Y' => FLAG_YES,
                    'N' => FLAG_NO
                   
                        ))
            ->setSeparator('    ')
            ->setValue(FLAG_NO);
         $this->addElement($comm);
         
       
        
        $res_city = new Zend_Form_Element_Select('occupation');
        $res_city->setOptions(
            array(
                'label'      => 'Occupation *',
               
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty'
                                ),
                'multioptions'    => $occupationList,
            )
        );
        $res_city->setRegisterInArrayValidator(false);
        $this->addElement($res_city);   
      
        
      
      
 
        
       
        
       
        
//        $res_country = new Zend_Form_Element_Select('country_code');
//        $res_country->setOptions(
//            array(
//                'label'      => 'Country *',
//                'required'   => true,
//                'filters'    => array(
//                                    'StringTrim',
//                                    'StripTags',
//                                ),
//                'validators' => array(
//                                    'NotEmpty'
//                                ),
//                'multioptions'    => Util::getCountry(),                       
//                
//            )
//        );
//        $this->addElement($res_country);
//        
        
        
        $res_address2 = new Zend_Form_Element_Text('address_line1');
        $res_address2->setOptions(
            array(
                'label'      => 'Permanent Address Line 1 * ',
                'required'   => TRUE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', array('StringLength', false, array(3, 45)),
                                    $AddComplexityValidator,
                                ),
                'maxlength' => '45',
            )
        );
        $this->addElement($res_address2);
        
       $res_address2 = new Zend_Form_Element_Text('address_line2');
        $res_address2->setOptions(
            array(
                'label'      => 'Permanent Address Line 2 *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', array('StringLength', false, array(3, 45)),
                                    $AddComplexityValidator,
                                ),
                'maxlength' => '45',
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
                'multioptions'    => $stateList,                       
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
                'multioptions'    => $cityList,
            )
        );
        $res_city->setRegisterInArrayValidator(false);
        $this->addElement($res_city);   
        
        $pincode = new Zend_Form_Element_Text('pincode');
        $pincode->setOptions(
            array(
                'label'      => 'PIN Code *',

                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array('NotEmpty','Digits', array('StringLength', false, array(6,6)),),
                'maxlength' => '6',
            )
        );
        $this->addElement($pincode); 
        
         
          $is_check = new Zend_Form_Element_Checkbox('is_check');
          $is_check->setCheckedValue(FLAG_YES);
          $is_check->setUncheckedValue(FLAG_NO);
          $is_check->setOptions(array(
                'label'      => 'Same as Permanent Address',
                'style'     => 'width:14px;'        
          ));
          $this->addElement($is_check);
       
//        $res_country = new Zend_Form_Element_Select('comm_country_code');
//        $res_country->setOptions(
//            array(
//                'label'      => 'Correspondence Country *',
//                'required'   => true,
//                'filters'    => array(
//                                    'StringTrim',
//                                    'StripTags',
//                                ),
//                'validators' => array(
//                                    'NotEmpty'
//                                ),
//                'multioptions'    => Util::getCountry(),                       
//                
//            )
//        );
//        $this->addElement($res_country);
        $res_address2 = new Zend_Form_Element_Text('comm_address_line1');
        $res_address2->setOptions(
            array(
                'label'      => 'Correspondence Address Line 1 *',
                'required'   => TRUE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', array('StringLength', false, array(3, 45)),
                                    $AddComplexityValidator,
                                ),
                'maxlength' => '45',
            )
        );
        $this->addElement($res_address2);
        
       $res_address2 = new Zend_Form_Element_Text('comm_address_line2');
        $res_address2->setOptions(
            array(
                'label'      => 'Correspondence Address Line 2 ',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', array('StringLength', false, array(0, 45)),
                                    $AddComplexityValidator,
                                ),
                'maxlength' => '45',
            )
        );
        $this->addElement($res_address2);
        
        $res_state = new Zend_Form_Element_Select('comm_state');
        $res_state->setOptions(
            array(
                'label'      => 'Correspondence State *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty'
                                ),
                'multioptions'    => $stateList,                       
            )
        );
        $this->addElement($res_state);

        
        $res_city = new Zend_Form_Element_Select('comm_city');
        $res_city->setOptions(
            array(
                'label'      => 'Correspondence City *',
               
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty'
                                ),
                'multioptions'    => $cityList,
            )
        );
        $res_city->setRegisterInArrayValidator(false);
        $this->addElement($res_city);   
        
        $pincode = new Zend_Form_Element_text('comm_pin');
        $pincode->setOptions(
            array(
                'label'      => 'Correspondence Pincode *',

                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array('NotEmpty','Digits', array('StringLength', false, array(6,6)),),
                'maxlength' => '6',
            )
        );
        $this->addElement($pincode);
     
             
         $mobile = $this->addElement('text', 'landline', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty','Digits', array('StringLength', false, array(10, 10)),),
            'required'   => false,
            'label'      => 'Telephone',
            'style'     => 'width:200px;',
            'maxlength'  => '10',
            'autocomplete'=> 'off',
            //'readonly' => true
           
        ));
         $mobile = $this->addElement('text', 'mobile', array(
            'filters'    => array('StringTrim'),
            'validators' => array('Digits', array('StringLength', false, array(10, 10)),),
            'required'   => false,
            'label'      => 'Mobile Number',
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
           
    
      
        
//        $Identification_number = new Zend_Form_Element_Text('cust_comm_code');
//        $Identification_number->setOptions(
//            array(
//                'label'      => 'Customer Communication Code *',
//                'required'   => TRUE,
//                'filters'    => array(
//                                    'StringTrim',
//                                    'StripTags',
//                                ),
//                'validators' => array(
//                                    'NotEmpty'
//                                ),
//                 'maxlength' => '10',
//            )
//        );
//        $this->addElement($Identification_number);
//        
//        
//        
        
        
        
//        $Identification_number = new Zend_Form_Element_Text('account_type_id');
//        $Identification_number->setOptions(
//            array(
//                'label'      => 'Account Type Id *',
//                'required'   => TRUE,
//                'filters'    => array(
//                                    'StringTrim',
//                                    'StripTags',
//                                ),
//                'validators' => array(
//                                    'NotEmpty'
//                                ),
//                 'maxlength' => '50',
//            )
//        );
//        $this->addElement($Identification_number);
//        
        
          $is_check = new Zend_Form_Element_Checkbox('nomination_flg');
          $is_check->setCheckedValue(FLAG_Y);
          $is_check->setUncheckedValue(FLAG_N);
          $is_check->setOptions(array(
                'label'      => 'Has Nominee *',
                'style'     => 'width:14px;'        
          ));
          $this->addElement($is_check);
       
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
                                    'NotEmpty',
				$RemNameComplexityValidator,
                                ),
                 'maxlength' => '100',
            )
        );
        $this->addElement($Identification_number);
        
        
        $Identification_number = new Zend_Form_Element_Select('nominee_relationship');
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
               'multioptions'    => $relationshipList,
            )
        );
        $this->addElement($Identification_number);
        
        
        $this->addElement(new ZendX_JQuery_Form_Element_DatePicker('nominee_dob',
            array('jQueryParams' => array('dateFormat' => 'dd-mm-yy'),
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(10, 20)),),
            'required'   => FALSE,
            'label'      => 'Nominee Date Of Birth: (e.g. dd-mm-yyyy) ',
            'maxlength'  => '20',
            'style'     => 'width:200px;',)

        )); 
        
        $res_address2 = new Zend_Form_Element_Text('nominee_add_line1');
        $res_address2->setOptions(
            array(
                'label'      => 'Nominee Address Line 1',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', array('StringLength', false, array(3, 50)),
				$AddComplexityValidator,
                                ),
                'maxlength' => '30',
            )
        );
        $this->addElement($res_address2);
        
        $res_address2 = new Zend_Form_Element_Text('nominee_add_line2');
        $res_address2->setOptions(
            array(
                'label'      => 'Nominee Address Line 2',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', array('StringLength', false, array(3, 50)),
				$AddComplexityValidator,
                                ),
                'maxlength' => '30',
            )
        );
        $this->addElement($res_address2);
       
        $res_address2 = new Zend_Form_Element_Select('nominee_city_cd');
        $res_address2->setOptions(
            array(
                'label'      => 'Nominee City',
               
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', array('StringLength', false, array(3, 50)),
				$AddComplexityValidator,
                                ),
                'multioptions'    => $cityList,
            )
        );
        $res_address2->setRegisterInArrayValidator(false);
        $this->addElement($res_address2); 
        
//                
//          $is_check = new Zend_Form_Element_Checkbox('nominee_minor_flag');
//          $is_check->setCheckedValue(FLAG_Y);
//          $is_check->setUncheckedValue(FLAG_N);
//          $is_check->setOptions(array(
//                'label'      => 'Is Minor',
//                'style'     => 'width:14px;'        
//          ));
//          $this->addElement($is_check);
//       
        $Identification_number = new Zend_Form_Element_Text('minor_guardian_name');
        $Identification_number->setOptions(
            array(
                'label'      => 'Minor Guardian',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
				$RemNameComplexityValidator,
                                ),
                 'maxlength' => '100',
            )
        );
        $this->addElement($Identification_number);
        
        
        $Identification_number = new Zend_Form_Element_Select('nominee_minor_guradian_cd');
        $Identification_number->setOptions(
            array(
                'label'      => 'Minor Guardian Relationship',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty'
                                ),
               'multioptions'    => $nomineeRelationshipList,
            )
        );
        $this->addElement($Identification_number);
        
         $Identification_number = new Zend_Form_Element_Text('ref_num');
        $Identification_number->setOptions(
            array(
                'label'      => 'Application Reference Number *',
                'required'   => TRUE,
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
        
        
       
        
        $submit = new Zend_Form_Element_Submit('btn_add');
        $submit->setOptions(
            array(
                'label'      => 'Update & Resubmit',
                'required'   => FALSE,
                'class'     => 'tangerine',
                'title'      => 'Update & Resubmit',
                //'onclick'    => 'Javascript:checkDOB();',
            )
        );
        $this->addElement($submit);
        
        
        $this->addElement('hidden', 'minor_flg', array(
          			'value'      => "N"
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
