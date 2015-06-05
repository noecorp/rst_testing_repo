<?php

class QueryMVCStatusForm extends App_Operation_Form
{
    protected $_cancelLink = false;
  
    public function init()
    {       
        $pan = $this->addElement('text', 'PAN', array(
            'filters'    => array('StringTrim'),
            'validators' => array('Digits', array('StringLength', false, array(16, 16)),),
            'required'   => true,
            'label'      => 'Card Number: *',
            'style'     => 'width:200px;',
            'maxlength' => '16',
        ));
        
        $expiry = $this->addElement('text', 'ExpiryDate', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(4, 4)),),
            'required'   => true,
            'label'      => 'Expiry Date: (mmyy)  *',
            'style'     => 'width:200px;',
            'maxlength' => '4',
        ));
        
        
        
        $cvv2 = $this->addElement('text', 'CVV2', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(3, 3)),),
            'required'   => true,
            'label'      => 'CVV2: *',
            'style'     => 'width:200px;',
            'maxlength' => '3',
        ));
        
        $Amount = $this->addElement('text', 'Amount', array(
            'filters'    => array('StringTrim'),
            'validators' => array('Float'),
            'required'   => true,
            'label'      => 'Amount: *',
            'style'     => 'width:200px;',
            'maxlength' => '10',
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
