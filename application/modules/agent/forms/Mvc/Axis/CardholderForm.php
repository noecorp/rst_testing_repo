<?php

class Mvc_Axis_CardholderForm extends App_Agent_Form
{
  
    public function  init()
    {       
        $arn = $this->addElement('text', 'arn', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty','Alnum', array('StringLength', false, array(2, 10))),
            'required'   => true,
            'label'      => 'ARN *',
            'maxlength'  => '10',
        ));
        
        $product_id = $this->addElement('select', 'product_id', array(
            'filters'    => array('StringTrim'),            
            'required'   => true,
            'label'      => 'Product *',
        ));
        
        $title = $this->addElement('select', 'title', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(2, 6)),),
            'required'   => true,
            'label'      => 'Title *',
            'multioptions' => Util::getTitle(),
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
       
        
        $mobile_country_code = $this->addElement('select', 'mobile_country_code', array(

            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(1, 6)),),
            'required'   => true,
            'label'      => 'Mobile Country Code *',
            'multiOptions' => array_merge(array(''=>'Select'),Mobile::getCountryCodes()),
        ));
        
        $mobile_number = $this->addElement('text', 'mobile_number', array(
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
            'validators' => array('NotEmpty', array('StringLength', false, array(4, 10)),),
            'required'   => false,
            'label'      => 'Authorization Code *',
            'maxlength'  => '6',
        ));          
        
        $email = $this->addElement('text', 'email', array(
            'filters'    => array('StringTrim'),
            'validators' => array('EmailAddress', array('StringLength', false, array(5, 50)),),
            'required'   => true,
            'label'      => 'Email *',
            'maxlength'  => '50',
        )); 
        
        $this->addElement(new ZendX_JQuery_Form_Element_DatePicker('date_of_birth',
            array('jQueryParams' => array('dateFormat' => 'dd-mm-yy'),
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(10, 10)),),
            'required'   => true,
            'label'      => 'Date of Birth *(e.g. dd-mm-yyyy) ',
            'maxlength'  => '10',
        )));        
        
         $gender = $this->addElement('select', 'gender', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(4, 6)),),
            'required'   => true,
            'label'      => 'Gender *',
            'multioptions' => Util::getGender(),
        ));  
         
         $customer_mvc_type = $this->addElement('select', 'customer_mvc_type', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(2, 10)),),
            'required'   => true,
            'label'      => 'MVC Type *',
            'multioptions' => Mvc_Axis_CardholderUser::getType(),
        ));
         
           
        $send_download_link = $this->addElement('button', 'send_download_link', array(
            'required' => false,
            'ignore'   => true,
            'label'    => 'Send Download Link',
            'class'     => 'tangerine',
        )); 
        
        
        $device_id = $this->addElement('text', 'device_id', array(
            'filters'    => array('StringTrim'),            
            'validators' => array('Digits', array('StringLength', false, array(7, 10)),),
            'required'   => false,
            'label'      => 'Device ID *',
            'maxlength'  => '10',
        ));        
        
        /* temporarily commented for revert
        $discard = new Zend_Form_Element_Submit('btn_discard');
        $discard->setOptions(
            array(
                'label'      => 'Discard',
                'required'   => FALSE,
                'title'       => 'Discard',
                'class'     => 'tangerine',
            )
        );
        $this->addElement($discard);
        
        */
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
