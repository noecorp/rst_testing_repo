<?php

class Corp_Ratnakar_EditCardholderForm extends App_Agent_Form
{
  
    public function  init()
    {   
        parent::init();
        
        
        $afn = $this->addElement('text', 'afn', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty','Alnum', array('StringLength', false, array(2, 10))),
            'required'   => true,
            'label'      => 'AFN *',
            'maxlength'  => '10',
        ));
        
        
        $medi_assist_id = $this->addElement('text', 'medi_assist_id', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty','Digits', array('StringLength', false, array(2, 10))),
            'required'   => true,
            'label'      => 'Medi Assist ID *',
            'maxlength'  => '10',
        ));
        
        
        
        $employee_id = $this->addElement('text', 'employee_id', array(
            'filters'    => array('StringTrim'),            
            'validators' => array('NotEmpty','Alnum', array('StringLength', false, array(2, 10))),
            'required'   => true,
            'label'      => 'Employee ID *',
            'maxlength'  => '10',
        ));
        
//        $mobile_country_code = $this->addElement('select', 'mobile_country_code', array(
//
//            'filters'    => array('StringTrim'),
//            'validators' => array('NotEmpty', array('StringLength', false, array(1, 6)),),
//            'required'   => true,
//            'label'      => 'Mobile Country Code *',
//            'multiOptions' => array_merge(array(''=>'Select'),Mobile::getCountryCodes()),
//             'readonly'  => 'readonly',
//        ));
        
        $mobile_number = $this->addElement('text', 'mobile_number', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty','Digits', array('StringLength', false, array(10, 10)),),
            'required'   => true,
            'label'      => 'Mobile Number *',
            'maxlength'  => '10',
            'readonly'  => 'readonly',
        )); 
        
                
        $fname = new Zend_Form_Element_Text('first_name');
        $fname->setOptions(
            array(
                'label'      => 'First Name *',
                'required'   => TRUE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty' ,array('StringLength', false, array(2, 26)),
                                ),
                'maxlength'  => '26',
            )
        );
        
        $this->addElement($fname);
        $fname->addValidator('Alpha', true, array('allowWhiteSpace' => true));
       
        $mname = new Zend_Form_Element_Text('middle_name');
        $mname->setOptions(
            array(
                'label'      => 'Middle Name ',
                'required'   => FALSE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty' ,array('StringLength', false, array(0, 26)),
                                ),
                'maxlength'  => '26',
            )
        );
        
        $this->addElement($mname);
        $mname->addValidator('Alpha', true, array('allowWhiteSpace' => true));
           
        
        $lname = new Zend_Form_Element_Text('last_name');
        $lname->setOptions(
            array(
                'label'      => 'Last Name *',
                'required'   => TRUE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty' ,array('StringLength', false, array(2, 26)),
                                ),
                'maxlength'  => '26',
            )
        );
        
        $this->addElement($lname);
        $lname->addValidator('Alpha', true, array('allowWhiteSpace' => true));
       
        
        $gender = $this->addElement('select', 'gender', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(4, 6)),),
            'required'   => true,
            'label'      => 'Gender *',
            'multioptions' => Util::getGender(),
        )); 
        
        $this->addElement(new ZendX_JQuery_Form_Element_DatePicker('date_of_birth',
            array('jQueryParams' => array('dateFormat' => 'dd-mm-yy'),
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(10, 10)),),
            'required'   => true,
            'label'      => 'Date of Birth *(e.g. dd-mm-yyyy) ',
            'maxlength'  => '10',
        )));
        
        
        
        
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
        
        $batch_name = $this->addElement('text', 'batch_name', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(4, 100)),),
            'required'   => false,
            'label'      => 'Batch Name',
            'maxlength'  => '100',
        )); 
        
        $corporate_id = $this->addElement('text', 'corporate_id', array(
            'filters'    => array('StringTrim'),
            'validators' => array(array('StringLength', false, array(3, 11)),),
            'required'   => false,
            'label'      => 'Corporate Id',
            'maxlength'  => '11',
        )); 

         $afn_old = $this->addElement('hidden', 'afn_old', array());
         $id = $this->addElement('hidden', 'id', array());
         
     
        $submit = new Zend_Form_Element_Submit('btn_edit');
        $submit->setOptions(
            array(
                'label'      => 'Edit Cardholder',
                'required'   => FALSE,
                'title'       => 'Edit Cardholder',
                'class'     => 'tangerine',
            )
        );
        $this->addElement($submit);
        
        /* temporarily commented for revert
        $next = new Zend_Form_Element_Submit('btn_next');
        $next->setOptions(
            array(
                'label'      => 'Next',
                'required'   => FALSE,
                'title'       => 'Next',
                'class'     => 'tangerine',
            )
        );
        $this->addElement($next);
        */
        
         $aadhaar_no_old = $this->addElement('hidden', 'aadhaar_no_old', array());
         $pan_old = $this->addElement('hidden', 'pan_old', array());
         $email_old = $this->addElement('hidden', 'email_old', array());
         $searchCriteria = $this->addElement('hidden', 'searchCriteria', array());
         $keyword = $this->addElement('hidden', 'keyword', array());
         $sub = $this->addElement('hidden', 'sub', array('value'=>'1'));
          
           
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
