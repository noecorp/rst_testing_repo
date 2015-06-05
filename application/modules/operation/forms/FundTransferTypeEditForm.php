<?php

//class FundTransferTypeEditForm extends Zend_Form
class FundTransferTypeEditForm extends App_Operation_Form
{
  
    public function  init()
    { 
        $activeInactive = Util::getActiveInactive();
               
        $arn = $this->addElement('text', 'name', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(2, 50)),),
            'required'   => true,
            'label'      => 'Name: *',
            'style'     => 'width:200px;',
        ));
        
         
        $status = $this->addElement('select', 'status', array(
            'filters'    => array('StringTrim'),            
            'required'   => true,
            'label'      => 'Status: *',
            'style'     => 'width:200px;',
            'multioptions' => $activeInactive,
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
