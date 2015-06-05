<?php

class Corp_Boi_EditDisbursementFile extends App_Operation_Form
{
    
    public function init()
    {   
        $this->_cancelLink = false;
        
        
        $disbursement = $this->addElement('text', 'ttum_file_name', array(
            'filters'    => array('StringTrim', 'StringToLower'),
            'required'   => TRUE,
            'validators' => array('NotEmpty','Digits'),
            'maxlength' => '12',
            'label'      => 'TTUM File Name: ',
        ));
        
        $id = new Zend_Form_Element_Hidden('id');
        $this->addElement($id);
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'label'      => 'Submit',
                'required' => false,
                'ignore'   => true,
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
