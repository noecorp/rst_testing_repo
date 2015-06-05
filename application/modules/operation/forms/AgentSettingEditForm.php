<?php

//class AgentSettingEditForm extends Zend_Form
class AgentSettingEditForm extends App_Operation_Form
{
  
    public function  init()
    { 
        $currency = new Currency();
        $currencyOptions = $currency->getAllCurrencyForDropDown();
               
        $name = $this->addElement('text', 'name', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(2, 50)),),
            'required'   => true,
            'label'      => 'Name: *',
            'style'     => 'width:200px;',
            'maxlength' => 50
        ));
        
         
      $description = $this->addElement('textarea', 'description', array(
            'filters'    => array('StringTrim'),
            'validators' => array(array('StringLength', false, array(8, 100)),),
            'required'   => false,
            'label'      => 'Description',
            'style'     => 'width:400px;height:200px;',
        ));
      
      $currency = $this->addElement('select', 'currency', array(
            'filters'    => array('StringTrim'),            
            'required'   => true,
            'label'      => 'Currency: *',
            'style'     => 'width:200px;',
            'multioptions' => $currencyOptions,
        ));
      
      $value = $this->addElement('text', 'value', array(
            'filters'    => array('StringTrim'),
            'validators' => array('Digits', array('StringLength', false, array(2, 8)),),
            //'validators' => array('NotEmpty', array('StringLength', false, array(2, 50)),),
            'required'   => true,
            'label'      => 'Amount: *',
            'style'     => 'width:200px;',
            'maxlength' => 10
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
