<?php

class CheckBalanceForm extends Zend_Form
{
  
    public function  init()
    {       
        $amount = $this->addElement('text', 'amount', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(2, 10)),),
            'required'   => true,
            'label'      => 'Active Balance',
            'disabled'   => 'disabled',
            'style'     => 'width:200px;',
        ));               
            
        // We want to display a 'failed authentication' message if necessary;
        // we'll do that with the form 'description', so we need to add that
        // decorator.
        $this->setDecorators(array(
            'FormElements',
            //array('HtmlTag', array('tag' => 'dl', 'class' => 'zend_form')),
            array(array('Value'=>'HtmlTag'), array('tag'=>'dl','class'=>'zend_form_custom')),
            array('Description', array('placement' => 'prepend')),
            'Form'
        ));
    }  
    
}
?>
