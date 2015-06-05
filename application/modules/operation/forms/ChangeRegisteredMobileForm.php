<?php

class ChangeRegisteredMobileForm extends App_Operation_Form
{
  
    public function init()
    {       
        $RequestRefNumber = $this->addElement('text', 'RequestRefNumber', array(
            'filters'    => array('StringTrim'),
           // 'validators' => array('NotEmpty', array('StringLength', false, array(2, 30)),),
            'required'   => true,
            'label'      => 'Request Reference Number: *',
            'style'     => 'width:200px;',
            'maxlength' => '20',
        ));        
        
        $OldMobileNumber = $this->addElement('text', 'OldMobileNumber', array(
            'filters'    => array('StringTrim'),
            'validators' => array('Digits', array('StringLength', false, array(10, 10)),),
            'required'   => true,
            'label'      => 'Old Mobile Number: * (+91)',
            'style'     => 'width:200px;',
            'maxlength' => '10',
        ));        
        
        
        $NewMobileNumber = $this->addElement('text', 'NewMobileNumber', array(
            'filters'    => array('StringTrim'),
            'validators' => array('Digits', array('StringLength', false, array(10, 10)),),
            'required'   => true,
            'label'      => 'New Mobile Number: * (+91)',
            'style'     => 'width:200px;',
            'maxlength' => '10',
        ));  
        
         /*$btn_auth_code = $this->addElement('button', 'btn_auth_code', array(
            'required' => false,
            'ignore'   => true,
            'label'    => 'Send Authorization Code',
            'title'    => 'Send Authorization Code',
            'onclick'  => "javascript:sendAuthCode();",
             'class'    => 'tangerine',
        )); */
         $btn = new Zend_Form_Element_Hidden('btn_auth_code');
        $btn->setOptions(
            array(
                'value' => '1'
            )
        );
        $this->addElement($btn);
         $btn_auth_code = new Zend_Form_Element_Submit('button');
        $btn_auth_code->setOptions(
            array(
                'label'      => 'Send Authorization Code',
                'required' => false,
                'ignore'   => true,
                'title'       => 'Send Authorization Code',
                'class'     => 'tangerine',
            'onclick'  => "javascript:sendAuthCode();",
            )
        );
        $this->addElement($btn_auth_code); 
        
          $auth_code = $this->addElement('text', 'auth_code', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(4, 10)),),
            'required'   => true,
            'label'      => 'Authorization Code: *',
            'style'     => 'width:200px;',
            'maxlength'  => '10',
        ));
        
          $send_auth_code = $this->addElement('hidden', 'send_auth_code', array());
           
        
//        $btn = new Zend_Form_Element_Hidden('btn_submit');
//        $btn->setOptions(
//            array(
//                'value' => '1'
//            )
//        );
        $this->addElement($btn);
         $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'label'      => 'Submit',
                'required' => false,
                'ignore'   => true,
                'title'       => 'Submit',
                'class'     => 'tangerine'
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
?>
