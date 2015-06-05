<?php

//class FundTransferTypeForm extends Zend_Form
class FundTransferTypeForm extends App_Operation_Form
{
  
    public function  init()
    { 
        $responseStatus = Util::getFundResponseStatus();
               
        $arn = $this->addElement('text', 'name', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(2, 50)),),
            'required'   => true,
            'label'      => 'Name: *',
            'style'     => 'width:200px;',
        ));
       
         $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'label'    => 'Save',
                'required' => false,
                'ignore'   => true,
                'title'    => 'Save',
                'class'    => 'tangerine',
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
