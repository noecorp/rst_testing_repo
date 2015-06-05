<?php

class Remit_Boi_RegistrationFeeForm extends App_Agent_Form
{
  
    public function  init()
    {       
        
         $regn_fee = new Zend_Form_Element_Text('regn_fee');
         $regn_fee->setOptions(
            array(
                'label'      => 'Remitter Registration Fee *',
                'required'   => TRUE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty' ,array('StringLength', false, array(2, 10)),
                                ),
                'maxlength'  => '10',
                'readonly' => 'readonly',
            )
        );
        
        $this->addElement($regn_fee);
        
       // $this->addElement($registration_fee);
                
        $submit = new Zend_Form_Element_Submit('btn_add');
        $submit->setOptions(
            array(
                'label'      => 'Pay Fee',
                'required'   => FALSE,
                'class'     => 'tangerine',
                'title'      => 'Pay Fee',
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
