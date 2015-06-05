<?php

class BlockAccountForm extends App_Operation_Form
{
  
    public function init()
    {      
        $this->_cancelLink = false;
        
        $mobile_number = $this->addElement('text', 'MobileNumber', array(
            'filters'    => array('StringTrim'),
            'validators' => array('Digits', array('StringLength', false, array(10, 10)),),
            'required'   => true,
            'label'      => 'Mobile Number: * (+91)',
            'style'     => 'width:200px;',
            'maxlength' => '10',
        ));
        
        $request_ref_number = $this->addElement('text', 'RequestRefNumber', array(
            'filters'    => array('StringTrim'),
            //'validators' => array('NotEmpty', array('StringLength', false, array(2, 30)),),
            'required'   => true,
            'label'      => 'Request Reference Number: *',
            'style'     => 'width:200px;',
            'maxlength' => '20',
        ));        
          
        
         $btn = new Zend_Form_Element_Hidden('btn_submit');
        $btn->setOptions(
            array(
                'value' => '1'
            )
        );
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
