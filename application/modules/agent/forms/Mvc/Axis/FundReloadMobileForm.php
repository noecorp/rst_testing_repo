<?php

class Mvc_Axis_FundReloadMobileForm extends App_Agent_Form
{
  
    public function  init()
    {
                
       $mobile_number = $this->addElement('text', 'mobile_number', array(
            'filters'    => array('StringTrim'),
            'validators' => array('Digits', array('StringLength', false, array(10, 10)),),
            'required'   => true,
            'label'      => 'Cardholder Mobile Number * (+91)',
            'style'      => 'width:200px;',
            'maxlength'  => '10',
           
        ));             
        
         $btn_mob = $this->addElement('submit', 'btn_mob', array(
            'required' => false,
            'ignore'   => true,
            'label'    => 'Submit',
        ));         
            
        $submit = new Zend_Form_Element_Submit('btn_mob');
        $submit->setOptions(
            array(
                'label'      => 'Submit',
                'required'   => FALSE,
                'title'       => 'Submit',
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
    
    
    
    
    
    
    /*     
     
      public function  init()
    {       
        $mobile_country_code = $this->addElement('select', 'mobile_country_code', array(

            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(1, 6)),),
            'required'   => true,
            'label'      => 'Mobile Country Code: *',
            'style'     => 'width:200px;',
            'multiOptions' => Mobile::getCountryCodes(),
        ));
        
        $mobile_number = $this->addElement('text', 'mobile_number', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(10, 10)),),
            'required'   => true,
            'label'      => 'Mobile Number: *',
            'style'     => 'width:200px;',
        ));       
        
        
         $alternate_contact_number = $this->addElement('text', 'alternate_contact_number', array(
            'filters'    => array('Digits'),
            'validators' => array(array('StringLength', false, array(10, 10)),),
            'required'   => false,
            'label'      => 'Alternate Number: ',
            'style'     => 'width:200px;',
        ));
         
         
        $arn = $this->addElement('text', 'arn', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(2, 10)),),
            'required'   => true,
            'label'      => 'ARN: *',
            'style'     => 'width:200px;',
        ));
        
        $product_id = $this->addElement('select', 'product_id', array(
            'filters'    => array('StringTrim'),            
            'required'   => true,
            'label'      => 'Product: *',
            'style'     => 'width:200px;',
        ));
        
        $customer_mvc_type = $this->addElement('select', 'customer_mvc_type', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(2, 10)),),
            'required'   => true,
            'label'      => 'MVC Type: *',
            'style'     => 'width:200px;',
            'multioptions' => MVC::getType(),
        ));
        
         $email = $this->addElement('text', 'email', array(
            'filters'    => array('StringTrim'),
            'validators' => array('EmailAddress', array('StringLength', false, array(5, 50)),),
            'required'   => true,
            'label'      => 'Email: *',
            'style'     => 'width:200px;',            
        ));  
         
         
        $title = $this->addElement('select', 'title', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(2, 6)),),
            'required'   => true,
            'label'      => 'Title: *',
            'style'     => 'width:200px;',
            'multioptions' => Util::getTitle(),
        ));        
        
                
        $first_name = $this->addElement('text', 'first_name', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(2, 20)),),
            'required'   => true,
            'label'      => 'First Name: *',
            'style'     => 'width:200px;',
        ));
        
        $middle_name = $this->addElement('text', 'middle_name', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(2, 20)),),
            'required'   => false,
            'label'      => 'Middle Name:',
            'style'     => 'width:200px;',
        ));
        
        
        
        $last_name = $this->addElement('text', 'last_name', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(2, 20)),),
            'required'   => true,
            'label'      => 'Last Name: *',
            'style'     => 'width:200px;',
        ));
        
        
        /* $alternate_number = $this->addElement('text', 'alternate_number', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(10, 10)),),
            'required'   => false,
            'label'      => 'Alternate Number: ',
            'style'     => 'width:200px;',
        ));*/  
         
         
        /* 
         $father_first_name = $this->addElement('text', 'father_first_name', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(2, 20)),),
            'required'   => true,
            'label'      => 'Father First Name: *',
            'style'     => 'width:200px;',
        ));
        
        $father_middle_name = $this->addElement('text', 'father_middle_name', array(
            'filters'    => array('StringTrim'),
            'validators' => array(array('StringLength', false, array(2, 20)),),
            'required'   => false,
            'label'      => 'Father Middle Name:',
            'style'     => 'width:200px;',
        ));        
        
        
        $father_last_name = $this->addElement('text', 'father_last_name', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(2, 20)),),
            'required'   => true,
            'label'      => 'Father Last Name: *',
            'style'     => 'width:200px;',
        ));
        
        $mother_maiden_name = $this->addElement('text', 'mother_maiden_name', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(2, 20)),),
            'required'   => true,
            'label'      => 'Mother Maiden Name: *',
            'style'     => 'width:200px;',
        ));
        
        
        $spouse_first_name = $this->addElement('text', 'spouse_first_name', array(
            'filters'    => array('StringTrim'),
            'validators' => array(array('StringLength', false, array(2, 20)),),
            'required'   => false,
            'label'      => 'Spouse First Name:',
            'style'     => 'width:200px;',
        ));
        
        $spouse_middle_name = $this->addElement('text', 'spouse_middle_name', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(2, 20)),),
            'required'   => false,
            'label'      => 'Spouse Middle Name:',
            'style'     => 'width:200px;',
        ));
        
        
        
        $spouse_last_name = $this->addElement('text', 'spouse_last_name', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(2, 20)),),
            'required'   => false,
            'label'      => 'Spouse Last Name:',
            'style'     => 'width:200px;',
        ));
        
          $addch = $this->addElement('submit', 'addch', array(
            'required' => false,
            'ignore'   => true,
            'label'    => 'Enroll Card Holder',
        ));
          
          $sub = $this->addElement('hidden', 'sub', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(1, 10)),),
            'required'   => true,
            'label'      => '',
            'style'     => 'width:200px;',
        )); 
          
          $mobile_number_old = $this->addElement('hidden', 'mobile_number_old', array(
           // 'filters'    => array('StringTrim'),
            //'validators' => array('NotEmpty', array('StringLength', false, array(10, 10)),),
            //'required'   => false,
            //'label'      => 'Mobile Number: *',
            //'style'     => 'width:200px;',
        ));
          
         $email_old = $this->addElement('hidden', 'email_old', array(
//            'filters'    => array('StringTrim'),
//            'validators' => array('EmailAddress', array('StringLength', false, array(5, 50)),),
//            'required'   => true,
//            'label'      => 'Email: *',
//            'style'     => 'width:200px;',            
        ));

        
        // We want to display a 'failed authentication' message if necessary;
        // we'll do that with the form 'description', so we need to add that
        // decorator.
        $this->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'dl', 'class' => 'zend_form')),
            array('Description', array('placement' => 'prepend')),
            'Form'
        ));
    }
     */
    
    
    
}
?>
